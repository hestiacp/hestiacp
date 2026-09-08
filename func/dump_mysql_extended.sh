#!/bin/bash
set -euo pipefail

MYSQL_BIN="${MYSQL_BIN:-mysql}"
MYSQLDUMP_BIN="${MYSQLDUMP_BIN:-mysqldump}"

BACKUP_DIR="${BACKUP_DIR:-/backup}"
LARGE_TABLE_ROW_THRESHOLD="${LARGE_TABLE_ROW_THRESHOLD:-100000}"

if [ -z "${MYSQL_AUTH+x}" ]; then
	if [ -n "${MYSQL_DEFAULTS_FILE:-}" ]; then
		MYSQL_AUTH=(--defaults-file="$MYSQL_DEFAULTS_FILE")
	else
		MYSQL_HOST="${MYSQL_HOST:-localhost}"
		MYSQL_USER="${MYSQL_USER:-root}"
		MYSQL_PASS="${MYSQL_PASS:-}"
		MYSQL_PORT="${MYSQL_PORT:-3306}"
		MYSQL_AUTH=(-h"$MYSQL_HOST" -u"$MYSQL_USER" -P"$MYSQL_PORT")
		if [ -n "$MYSQL_PASS" ]; then
			MYSQL_AUTH+=(-p"$MYSQL_PASS")
		fi
	fi
fi

DB="${1:-}"

if [[ -z "$DB" ]]; then
  echo "Usage: $0 <database> [problematic_table ...]" >&2
  exit 1
fi

shift || true
PROBLEMATIC_TABLES=("$@")

TMP_DIR="$(mktemp -d)"

cleanup() {
  rm -rf "$TMP_DIR"
}

trap cleanup EXIT

sql_escape() {
  printf "%s" "$1" | sed "s/'/''/g"
}

quote_identifier() {
  local value="${1//\`/\`\`}"
  printf '`%s`' "$value"
}

backtick_escaped_name() {
  printf "%s" "${1//\`/\`\`}"
}

safe_filename() {
  printf "%s" "$1" | sed 's/[^A-Za-z0-9_.-]/_/g'
}

escape_ere() {
  printf "%s" "$1" | sed 's/[][(){}.^$*+?|\\]/\\&/g'
}

join_by() {
  local IFS="$1"
  shift
  printf "%s" "$*"
}

is_problematic_table() {
  local table="$1"
  local problematic_table

  for problematic_table in "${PROBLEMATIC_TABLES[@]}"; do
    if [[ "$problematic_table" == "$table" ]]; then
      return 0
    fi
  done

  return 1
}

append_view_dependency() {
  local view="$1"
  local dependency="$2"

  if [[ "$view" == "$dependency" ]]; then
    return 0
  fi

  case $'\n'"${VIEW_DEPENDENCIES[$view]:-}"$'\n' in
    *$'\n'"$dependency"$'\n'*)
      ;;
    *)
      VIEW_DEPENDENCIES["$view"]+="${dependency}"$'\n'
      ;;
  esac
}

get_view_definition_file() {
  local view="$1"
  local output_file="$2"

  local db_sql
  local view_sql

  db_sql="$(sql_escape "$DB")"
  view_sql="$(sql_escape "$view")"

  "$MYSQL_BIN" "${MYSQL_AUTH[@]}" \
    --batch \
    --raw \
    --skip-column-names \
    -e "
      SELECT VIEW_DEFINITION
      FROM information_schema.views
      WHERE table_schema = '${db_sql}'
        AND table_name = '${view_sql}';
    " > "$output_file"
}

view_definition_references_view() {
  local definition_file="$1"
  local dependency="$2"

  local db_bt
  local dep_bt
  local dep_ere

  db_bt="$(backtick_escaped_name "$DB")"
  dep_bt="$(backtick_escaped_name "$dependency")"
  dep_ere="$(escape_ere "$dependency")"

  if grep -Fq "\`${db_bt}\`.\`${dep_bt}\`" "$definition_file"; then
    return 0
  fi

  if grep -Fq "\`${dep_bt}\`" "$definition_file"; then
    return 0
  fi

  if tr '\n\r\t' '   ' < "$definition_file" |
    grep -Eiq "((from|join)[[:space:]\(]+|,[[:space:]]+)([A-Za-z0-9_\$]+\.)?${dep_ere}([^A-Za-z0-9_\$]|$)"; then
    return 0
  fi

  return 1
}

build_view_dependencies_from_information_schema() {
  local db_sql
  db_sql="$(sql_escape "$DB")"

  while IFS=$'\t' read -r view dependency; do
    if [[ -n "$view" && -n "$dependency" ]]; then
      append_view_dependency "$view" "$dependency"
    fi
  done < <(
    "$MYSQL_BIN" "${MYSQL_AUTH[@]}" \
      --batch \
      --raw \
      --skip-column-names \
      -e "
        SELECT
          usage_info.VIEW_NAME,
          usage_info.TABLE_NAME
        FROM information_schema.VIEW_TABLE_USAGE usage_info
        INNER JOIN information_schema.views view_info
          ON view_info.TABLE_SCHEMA = usage_info.TABLE_SCHEMA
         AND view_info.TABLE_NAME = usage_info.TABLE_NAME
        WHERE usage_info.VIEW_SCHEMA = '${db_sql}'
          AND usage_info.TABLE_SCHEMA = '${db_sql}'
        ORDER BY usage_info.VIEW_NAME, usage_info.TABLE_NAME;
      "
  )
}

build_view_dependencies_by_definition_scan() {
  local view
  local dependency
  local definition_file

  for view in "${VIEWS[@]}"; do
    definition_file="${TMP_DIR}/view-definition-$(safe_filename "$view").sql"
    get_view_definition_file "$view" "$definition_file"

    for dependency in "${VIEWS[@]}"; do
      if [[ "$view" == "$dependency" ]]; then
        continue
      fi

      if view_definition_references_view "$definition_file" "$dependency"; then
        append_view_dependency "$view" "$dependency"
      fi
    done
  done
}

build_view_dependencies() {
  local db_sql
  local has_view_table_usage

  db_sql="$(sql_escape "$DB")"

  has_view_table_usage="$(
    "$MYSQL_BIN" "${MYSQL_AUTH[@]}" \
      --batch \
      --raw \
      --skip-column-names \
      -e "
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = 'information_schema'
          AND UPPER(table_name) = 'VIEW_TABLE_USAGE';
      "
  )"

  if [[ "$has_view_table_usage" =~ ^[0-9]+$ && "$has_view_table_usage" -gt 0 ]]; then
    echo "view dependency source ---> information_schema.VIEW_TABLE_USAGE"
    build_view_dependencies_from_information_schema
  else
    echo "view dependency source ---> VIEW_DEFINITION scan fallback"
    build_view_dependencies_by_definition_scan
  fi
}

sort_views_by_dependencies() {
  ORDERED_VIEWS=()

  declare -gA VIEW_DONE=()

  local total
  local progress
  local view
  local dependency
  local ready
  local ordered_count
  local dependency_list

  total="${#VIEWS[@]}"

  while (( ${#ORDERED_VIEWS[@]} < total )); do
    progress=0

    for view in "${VIEWS[@]}"; do
      if [[ -n "${VIEW_DONE[$view]:-}" ]]; then
        continue
      fi

      ready=1
      dependency_list="${VIEW_DEPENDENCIES[$view]:-}"

      while IFS= read -r dependency; do
        if [[ -z "$dependency" ]]; then
          continue
        fi

        if [[ -z "${VIEW_DONE[$dependency]:-}" ]]; then
          ready=0
          break
        fi
      done <<< "$dependency_list"

      if (( ready == 1 )); then
        ORDERED_VIEWS+=("$view")
        VIEW_DONE["$view"]=1
        progress=1
      fi
    done

    if (( progress == 0 )); then
      echo "warning ---> circular or unresolved view dependency detected; remaining views will be appended alphabetically" >&2

      for view in "${VIEWS[@]}"; do
        if [[ -z "${VIEW_DONE[$view]:-}" ]]; then
          ORDERED_VIEWS+=("$view")
          VIEW_DONE["$view"]=1
        fi
      done

      break
    fi
  done
}

write_view_placeholders_and_definitions() {
  local output_file="$1"

  local view
  local column
  local columns
  local select_parts
  local select_list
  local definition_file
  local db_sql
  local view_sql
  local security_type
  local check_option
  local check_clause
  local idx

  : > "$output_file"

  if (( ${#VIEWS[@]} == 0 )); then
    {
      echo "-- No view found."
      echo "-- Generated at: $(date '+%Y-%m-%d %H:%M:%S')"
    } > "$output_file"

    return 0
  fi

  build_view_dependencies
  sort_views_by_dependencies

  {
    echo "-- View definitions generated in dependency order."
    echo "-- Existing views are dropped first."
    echo "-- Lightweight placeholders are created before real definitions."
    echo
    echo "-- Drop existing views. Dependents are dropped before dependencies."
  } >> "$output_file"

  for (( idx=${#ORDERED_VIEWS[@]}-1; idx>=0; idx-- )); do
    view="${ORDERED_VIEWS[$idx]}"
    echo "DROP VIEW IF EXISTS $(quote_identifier "$view");" >> "$output_file"
  done

  {
    echo
    echo "-- Lightweight placeholders."
  } >> "$output_file"

  for view in "${ORDERED_VIEWS[@]}"; do
    db_sql="$(sql_escape "$DB")"
    view_sql="$(sql_escape "$view")"

    mapfile -t columns < <(
      "$MYSQL_BIN" "${MYSQL_AUTH[@]}" \
        --batch \
        --raw \
        --skip-column-names \
        -e "
          SELECT COLUMN_NAME
          FROM information_schema.columns
          WHERE table_schema = '${db_sql}'
            AND table_name = '${view_sql}'
          ORDER BY ORDINAL_POSITION;
        "
    )

    select_parts=()

    if (( ${#columns[@]} == 0 )); then
      select_parts+=("NULL AS dummy_column")
    else
      for column in "${columns[@]}"; do
        select_parts+=("NULL AS $(quote_identifier "$column")")
      done
    fi

    select_list="$(join_by ", " "${select_parts[@]}")"

    echo "CREATE OR REPLACE ALGORITHM = UNDEFINED VIEW $(quote_identifier "$view") AS SELECT ${select_list} WHERE FALSE;" >> "$output_file"
  done

  {
    echo
    echo "-- Real view definitions."
  } >> "$output_file"

  for view in "${ORDERED_VIEWS[@]}"; do
    db_sql="$(sql_escape "$DB")"
    view_sql="$(sql_escape "$view")"

    read -r security_type check_option < <(
      "$MYSQL_BIN" "${MYSQL_AUTH[@]}" \
        --batch \
        --raw \
        --skip-column-names \
        -e "
          SELECT
            COALESCE(SECURITY_TYPE, 'DEFINER'),
            COALESCE(CHECK_OPTION, 'NONE')
          FROM information_schema.views
          WHERE table_schema = '${db_sql}'
            AND table_name = '${view_sql}';
        "
    )

    security_type="${security_type^^}"
    check_option="${check_option^^}"

    if [[ "$security_type" != "DEFINER" && "$security_type" != "INVOKER" ]]; then
      security_type="DEFINER"
    fi

    check_clause=""

    case "$check_option" in
      LOCAL)
        check_clause=" WITH LOCAL CHECK OPTION"
        ;;
      CASCADED|CASCADE)
        check_clause=" WITH CASCADED CHECK OPTION"
        ;;
    esac

    definition_file="${TMP_DIR}/view-definition-final-$(safe_filename "$view").sql"
    get_view_definition_file "$view" "$definition_file"

    {
      echo
      echo "-- ${view} ------------------"
      echo "DROP VIEW IF EXISTS $(quote_identifier "$view");"
      echo "CREATE OR REPLACE ALGORITHM = UNDEFINED SQL SECURITY ${security_type} VIEW $(quote_identifier "$view") AS"
    } >> "$output_file"

    cat "$definition_file" >> "$output_file"
    printf "%s;\n" "$check_clause" >> "$output_file"
  done
}

DB_SQL="$(sql_escape "$DB")"

cd "$BACKUP_DIR"
# mkdir -p ./.backup-prev
# if [[ -f "./${DB}.sql.zip" ]]; then
  # mv -f "./${DB}.sql.zip" ./.backup-prev/
# fi

rm -f "./${DB}"_*.sql

echo "step ---> collecting base tables"

mapfile -t BASE_TABLES < <(
  "$MYSQL_BIN" "${MYSQL_AUTH[@]}" \
    --batch \
    --raw \
    --skip-column-names \
    -e "
      SELECT TABLE_NAME
      FROM information_schema.tables
      WHERE table_schema = '${DB_SQL}'
        AND table_type = 'BASE TABLE'
      ORDER BY TABLE_NAME;
    "
)

echo "step ---> table data"

SMALL_TABLES=()

for table in "${BASE_TABLES[@]}"; do
  if is_problematic_table "$table"; then
    echo " > ${table} --> problematic table skipped; dump this table manually"
    continue
  fi

  table_sql="$(quote_identifier "$table")"

  row_count="$(
    "$MYSQL_BIN" "${MYSQL_AUTH[@]}" \
      --batch \
      --raw \
      --skip-column-names \
      "$DB" \
      -e "SELECT COUNT(*) FROM ${table_sql};"
  )"

  if ! [[ "$row_count" =~ ^[0-9]+$ ]]; then
    echo "Invalid row count for table '${table}': ${row_count}" >&2
    exit 1
  fi

  if (( row_count > LARGE_TABLE_ROW_THRESHOLD )); then
    output_file="${DB}_$(safe_filename "$table")-data.sql"

    echo "dumping ${table} | ${row_count} rows"

    "$MYSQLDUMP_BIN" "${MYSQL_AUTH[@]}" \
      --single-transaction \
      --quick \
      --no-create-info \
      --skip-triggers \
      "$DB" "$table" > "$output_file"
  else
    SMALL_TABLES+=("$table")
  fi
done

echo "dumping other table data..."

if (( ${#SMALL_TABLES[@]} > 0 )); then
  "$MYSQLDUMP_BIN" "${MYSQL_AUTH[@]}" \
    --single-transaction \
    --quick \
    --no-create-info \
    --skip-triggers \
    "$DB" "${SMALL_TABLES[@]}" > "${DB}_zzz-small-tb-data.sql"
else
  {
    echo "-- No small table data found."
    echo "-- Generated at: $(date '+%Y-%m-%d %H:%M:%S')"
  } > "${DB}_zzz-small-tb-data.sql"
fi

echo "step ---> table structures and indexes"

if (( ${#BASE_TABLES[@]} > 0 )); then
  "$MYSQLDUMP_BIN" "${MYSQL_AUTH[@]}" \
    --single-transaction \
    --no-data \
    --skip-triggers \
    "$DB" "${BASE_TABLES[@]}" > "${DB}_0-table-structs.sql"
else
  {
    echo "-- No base table found."
    echo "-- Generated at: $(date '+%Y-%m-%d %H:%M:%S')"
  } > "${DB}_0-table-structs.sql"
fi

echo "step ---> view structures"

mapfile -t VIEWS < <(
  "$MYSQL_BIN" "${MYSQL_AUTH[@]}" \
    --batch \
    --raw \
    --skip-column-names \
    -e "
      SELECT TABLE_NAME
      FROM information_schema.views
      WHERE table_schema = '${DB_SQL}'
      ORDER BY TABLE_NAME;
    "
)

declare -A VIEW_DEPENDENCIES=()
declare -a ORDERED_VIEWS=()

write_view_placeholders_and_definitions "${DB}_1-views.sql"

echo "step ---> stored routines"

"$MYSQLDUMP_BIN" "${MYSQL_AUTH[@]}" \
  --single-transaction \
  --no-data \
  --no-create-info \
  --skip-triggers \
  --routines \
  --skip-events \
  "$DB" > "${DB}_2-routines.sql"

echo "step ---> triggers"

if (( ${#BASE_TABLES[@]} > 0 )); then
  "$MYSQLDUMP_BIN" "${MYSQL_AUTH[@]}" \
    --single-transaction \
    --no-data \
    --no-create-info \
    --triggers \
    --skip-routines \
    --skip-events \
    "$DB" "${BASE_TABLES[@]}" > "${DB}_3-triggers.sql"
else
  {
    echo "-- No base table found. No trigger can be exported."
    echo "-- Generated at: $(date '+%Y-%m-%d %H:%M:%S')"
  } > "${DB}_3-triggers.sql"
fi

echo "step ---> events"

"$MYSQLDUMP_BIN" "${MYSQL_AUTH[@]}" \
  --single-transaction \
  --no-data \
  --no-create-info \
  --skip-routines \
  --skip-triggers \
  --events \
  "$DB" > "${DB}_4-events.sql"

echo "step ---> combining sql files into single dump"

# Combine all SQL files into single {DB}.mysql.sql in correct order
# This consolidated file will be compressed and archived by v-backup-user
COMBINED_SQL="${DB}.mysql.sql"
: > "$COMBINED_SQL"

echo "  combining: table structures"
cat "${DB}_0-table-structs.sql" >> "$COMBINED_SQL"

echo "  combining: views (with dependency ordering)"
cat "${DB}_1-views.sql" >> "$COMBINED_SQL"

echo "  combining: stored routines"
cat "${DB}_2-routines.sql" >> "$COMBINED_SQL"

echo "  combining: triggers"
cat "${DB}_3-triggers.sql" >> "$COMBINED_SQL"

echo "  combining: small tables"
cat "${DB}_zzz-small-tb-data.sql" >> "$COMBINED_SQL"

# Combine table data
for large_data_file in $(ls "${DB}"_*-data.sql 2>/dev/null | grep -v "_zzz-small-tb-data.sql" | sort); do
  if [[ -f "$large_data_file" ]]; then
    echo "  combining: $large_data_file"
    cat "$large_data_file" >> "$COMBINED_SQL"
  fi
done

echo "  combining: events"
cat "${DB}_4-events.sql" >> "$COMBINED_SQL"

# Remove intermediate-data files
rm -f "${DB}"_*-data.sql "${DB}_zzz-small-tb-data.sql"

echo
echo "·······················································"
echo "····> Extended MySQL Dump Complete"
echo "····> Output: ${COMBINED_SQL}"
echo "····> Structural files: ${DB}_0-table-structs.sql"
echo "····>               ... ${DB}_1-views.sql"
echo "····>               ... ${DB}_2-routines.sql"
echo "····>               ... ${DB}_3-triggers.sql"
echo "····>               ... ${DB}_4-events.sql"
echo "····> Ready for compression and archival"
echo "·······················································"
