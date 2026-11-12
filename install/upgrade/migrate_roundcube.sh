#!/bin/bash
# info: Migrate a distribution-packaged Roundcube install to the Hestia-managed upstream install
# options: [--yes]
#
# This script is used automatically by the 1.10.7 upgrade and can also be run
# manually. It is intentionally non-destructive until a verified database dump
# exists, and it keeps a timestamped backup under /root forever.

set -o pipefail

#----------------------------------------------------------#
#                    Variables & Functions                 #
#----------------------------------------------------------#

HESTIA="${HESTIA:-/usr/local/hestia}"
hestia_conf="${RC_HESTIA_CONF:-/etc/hestiacp/hestia.conf}"

# func/main.sh unconditionally points HESTIA_BACKUP at its own default. Keep
# the real upgrade backup directory so migration messages reach the upgrade
# log/email (and do not get silently dropped).
RC_UPGRADE_BACKUP="${HESTIA_BACKUP:-}"

if [[ -r "$hestia_conf" ]]; then
	# shellcheck disable=SC1090,SC1091
	source "$hestia_conf"
fi

HESTIA="${HESTIA:-/usr/local/hestia}"
BIN="${BIN:-$HESTIA/bin}"

# shellcheck disable=SC1090,SC1091
source "$HESTIA/func/main.sh"
# shellcheck disable=SC1090,SC1091
source "$HESTIA/func/db.sh"
source_conf "$HESTIA/conf/hestia.conf"
source "$HESTIA/install/upgrade/upgrade.conf"

if [[ -n "$RC_UPGRADE_BACKUP" ]]; then
	HESTIA_BACKUP="$RC_UPGRADE_BACKUP"
fi

# These paths are overridable in tests. Production always uses the defaults.
RC_SHARE_DIR="${RC_SHARE_DIR:-/usr/share/roundcube}"
RC_CONFIG_DIR="${RC_CONFIG_DIR:-/etc/roundcube}"
RC_INSTALL_DIR="${RC_INSTALL_DIR:-/var/lib/roundcube}"
RC_LOG_DIR="${RC_LOG_DIR:-/var/log/roundcube}"
RC_BACKUP_ROOT="${RC_BACKUP_ROOT:-/root}"
RC_CONFIG_TOOL="$HESTIA/install/upgrade/roundcube-config-tool.php"
# Roundcube's updater needs the system PHP (PDO MySQL), not Hestia's bundled
# PHP build. This matches bin/v-add-sys-roundcube.
PHP_CLI="${PHP_CLI:-/usr/bin/php}"
if [[ ! -x "$PHP_CLI" ]]; then
	PHP_CLI="$(command -v php || true)"
fi

ASSUME_YES="no"
DEFER_PACKAGE_REMOVAL="no"
PURGE_ONLY="no"
while [[ $# -gt 0 ]]; do
	case "$1" in
		--yes | -y) ASSUME_YES="yes" ;;
		--defer-package-removal) DEFER_PACKAGE_REMOVAL="yes" ;;
		--purge-only) PURGE_ONLY="yes" ;;
		--backup-dir)
			[[ -n "${2:-}" ]] || {
				echo "ERROR: --backup-dir requires a value" >&2
				exit 2
			}
			BACKUP_DIR="$2"
			shift
			;;
		--help | -h)
			echo "Usage: $0 [--yes] [--defer-package-removal]"
			echo "       $0 --purge-only --backup-dir <dir>"
			exit 0
			;;
		*)
			echo "ERROR: Unknown option: $1" >&2
			exit 2
			;;
	esac
	shift
done

BACKUP_DIR="${BACKUP_DIR:-}"
MYSQL_DUMP_BIN=""
MYSQL_CLIENT_BIN=""
mycnf=""
PACKAGE_MANAGER=""

DESTRUCTIVE_STARTED=0
OLD_DB_DROPPED=0
OLD_USER_DROPPED=0
ORIGINAL_SHARE_EXISTED=0
ORIGINAL_CONFIG_EXISTED=0
ORIGINAL_INSTALL_EXISTED=0
BACKED_UP_SHARE=0
BACKED_UP_CONFIG=0
BACKED_UP_INSTALL=0
VADD_ATTEMPTED=0
PACKAGES_REMOVED=0
SKIP_VERSION_CHECK=0
ROLLBACK_DISABLED=0
ROLLING_BACK=0
STAGING_DIR=""
OLD_DB_DUMP=""
OLD_DB_BASELINE=""
NEW_DB_REPAIR_DUMP=""
NEW_DB_REPAIR_BASELINE=""
VERIFIED_DUMP_BASELINE=""

add_rc_message() {
	local message="$1"

	if [[ -n "${HESTIA_BACKUP:-}" && -d "${HESTIA_BACKUP:-}" ]]; then
		printf '%b\n\n\n' "$message" >> "$HESTIA_BACKUP/message.log"
	fi
}

skip_migration() {
	local reason="$1"

	echo "[ ! ] Roundcube migration skipped: $reason"
	add_rc_message "Roundcube migration skipped: $reason\nYour distribution-packaged Roundcube installation was left unchanged."
	exit 0
}

abort_early() {
	local reason="$1"

	echo "[ ! ] ERROR: $reason" >&2
	echo "[ ! ] No changes were made to the existing Roundcube installation." >&2
	if [[ -n "$BACKUP_DIR" ]]; then
		echo "[ ! ] Backup directory retained: $BACKUP_DIR" >&2
		add_rc_message "Roundcube migration was not started: $reason\nThe existing installation is unchanged. Partial backup: $BACKUP_DIR"
	fi
	exit 1
}

is_hestia_roundcube() {
	[[ -f "$RC_INSTALL_DIR/program/include/iniset.php" ]] \
		&& [[ ! -L "$RC_INSTALL_DIR/program" ]]
}

is_distribution_roundcube_layout() {
	# Debian/Ubuntu install the package code in /usr/share/roundcube and use
	# /var/lib/roundcube as a symlink farm into it. Require a code symlink:
	# Hestia itself creates a "config" symlink, so that alone is not enough.
	[[ -L "$RC_INSTALL_DIR/program" || -L "$RC_INSTALL_DIR/index.php" ]]
}

ensure_roundcube_polyfill() {
	local php_id
	local composer_bin
	local composer_home
	local composer_log

	php_id="$("$PHP_CLI" -r 'echo PHP_VERSION_ID;' 2> /dev/null || true)"
	[[ "$php_id" =~ ^[0-9]+$ ]] || return 0
	[[ "$php_id" -lt 80400 ]] || return 0

	if [[ -f "$RC_INSTALL_DIR/vendor/autoload.php" ]]; then
		# shellcheck disable=SC2016 # $argv is PHP, not shell
		if "$PHP_CLI" -r 'require $argv[1]; exit(class_exists("Pdo\\Mysql") ? 0 : 1);' \
			"$RC_INSTALL_DIR/vendor/autoload.php" > /dev/null 2>&1; then
			return 0
		fi
	elif "$PHP_CLI" -r 'exit(class_exists("Pdo\\Mysql") ? 0 : 1);' > /dev/null 2>&1; then
		return 0
	fi

	if [[ ! -f "$RC_INSTALL_DIR/composer.json" ]]; then
		echo "[ ! ] Pdo\\Mysql is missing and $RC_INSTALL_DIR/composer.json is absent" >&2
		return 1
	fi

	composer_bin="$HOMEDIR/${ROOT_USER:-admin}/.composer/composer"
	if [[ ! -x "$composer_bin" ]]; then
		"$BIN/v-add-user-composer" "${ROOT_USER:-admin}" &> /dev/null || true
	fi
	if [[ ! -x "$composer_bin" ]]; then
		echo "[ ! ] Pdo\\Mysql is missing and Composer is unavailable" >&2
		return 1
	fi

	composer_home="$(mktemp -d)"
	composer_log="$(mktemp)"
	if COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_HOME="$composer_home" "$PHP_CLI" -d disable_functions= \
		"$composer_bin" --working-dir="$RC_INSTALL_DIR" require 'symfony/polyfill-php84:^1.34' \
		--no-interaction --no-progress --update-no-dev > "$composer_log" 2>&1; then
		echo "[ + ] Installed symfony/polyfill-php84 for Roundcube"
	else
		echo "[ ! ] Failed to install symfony/polyfill-php84 for Roundcube" >&2
		tail -n 20 "$composer_log" >&2 || true
		rm -rf "$composer_home" "$composer_log"
		return 1
	fi

	rm -rf "$composer_home" "$composer_log"
	return 0
}

roundcube_version() {
	local file="$1"

	[[ -f "$file" ]] || return 1
	grep -oE "RCMAIL_VERSION[^0-9]*[0-9]+\.[0-9]+\.[0-9]+" "$file" \
		| grep -oE '[0-9]+\.[0-9]+\.[0-9]+' \
		| head -n 1
}

current_db_dump() {
	if [[ -n "${OLD_DB_DUMP:-}" && -s "$OLD_DB_DUMP" ]]; then
		printf '%s\n' "$OLD_DB_DUMP"
	else
		printf '%s\n' "$BACKUP_DIR/db/roundcube.sql"
	fi
}

new_db_repair_dump() {
	if [[ -n "${NEW_DB_REPAIR_DUMP:-}" && -s "$NEW_DB_REPAIR_DUMP" ]]; then
		printf '%s\n' "$NEW_DB_REPAIR_DUMP"
	else
		current_db_dump
	fi
}

row_counts() {
	local database="$1"
	local tables
	local table
	local count

	tables="$(mysql_query_raw "SELECT table_name FROM information_schema.tables WHERE table_schema = '$(mysql_sql_escape "$database")' AND table_type = 'BASE TABLE' ORDER BY table_name")" || return 1

	while IFS= read -r table; do
		[[ -n "$table" ]] || continue
		table="${table//\`/\`\`}"
		count="$(mysql_query_raw "SELECT COUNT(*) FROM \`$database\`.\`$table\`")" || return 1
		printf '%s\t%s\n' "$table" "$count"
	done <<< "$tables"
}

capture_row_counts() {
	local output_file="$1"
	local database="$2"

	row_counts "$database" > "$output_file" || return 1
	[[ -s "$output_file" ]]
}

compare_row_counts() {
	local before_file="$1"
	local after_file="$2"
	local phase="$3"
	local table
	local expected
	local actual
	local missing=0
	local lost=0
	local changed=0

	while IFS=$'\t' read -r table expected; do
		[[ -n "$table" ]] || continue
		actual="$(awk -F '\t' -v table="$table" '$1 == table { print $2; exit }' "$after_file")"

		if [[ -z "$actual" ]]; then
			echo "[ ! ] Table '$table' is missing $phase" >&2
			missing=1
		elif [[ "$actual" -lt "$expected" ]]; then
			echo "[ ! ] Table '$table' lost rows $phase ($expected -> $actual)" >&2
			lost=1
		elif [[ "$actual" -gt "$expected" ]]; then
			echo "[ * ] Table '$table' gained rows $phase ($expected -> $actual)"
			changed=1
		fi
	done < "$before_file"

	[[ "$changed" -eq 0 ]] || echo "[ * ] Roundcube's schema update added rows to one or more tables"
	[[ "$missing" -eq 0 ]] || return 2
	[[ "$lost" -eq 0 ]] || return 1
	return 0
}

verify_dump_file() {
	local dump_file="$1"
	local baseline_file="$2"
	local label="$3"
	local expected_tables
	local actual_tables
	local check_db
	local attempt
	local existing_check_db
	local rc=0
	local verify_counts="$BACKUP_DIR/db/rowcounts-verify-${label// /-}.tsv"

	VERIFIED_DUMP_BASELINE=""

	[[ -s "$dump_file" ]] || {
		echo "[ ! ] $label dump is missing or empty: $dump_file" >&2
		return 1
	}
	grep -q '^CREATE TABLE' "$dump_file" || {
		echo "[ ! ] $label dump does not contain a table schema" >&2
		return 1
	}
	expected_tables="$(grep -c . "$baseline_file" 2> /dev/null || true)"
	actual_tables="$(grep -c '^CREATE TABLE' "$dump_file" || true)"
	if [[ ! "$expected_tables" =~ ^[0-9]+$ || "$expected_tables" -eq 0 || "$actual_tables" -lt "$expected_tables" ]]; then
		echo "[ ! ] $label dump has $actual_tables tables; expected at least $expected_tables" >&2
		return 1
	fi

	for attempt in 1 2 3 4 5; do
		check_db="hestia_rc_check_$$_${RANDOM}_$attempt"
		if ! existing_check_db="$(mysql_query_raw "SELECT SCHEMA_NAME FROM information_schema.schemata WHERE schema_name = '$(mysql_sql_escape "$check_db")'")"; then
			echo "[ ! ] Could not check the $label dump-test database name" >&2
			return 1
		fi
		[[ -z "$existing_check_db" ]] && break
	done
	if [[ -n "$existing_check_db" ]]; then
		echo "[ ! ] Could not find a free dump-test database name" >&2
		return 1
	fi

	mysql_query "CREATE DATABASE \`$check_db\` CHARACTER SET utf8" || return 1
	"$MYSQL_CLIENT_BIN" --defaults-file="$mycnf" "$check_db" < "$dump_file" || rc=1
	if [[ "$rc" -eq 0 ]]; then
		capture_row_counts "$verify_counts" "$check_db" || rc=1
	fi
	if [[ "$rc" -eq 0 ]]; then
		compare_row_counts "$baseline_file" "$verify_counts" "in the $label dump test"
		local compare_rc=$?
		if [[ "$compare_rc" -eq 2 ]]; then
			rc=1
		elif [[ "$compare_rc" -eq 1 ]]; then
			echo "[ ! ] Rows changed before the $label dump; continuing with the point-in-time snapshot" >&2
		fi
	fi
	mysql_query "DROP DATABASE IF EXISTS \`$check_db\`" > /dev/null 2>&1 || true
	if [[ "$rc" -eq 0 ]]; then
		VERIFIED_DUMP_BASELINE="$verify_counts"
	fi
	return "$rc"
}

installed_roundcube_packages() {
	local output

	if command -v dpkg-query > /dev/null 2>&1; then
		output="$(dpkg-query -W -f='${db:Status-Abbrev}\t${Package}\t${Version}\n' 'roundcube*' 2> /dev/null)"
		local dpkg_rc=$?
		# dpkg-query exits 1 when the glob matches no packages; that is a
		# normal "none installed" result, not a command failure.
		if [[ "$dpkg_rc" -ne 0 && ! ("$dpkg_rc" -eq 1 && -z "$output") ]]; then
			return 1
		fi
		awk -F '\t' '$1 !~ /^(un|pn)/ && $2 ~ /^roundcube(-|$)/ { print $2 "\t" $3 }' <<< "$output"
	elif command -v rpm > /dev/null 2>&1; then
		output="$(rpm -qa --qf '%{NAME}\t%{VERSION}-%{RELEASE}\n' 2> /dev/null)" || return 1
		awk -F '\t' '$1 ~ /^roundcube(-|$)/' <<< "$output"
	else
		return 1
	fi
}

detect_package_manager() {
	if command -v dpkg-query > /dev/null 2>&1; then
		PACKAGE_MANAGER="apt"
	elif command -v dnf > /dev/null 2>&1; then
		PACKAGE_MANAGER="dnf"
	elif command -v yum > /dev/null 2>&1; then
		PACKAGE_MANAGER="yum"
	else
		PACKAGE_MANAGER=""
	fi
}

backup_path_atomically() {
	local source="$1"
	local backup_path="$2"
	local partial="${backup_path}.partial"

	rm -rf "$partial" "$backup_path"
	cp -a "$source" "$partial" || {
		rm -rf "$partial"
		return 1
	}
	mv "$partial" "$backup_path" || return 1
}

restore_backup_directory() {
	local backup_path="$1"
	local destination="$2"
	local temporary="${destination}.hestia-restore.$$"

	[[ -e "$backup_path" || -L "$backup_path" ]] || return 0
	rm -rf "$temporary"
	mkdir -p "$(dirname "$destination")"
	cp -a "$backup_path" "$temporary" || return 1
	rm -rf "$destination"
	mv "$temporary" "$destination"
}

restore_staged_directory() {
	local staged_path="$1"
	local destination="$2"

	[[ -e "$staged_path" || -L "$staged_path" ]] || return 0
	rm -rf "$destination"
	mkdir -p "$(dirname "$destination")"
	mv "$staged_path" "$destination"
}

restore_old_database() {
	local dump_file
	dump_file="$(current_db_dump)"
	local escaped_password
	local escaped_user
	local escaped_host
	local query
	local old_db_exists=""
	local recreate_old_db=0

	if [[ ! -s "$dump_file" ]]; then
		echo "[ ! ] CRITICAL: database dump is missing; cannot restore the old database" >&2
		return 1
	fi

	escaped_user="$(mysql_sql_escape "$OLD_DB_USER")"
	escaped_password="$(mysql_sql_escape "$OLD_DB_PASS")"
	escaped_host="$(mysql_sql_escape "$OLD_DB_HOST")"

	if [[ "$OLD_DB_NAME" == "$NEW_DB_NAME" ]]; then
		if ! old_db_exists="$(mysql_query_raw "SELECT SCHEMA_NAME FROM information_schema.schemata WHERE schema_name = '$(mysql_sql_escape "$OLD_DB_NAME")'")"; then
			echo "[ ! ] CRITICAL: could not determine whether database $OLD_DB_NAME exists" >&2
			return 1
		fi

		# The old database was dropped before v-add-sys-roundcube, or the drop
		# may have happened just before an interrupting signal. If it still
		# exists and v-add was never attempted, it is the untouched original.
		if [[ "$OLD_DB_DROPPED" -eq 1 || "$VADD_ATTEMPTED" -eq 1 || -z "$old_db_exists" ]]; then
			recreate_old_db=1
		fi
	else
		# The old database has a different name and was never dropped. Only
		# remove the newly created Hestia database.
		mysql_query "DROP DATABASE IF EXISTS \`$NEW_DB_NAME\`" || true
	fi

	if [[ "$recreate_old_db" -eq 1 ]]; then
		mysql_query "DROP DATABASE IF EXISTS \`$NEW_DB_NAME\`" || true
		if ! mysql_query "CREATE DATABASE IF NOT EXISTS \`$OLD_DB_NAME\` CHARACTER SET utf8"; then
			echo "[ ! ] CRITICAL: could not recreate database $OLD_DB_NAME" >&2
			return 1
		fi
	fi

	if [[ "$OLD_DB_USER" == "$NEW_DB_USER" ]]; then
		local old_user_exists=""
		if ! old_user_exists="$(mysql_query_raw "SELECT 1 FROM mysql.user WHERE User = '$escaped_user' AND Host = '$escaped_host' LIMIT 1")"; then
			echo "[ ! ] CRITICAL: could not determine whether user $OLD_DB_USER exists" >&2
			return 1
		fi
		if [[ "$OLD_USER_DROPPED" -eq 1 || "$VADD_ATTEMPTED" -eq 1 || -z "$old_user_exists" ]]; then
			mysql_query "DROP USER IF EXISTS '$NEW_DB_USER'@'localhost', '$NEW_DB_USER'@'127.0.0.1', '$NEW_DB_USER'@'::1', '$NEW_DB_USER'@'%'" || true
			query="CREATE USER '$escaped_user'@'$escaped_host' IDENTIFIED BY '$escaped_password'"
			if ! mysql_query "$query"; then
				echo "[ ! ] CRITICAL: could not recreate database user $OLD_DB_USER" >&2
				return 1
			fi
		fi
	else
		# The old user has a different name and was not touched. Remove the
		# new user v-add-sys-roundcube may have created.
		mysql_query "DROP USER IF EXISTS '$NEW_DB_USER'@'localhost', '$NEW_DB_USER'@'127.0.0.1', '$NEW_DB_USER'@'::1', '$NEW_DB_USER'@'%'" || true
	fi

	# Only touch grants when the old database/user was actually removed or the
	# new one may have replaced it. A failure before any destructive step must
	# leave the old account exactly as it was.
	if [[ "$recreate_old_db" -eq 1 || "$OLD_USER_DROPPED" -eq 1 || "$VADD_ATTEMPTED" -eq 1 ]]; then
		query="GRANT ALL ON \`$OLD_DB_NAME\`.* TO '$escaped_user'@'$escaped_host'"
		mysql_query "$query" || true
	fi

	if [[ "$recreate_old_db" -eq 1 ]]; then
		if ! "$MYSQL_CLIENT_BIN" --defaults-file="$mycnf" "$OLD_DB_NAME" < "$dump_file"; then
			echo "[ ! ] CRITICAL: could not re-import the old database dump" >&2
			return 1
		fi
	fi

	return 0
}

restore_path() {
	local existed="$1"
	local backed_up="$2"
	local backup_path="$3"
	local destination="$4"

	if [[ "$backed_up" -eq 1 || -e "$backup_path" || -L "$backup_path" ]]; then
		restore_backup_directory "$backup_path" "$destination"
	elif [[ "$existed" -eq 0 ]]; then
		# The path did not exist before the migration; remove anything the new
		# install created there.
		rm -rf "$destination"
	fi
}

rollback() {
	local reason="$1"
	local failed=0
	local restored_old=0
	local restored_new=0

	if [[ "$ROLLING_BACK" -eq 1 ]]; then
		return
	fi
	ROLLING_BACK=1

	# A second INT/TERM/HUP must not abort a rollback halfway through.
	trap '' INT TERM HUP

	echo "[ ! ] Rolling back Roundcube migration: $reason"

	if [[ -z "$BACKUP_DIR" ]]; then
		echo "[ * ] No files or databases had been changed yet"
		return
	fi

	if [[ "$DESTRUCTIVE_STARTED" -eq 0 && -z "$STAGING_DIR" ]]; then
		echo "[ * ] No files or databases had been changed yet"
		return
	fi

	if [[ "$ROLLBACK_DISABLED" -eq 1 || "$PURGE_ONLY" == "yes" ]]; then
		echo "[ ! ] Package removal had already started; restoring the new Hestia install instead"
		if [[ -n "$STAGING_DIR" && -d "$STAGING_DIR" ]]; then
			restore_staged_directory "$STAGING_DIR/var-lib-roundcube" "$RC_INSTALL_DIR" || failed=1
			restore_staged_directory "$STAGING_DIR/etc-roundcube" "$RC_CONFIG_DIR" || failed=1
			restore_staged_directory "$STAGING_DIR/var-log-roundcube" "$RC_LOG_DIR" || failed=1
			rmdir "$STAGING_DIR" 2> /dev/null || true
			STAGING_DIR=""
			restored_new=1
		fi
	else
		restore_path "$ORIGINAL_CONFIG_EXISTED" "$BACKED_UP_CONFIG" "$BACKUP_DIR/files/etc-roundcube" "$RC_CONFIG_DIR" || failed=1
		restore_path "$ORIGINAL_INSTALL_EXISTED" "$BACKED_UP_INSTALL" "$BACKUP_DIR/files/var-lib-roundcube" "$RC_INSTALL_DIR" || failed=1
		restore_path "$ORIGINAL_SHARE_EXISTED" "$BACKED_UP_SHARE" "$BACKUP_DIR/files/usr-share-roundcube" "$RC_SHARE_DIR" || failed=1
		restored_old=1

		restore_old_database || failed=1

		# Validate the restored old database when it was actually rebuilt.
		if [[ "$OLD_DB_DROPPED" -eq 1 || "$VADD_ATTEMPTED" -eq 1 ]]; then
			if ! capture_row_counts "$BACKUP_DIR/db/rowcounts-after-rollback.tsv" "$OLD_DB_NAME"; then
				failed=1
			elif ! compare_row_counts "${OLD_DB_BASELINE:-$BACKUP_DIR/db/rowcounts-before.tsv}" "$BACKUP_DIR/db/rowcounts-after-rollback.tsv" "after rollback"; then
				failed=1
			fi
		fi
	fi

	if [[ "$failed" -eq 0 ]]; then
		if [[ "$restored_old" -eq 1 ]]; then
			echo "[ + ] Original Roundcube installation restored"
			add_rc_message "Roundcube migration failed: $reason\nThe original distribution-packaged Roundcube installation was restored. A complete backup is retained at: $BACKUP_DIR"
		elif [[ "$restored_new" -eq 1 ]]; then
			echo "[ * ] New Hestia Roundcube installation was restored after an interrupted package purge"
			add_rc_message "Roundcube package removal was interrupted; the new Hestia Roundcube installation was restored. Inspect $BACKUP_DIR/cleanup.log and retry cleanup."
		else
			echo "[ * ] No Roundcube files or databases needed restoring"
		fi
	else
		echo "[ ! ] CRITICAL: automatic rollback was incomplete; inspect $BACKUP_DIR" >&2
		add_rc_message "CRITICAL: Roundcube migration failed: $reason\nAutomatic rollback was incomplete. Restore from the backup at: $BACKUP_DIR"
	fi
}

fail() {
	local reason="$1"

	echo "[ ! ] ERROR: $reason" >&2
	rollback "$reason"
	exit 1
}

# shellcheck disable=SC2329 # invoked through trap
handle_signal() {
	fail "interrupted by signal"
}

trap handle_signal INT TERM HUP

verify_roundcube_install() {
	local version

	[[ -f "$RC_INSTALL_DIR/program/include/iniset.php" ]] || {
		echo "[ ! ] New Roundcube installation is missing iniset.php" >&2
		return 1
	}
	[[ -f "$RC_CONFIG_DIR/config.inc.php" ]] || {
		echo "[ ! ] New Roundcube configuration is missing" >&2
		return 1
	}

	version="$(roundcube_version "$RC_INSTALL_DIR/program/include/iniset.php")"
	if [[ -z "$version" ]]; then
		echo "[ ! ] Could not determine the installed Roundcube version" >&2
		return 1
	fi
	if [[ "$SKIP_VERSION_CHECK" -ne 1 && "$version" != "$rc_v" ]]; then
		echo "[ ! ] New Roundcube version is $version, expected $rc_v" >&2
		return 1
	fi
}

verify_roundcube_credentials() {
	local new_db_values

	if ! new_db_values="$("$PHP_CLI" "$RC_CONFIG_TOOL" db-config "$RC_CONFIG_DIR/config.inc.php")"; then
		return 1
	fi
	eval "$new_db_values"

	if [[ "$RC_DB_HOST" == "localhost" ]]; then
		MYSQL_PWD="$RC_DB_PASS" "$MYSQL_CLIENT_BIN" --no-defaults -u "$RC_DB_USER" "$RC_DB_NAME" -e 'SELECT 1' > /dev/null 2>&1
	else
		MYSQL_PWD="$RC_DB_PASS" "$MYSQL_CLIENT_BIN" --no-defaults -h "$RC_DB_HOST" -P "${RC_DB_PORT:-3306}" \
			-u "$RC_DB_USER" "$RC_DB_NAME" -e 'SELECT 1' > /dev/null 2>&1
	fi
}

ensure_old_custom_database() {
	local old_db_exists

	[[ "$OLD_DB_NAME" != "$NEW_DB_NAME" ]] || return 0
	if ! old_db_exists="$(mysql_query_raw "SELECT SCHEMA_NAME FROM information_schema.schemata WHERE schema_name = '$(mysql_sql_escape "$OLD_DB_NAME")'")"; then
		echo "[ ! ] Could not determine whether the old custom database $OLD_DB_NAME exists" >&2
		return 1
	fi
	[[ -z "$old_db_exists" ]] || return 0

	[[ -s "$(current_db_dump)" ]] || return 1
	echo "[ ! ] The old custom database '$OLD_DB_NAME' disappeared during package removal; restoring it from the backup" >&2
	mysql_query "CREATE DATABASE IF NOT EXISTS \`$OLD_DB_NAME\` CHARACTER SET utf8" || return 1
	"$MYSQL_CLIENT_BIN" --defaults-file="$mycnf" "$OLD_DB_NAME" < "$(current_db_dump)"
}

repair_new_database() {
	local force_import="${1:-0}"
	local new_db_values
	local escaped_password
	local escaped_user
	local escaped_host
	local query
	local db_exists
	local table_count

	if ! new_db_values="$("$PHP_CLI" "$RC_CONFIG_TOOL" db-config "$RC_CONFIG_DIR/config.inc.php")"; then
		return 1
	fi
	eval "$new_db_values"

	escaped_password="$(mysql_sql_escape "$RC_DB_PASS")"
	escaped_user="$(mysql_sql_escape "$RC_DB_USER")"
	escaped_host="$(mysql_sql_escape "$RC_DB_HOST")"

	if ! db_exists="$(mysql_query_raw "SELECT SCHEMA_NAME FROM information_schema.schemata WHERE schema_name = '$(mysql_sql_escape "$NEW_DB_NAME")'")"; then
		echo "[ ! ] Could not determine whether database $NEW_DB_NAME exists; refusing to import over it" >&2
		return 1
	fi

	# A credential failure does not mean the database is gone. If it still
	# exists, only recreate the user and grants; never drop live data.
	mysql_query "CREATE DATABASE IF NOT EXISTS \`$NEW_DB_NAME\` CHARACTER SET utf8" || return 1
	mysql_query "DROP USER IF EXISTS '$escaped_user'@'$escaped_host'" || true
	mysql_query "CREATE USER '$escaped_user'@'$escaped_host' IDENTIFIED BY '$escaped_password'" || return 1
	mysql_query "GRANT ALL ON \`$NEW_DB_NAME\`.* TO '$escaped_user'@'$escaped_host'" || return 1

	if [[ -n "$db_exists" ]]; then
		if ! table_count="$(mysql_query_raw "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$(mysql_sql_escape "$NEW_DB_NAME")'")"; then
			echo "[ ! ] Could not determine the table count in $NEW_DB_NAME; refusing to import over it" >&2
			return 1
		fi
		if [[ ! "$table_count" =~ ^[0-9]+$ ]]; then
			echo "[ ! ] Invalid table count '$table_count' in $NEW_DB_NAME; refusing to import over it" >&2
			return 1
		fi
		if [[ "$table_count" -gt 0 && "$force_import" -ne 1 ]]; then
			return 0
		fi
		if [[ "$table_count" -gt 0 ]]; then
			"$MYSQL_DUMP_BIN" --defaults-file="$mycnf" --single-transaction --quick \
				--routines --events --add-drop-table --skip-lock-tables \
				"$NEW_DB_NAME" > "$BACKUP_DIR/db/roundcube-before-repair.sql" 2> /dev/null || return 1
			[[ -s "$BACKUP_DIR/db/roundcube-before-repair.sql" ]] || return 1
		fi
	fi

	if [[ ! -s "$(new_db_repair_dump)" ]]; then
		echo "[ ! ] Database is empty and the backup dump is missing; cannot repair it" >&2
		return 1
	fi

	if ! "$MYSQL_CLIENT_BIN" --defaults-file="$mycnf" "$NEW_DB_NAME" < "$(new_db_repair_dump)"; then
		return 1
	fi

	if ! "$PHP_CLI" -d disable_functions= "$RC_INSTALL_DIR/bin/update.sh" \
		--version "$old_version" < /dev/null >> "$BACKUP_DIR/update-after-purge.log" 2>&1; then
		return 1
	fi

	return 0
}

neutralize_dbconfig_remove() {
	local config
	local dbconfig_backup="$BACKUP_DIR/dbconfig-common"

	DB_CONFIG_FILES=()
	mkdir -p "$dbconfig_backup" || return 1

	for config in /etc/dbconfig-common/roundcube.conf /etc/dbconfig-common/roundcube-core.conf; do
		[[ -f "$config" ]] || continue
		cp -a "$config" "$dbconfig_backup/" || return 1
		DB_CONFIG_FILES+=("$config")
		sed -i -E "s/^dbc_remove=.*/dbc_remove='false'/" "$config" || return 1
		sed -i -E "s/^dbc_upgrade=.*/dbc_upgrade='false'/" "$config" || return 1
		grep -q "^dbc_remove=" "$config" || echo "dbc_remove='false'" >> "$config"
		grep -q "^dbc_upgrade=" "$config" || echo "dbc_upgrade='false'" >> "$config"
	done

	return 0
}

restore_dbconfig_remove() {
	local config
	local name

	for config in "${DB_CONFIG_FILES[@]}"; do
		name="$(basename "$config")"
		if [[ -f "$BACKUP_DIR/dbconfig-common/$name" ]]; then
			cp -a "$BACKUP_DIR/dbconfig-common/$name" "$config" || true
		fi
	done
}

run_package_manager_remove() {
	local package_manager="$1"
	shift
	local packages=("$@")

	case "$package_manager" in
		apt)
			DEBIAN_FRONTEND=noninteractive apt-get purge -y \
				-o DPkg::Lock::Timeout=3600 \
				-o Dpkg::Options::=--force-confdef \
				-o Dpkg::Options::=--force-confold \
				"${packages[@]}"
			;;
		dnf)
			dnf remove -y "${packages[@]}"
			;;
		yum)
			yum remove -y "${packages[@]}"
			;;
		*)
			return 1
			;;
	esac
}

purge_distribution_packages() {
	local package
	local purge_failed=0
	local packages=()

	while IFS=$'\t' read -r package _; do
		[[ -n "$package" ]] && packages+=("$package")
	done < "$BACKUP_DIR/packages/installed.tsv"

	if [[ ${#packages[@]} -eq 0 ]]; then
		echo "[ ! ] No installed distribution Roundcube packages were found to remove"
		return 0
	fi

	echo "[ * ] Removing distribution Roundcube packages: ${packages[*]}"
	DESTRUCTIVE_STARTED=1
	STAGING_DIR="$BACKUP_DIR/new-install-staging"
	if [[ -d "$STAGING_DIR" ]]; then
		rmdir "$STAGING_DIR" 2> /dev/null || {
			echo "[ ! ] Existing staging directory found: $STAGING_DIR" >&2
			echo "[ ! ] Refusing to remove it automatically; inspect/restore it from the backup, then retry" >&2
			return 2
		}
	fi
	mkdir -p "$STAGING_DIR" || return 2

	# Keep the new Hestia files out of dpkg's way: the old package owns paths
	# such as /var/lib/roundcube/index.php and /etc/roundcube, and a purge must
	# not delete the files we just installed.
	mv "$RC_INSTALL_DIR" "$STAGING_DIR/var-lib-roundcube" || return 2
	if [[ -d "$RC_CONFIG_DIR" ]]; then
		mv "$RC_CONFIG_DIR" "$STAGING_DIR/etc-roundcube" || return 2
	fi
	if [[ -d "$RC_LOG_DIR" ]]; then
		mv "$RC_LOG_DIR" "$STAGING_DIR/var-log-roundcube" || return 2
	fi

	# The webmail files are out of the way, so no new requests can start.
	# Take and verify a fresh snapshot before removing the packages.
	if ! capture_row_counts "$BACKUP_DIR/db/rowcounts-before-purge.tsv" "$NEW_DB_NAME"; then
		echo "[ ! ] Could not record row counts before package removal" >&2
		return 2
	fi
	if ! "$MYSQL_DUMP_BIN" --defaults-file="$mycnf" --single-transaction --quick \
		--routines --events --add-drop-table --skip-lock-tables \
		"$NEW_DB_NAME" > "$BACKUP_DIR/db/roundcube-before-purge.sql" 2> "$BACKUP_DIR/db/dump-before-purge.err"; then
		echo "[ ! ] Could not take a fresh database snapshot before package removal" >&2
		return 2
	fi
	if ! verify_dump_file "$BACKUP_DIR/db/roundcube-before-purge.sql" "$BACKUP_DIR/db/rowcounts-before-purge.tsv" "before purge"; then
		echo "[ ! ] The pre-purge database snapshot could not be verified" >&2
		return 2
	fi
	NEW_DB_REPAIR_DUMP="$BACKUP_DIR/db/roundcube-before-purge.sql"
	NEW_DB_REPAIR_BASELINE="$VERIFIED_DUMP_BASELINE"

	if ! neutralize_dbconfig_remove; then
		restore_dbconfig_remove
		restore_staged_directory "$STAGING_DIR/var-lib-roundcube" "$RC_INSTALL_DIR" || return 2
		restore_staged_directory "$STAGING_DIR/etc-roundcube" "$RC_CONFIG_DIR" || return 2
		restore_staged_directory "$STAGING_DIR/var-log-roundcube" "$RC_LOG_DIR" || return 2
		rmdir "$STAGING_DIR" 2> /dev/null || true
		STAGING_DIR=""
		return 2
	fi

	ROLLBACK_DISABLED=1

	run_package_manager_remove "$PACKAGE_MANAGER" "${packages[@]}" || purge_failed=1

	mkdir -p "$(dirname "$RC_INSTALL_DIR")" "$(dirname "$RC_CONFIG_DIR")" "$(dirname "$RC_LOG_DIR")"
	restore_staged_directory "$STAGING_DIR/var-lib-roundcube" "$RC_INSTALL_DIR" || return 2
	restore_staged_directory "$STAGING_DIR/etc-roundcube" "$RC_CONFIG_DIR" || return 2
	restore_staged_directory "$STAGING_DIR/var-log-roundcube" "$RC_LOG_DIR" || return 2
	rmdir "$STAGING_DIR" 2> /dev/null || true
	STAGING_DIR=""

	if [[ "$purge_failed" -ne 0 ]]; then
		echo "[ ! ] The distribution Roundcube packages could not be removed completely" >&2
	fi

	# Verify and repair regardless of whether the package purge fully succeeded;
	# a partial purge can still have damaged the database.
	if ! verify_roundcube_install; then
		echo "[ ! ] Roundcube files/config failed verification after package removal" >&2
		return 2
	fi

	if ! verify_roundcube_credentials; then
		echo "[ ! ] Roundcube database credentials failed after package removal; repairing user/grants or an empty database" >&2
		repair_new_database || return 2
		verify_roundcube_credentials || return 2
	fi

	ensure_old_custom_database || return 2

	if ! capture_row_counts "$BACKUP_DIR/db/rowcounts-after-purge.tsv" "$NEW_DB_NAME"; then
		echo "[ ! ] Could not read the database after package removal; attempting repair" >&2
		repair_new_database || return 2
		verify_roundcube_credentials || return 2
		capture_row_counts "$BACKUP_DIR/db/rowcounts-after-purge.tsv" "$NEW_DB_NAME" || return 2
	fi
	compare_row_counts "$BACKUP_DIR/db/rowcounts-before-purge.tsv" "$BACKUP_DIR/db/rowcounts-after-purge.tsv" "after package removal"
	local compare_rc=$?
	if [[ "$compare_rc" -eq 2 ]]; then
		echo "[ ! ] Tables disappeared after package removal; restoring the database from the backup" >&2
		repair_new_database 1 || return 2
		verify_roundcube_credentials || return 2
		capture_row_counts "$BACKUP_DIR/db/rowcounts-after-repair.tsv" "$NEW_DB_NAME" || return 2
		compare_row_counts "${NEW_DB_REPAIR_BASELINE:-$BACKUP_DIR/db/rowcounts-before-purge.tsv}" "$BACKUP_DIR/db/rowcounts-after-repair.tsv" "after repair"
		local repair_compare_rc=$?
		if [[ "$repair_compare_rc" -ne 0 ]]; then
			echo "[ ! ] Tables or rows are still missing after the repair" >&2
			return 2
		fi
	elif [[ "$compare_rc" -eq 1 ]]; then
		# Writes may legitimately happen while packages are removed. Keep the
		# live database; do not re-import the older dump.
		echo "[ ! ] Rows changed while the distribution packages were removed; inspect the database and backup" >&2
	fi

	if ! remaining_packages="$(installed_roundcube_packages)"; then
		echo "[ ! ] Could not determine whether Roundcube packages remain installed" >&2
		return 1
	fi
	if [[ -n "$remaining_packages" || "$purge_failed" -ne 0 ]]; then
		echo "[ ! ] Some Roundcube packages are still installed; keeping dbconfig-common removal disabled" >&2
		return 1
	fi

	# The packages are gone; their dbconfig-common metadata is no longer useful.
	rm -f "${DB_CONFIG_FILES[@]}"
	PACKAGES_REMOVED=1
	return 0
}

mark_cleanup_scheduled() {
	if [[ -n "${HESTIA_BACKUP:-}" && -d "$HESTIA_BACKUP" ]]; then
		printf '%s\n' "$BACKUP_DIR" > "$HESTIA_BACKUP/roundcube-package-cleanup-scheduled" || true
	fi
}

schedule_package_removal() {
	local cleanup_script="$BACKUP_DIR/cleanup-packages.sh"
	local unit
	unit="hestia-roundcube-purge-$(date +%s)-$$"

	[[ -f "$BACKUP_DIR/packages/installed.tsv" ]] || return 0
	grep -q . "$BACKUP_DIR/packages/installed.tsv" || return 0

	{
		echo '#!/bin/bash'
		if [[ -n "${HESTIA_BACKUP:-}" ]]; then
			printf 'export HESTIA_BACKUP=%q\n' "$HESTIA_BACKUP"
		fi
		printf 'exec %q --purge-only --backup-dir %q >> %q 2>&1\n' \
			"$HESTIA/install/upgrade/migrate_roundcube.sh" \
			"$BACKUP_DIR" \
			"$BACKUP_DIR/cleanup.log"
	} > "$cleanup_script" || return 1
	chmod 700 "$cleanup_script" || return 1

	if command -v systemd-run > /dev/null 2>&1 && [[ -d /run/systemd/system ]]; then
		if systemd-run --on-active=2 --collect --unit="$unit" \
			--description="Remove distribution Roundcube packages" \
			/bin/bash "$cleanup_script"; then
			mark_cleanup_scheduled
			return 0
		fi
		echo "[ ! ] systemd-run could not schedule package removal; trying a detached fallback" >&2
	fi

	if command -v setsid > /dev/null 2>&1; then
		setsid /bin/bash "$cleanup_script" > /dev/null 2>&1 < /dev/null &
		mark_cleanup_scheduled
		return 0
	fi

	return 1
}

wait_for_dpkg_lock() {
	local attempt
	local lock_files=(/var/lib/dpkg/lock-frontend /var/lib/dpkg/lock /var/cache/apt/archives/lock)
	local lock_tool=""

	if command -v fuser > /dev/null 2>&1; then
		lock_tool="fuser"
	elif command -v lsof > /dev/null 2>&1; then
		lock_tool="lsof"
	else
		echo "[ ! ] Neither fuser nor lsof is available; cannot wait for the apt/dpkg lock" >&2
		return 1
	fi

	for attempt in $(seq 1 1800); do
		if [[ "$lock_tool" == "fuser" ]]; then
			if ! fuser "${lock_files[@]}" > /dev/null 2>&1; then
				return 0
			fi
		else
			if ! lsof "${lock_files[@]}" > /dev/null 2>&1; then
				return 0
			fi
		fi
		sleep 2
	done

	return 1
}

recover_staged_install() {
	local staging="$BACKUP_DIR/new-install-staging"

	[[ -d "$staging" ]] || return 0

	if ! is_hestia_roundcube && [[ -e "$staging/var-lib-roundcube" ]]; then
		rm -rf "$RC_INSTALL_DIR"
		mv "$staging/var-lib-roundcube" "$RC_INSTALL_DIR" || return 1
	fi
	if [[ ! -f "$RC_CONFIG_DIR/config.inc.php" && -e "$staging/etc-roundcube" ]]; then
		rm -rf "$RC_CONFIG_DIR"
		mv "$staging/etc-roundcube" "$RC_CONFIG_DIR" || return 1
	fi
	if [[ ! -d "$RC_LOG_DIR" && -e "$staging/var-log-roundcube" ]]; then
		mv "$staging/var-log-roundcube" "$RC_LOG_DIR" || return 1
	fi

	if rmdir "$staging" 2> /dev/null; then
		return 0
	fi

	mv "$staging" "${staging}.stale-$(date +%s)" || return 1
	echo "[ ! ] Left unused staged files at ${staging}.stale-*; inspect before deleting" >&2
	return 0
}

purge_only_main() {
	local key
	local value
	local source_version=""
	local target_version=""
	local db_name=""

	if [[ "$(id -u)" != "0" ]]; then
		echo "ERROR: This script must be run as root" >&2
		return 1
	fi

	if [[ -z "$BACKUP_DIR" || ! -d "$BACKUP_DIR" ]]; then
		echo "ERROR: --purge-only requires an existing --backup-dir" >&2
		return 1
	fi

	if [[ ! -f "$BACKUP_DIR/migration-complete" ]]; then
		echo "ERROR: $BACKUP_DIR does not contain a completed migration marker; refusing to remove packages" >&2
		return 1
	fi

	if ! recover_staged_install; then
		echo "ERROR: could not recover a previously staged Hestia install" >&2
		return 1
	fi

	if ! is_hestia_roundcube || is_distribution_roundcube_layout || [[ -d "$RC_SHARE_DIR" ]]; then
		echo "ERROR: $RC_INSTALL_DIR is not a completed Hestia-managed Roundcube install; refusing to remove distribution packages" >&2
		return 1
	fi

	if ! wait_for_dpkg_lock; then
		echo "ERROR: timed out waiting for the apt/dpkg lock" >&2
		return 1
	fi

	if [[ -f "$BACKUP_DIR/metadata" ]]; then
		while IFS='=' read -r key value; do
			case "$key" in
				source_version) source_version="$value" ;;
				target_version) target_version="$value" ;;
				db_name) db_name="$value" ;;
			esac
		done < "$BACKUP_DIR/metadata"
	fi

	[[ -n "$target_version" ]] && rc_v="$target_version"
	# A later Hestia/Roundcube upgrade may have advanced the install since the
	# deferred cleanup was scheduled. Do not require the old target version.
	SKIP_VERSION_CHECK=1
	old_version="$source_version"
	OLD_DB_NAME="${db_name:-roundcube}"
	NEW_DB_NAME="roundcube"
	NEW_DB_USER="roundcube"

	detect_package_manager
	[[ -n "$PACKAGE_MANAGER" ]] || {
		echo "ERROR: no supported package manager found" >&2
		return 1
	}

	MYSQL_DUMP_BIN="$(command -v mariadb-dump || command -v mysqldump || true)"
	MYSQL_CLIENT_BIN="$(command -v mariadb || command -v mysql || true)"
	[[ -n "$MYSQL_CLIENT_BIN" ]] || {
		echo "ERROR: MySQL/MariaDB client tools were not found" >&2
		return 1
	}

	if ! (mysql_connect localhost) > /dev/null 2>&1; then
		echo "ERROR: could not connect to the Hestia MySQL/MariaDB server" >&2
		return 1
	fi
	mycnf="$HESTIA/conf/.mysql.localhost"
	[[ -f "$mycnf" ]] || {
		echo "ERROR: Hestia MySQL defaults file was not created" >&2
		return 1
	}

	if ! verify_roundcube_install || ! verify_roundcube_credentials; then
		echo "ERROR: Hestia Roundcube install failed verification before package removal" >&2
		return 1
	fi

	purge_distribution_packages
	local purge_rc=$?
	if [[ "$purge_rc" -eq 2 ]]; then
		echo "ERROR: package removal left the Hestia install in an inconsistent state; attempting recovery" >&2
		rollback "deferred package removal failed"
		return 1
	elif [[ "$purge_rc" -eq 1 ]]; then
		echo "[ ! ] Distribution Roundcube packages remain installed; clean them up manually" >&2
		return 1
	fi

	echo "[ + ] Distribution Roundcube packages removed"
	echo "[ + ] Backup (kept): $BACKUP_DIR"
	return 0
}

if [[ "$PURGE_ONLY" == "yes" ]]; then
	purge_only_main
	purge_only_rc=$?
	if [[ -n "${HESTIA_BACKUP:-}" ]]; then
		rm -f "$HESTIA_BACKUP/roundcube-package-cleanup-scheduled" 2> /dev/null || true
		if [[ "$purge_only_rc" -ne 0 ]]; then
			if [[ -d "$HESTIA_BACKUP" ]]; then
				printf '%s\n' "$BACKUP_DIR" > "$HESTIA_BACKUP/roundcube-package-cleanup-failed" 2> /dev/null || true
			fi
		else
			rm -f "$HESTIA_BACKUP/roundcube-package-cleanup-failed" 2> /dev/null || true
		fi
	fi
	if [[ "$purge_only_rc" -ne 0 ]]; then
		echo "[ ! ] Deferred Roundcube package cleanup failed; see $BACKUP_DIR/cleanup.log" >&2
		add_rc_message "Deferred Roundcube package cleanup failed. Inspect $BACKUP_DIR/cleanup.log; the Hestia Roundcube install remains usable."
		exit 1
	fi
	exit 0
fi

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

if [[ "$(id -u)" != "0" ]]; then
	echo "ERROR: This script must be run as root" >&2
	exit 1
fi

if [[ -z "${WEBMAIL_SYSTEM:-}" ]] || ! grep -qw 'roundcube' <<< "$WEBMAIL_SYSTEM"; then
	echo "[ * ] Roundcube is not enabled in WEBMAIL_SYSTEM; nothing to migrate"
	exit 0
fi

if [[ ! -d "$RC_SHARE_DIR" ]]; then
	if is_hestia_roundcube; then
		echo "[ * ] Roundcube is already managed by Hestia ($RC_INSTALL_DIR)"
		if remaining_packages="$(installed_roundcube_packages)"; then
			if [[ -n "$remaining_packages" ]]; then
				echo "[ ! ] Distribution Roundcube packages are still installed; retry cleanup with:" >&2
				echo "      $0 --purge-only --backup-dir <roundcube-apt-migration backup directory>" >&2
				add_rc_message "Hestia Roundcube is installed, but distribution Roundcube packages remain. Retry cleanup with $0 --purge-only --backup-dir <backup directory>."
			fi
		else
			echo "[ ! ] Could not determine whether distribution Roundcube packages remain installed" >&2
		fi
	else
		echo "[ * ] No distribution-packaged Roundcube installation found; nothing to migrate"
	fi
	exit 0
fi

if is_hestia_roundcube; then
	skip_migration "both $RC_SHARE_DIR and a real Hestia Roundcube install in $RC_INSTALL_DIR exist"
fi

if [[ -d "$RC_INSTALL_DIR" ]] && ! is_distribution_roundcube_layout; then
	skip_migration "both $RC_SHARE_DIR and an unrecognized installation in $RC_INSTALL_DIR exist"
fi

if ! grep -qw 'mysql' <<< "${DB_SYSTEM:-}"; then
	skip_migration "Hestia is not using MySQL/MariaDB (DB_SYSTEM=${DB_SYSTEM:-unset})"
fi

if [[ -z "$PHP_CLI" || ! -x "$PHP_CLI" ]]; then
	skip_migration "php-cli was not found"
fi

if [[ ! -f "$RC_CONFIG_TOOL" ]]; then
	skip_migration "configuration helper $RC_CONFIG_TOOL is missing"
fi

if [[ ! -f "$RC_CONFIG_DIR/config.inc.php" ]]; then
	skip_migration "$RC_CONFIG_DIR/config.inc.php is missing"
fi

version_file="$RC_SHARE_DIR/program/include/iniset.php"
old_version=""
if [[ -f "$version_file" ]]; then
	old_version="$(roundcube_version "$version_file")"
fi
if [[ -z "$old_version" ]]; then
	skip_migration "could not determine the installed distribution Roundcube version"
fi
if [[ "$old_version" != "$rc_v" ]] \
	&& [[ "$(printf '%s\n%s\n' "$old_version" "$rc_v" | sort -V | tail -n 1)" == "$old_version" ]]; then
	skip_migration "installed distribution Roundcube $old_version is newer than Hestia's target $rc_v"
fi

detect_package_manager
if [[ -z "$PACKAGE_MANAGER" ]]; then
	skip_migration "no supported package manager found"
fi

MYSQL_DUMP_BIN="$(command -v mariadb-dump || command -v mysqldump || true)"
MYSQL_CLIENT_BIN="$(command -v mariadb || command -v mysql || true)"
if [[ -z "$MYSQL_DUMP_BIN" || -z "$MYSQL_CLIENT_BIN" ]]; then
	skip_migration "MySQL/MariaDB client tools were not found"
fi

# func/db.sh's mysql_connect exits the shell on failure. Run it in a subshell
# to contain that exit, then use the defaults file it creates.
if ! (mysql_connect localhost) > /dev/null 2>&1; then
	abort_early "could not connect to the Hestia MySQL/MariaDB server"
fi
mycnf="$HESTIA/conf/.mysql.localhost"
[[ -f "$mycnf" ]] || abort_early "Hestia MySQL defaults file was not created"

if ! db_values="$("$PHP_CLI" "$RC_CONFIG_TOOL" db-config "$RC_CONFIG_DIR/config.inc.php")"; then
	skip_migration "could not read the database settings from the distribution Roundcube configuration"
fi
eval "$db_values"

OLD_DB_BACKEND="$RC_DB_BACKEND"
OLD_DB_NAME="$RC_DB_NAME"
OLD_DB_USER="$RC_DB_USER"
OLD_DB_PASS="$RC_DB_PASS"
OLD_DB_HOST="$RC_DB_HOST"
OLD_DB_PORT="$RC_DB_PORT"
OLD_DB_PREFIX="${RC_DB_PREFIX:-}"
OLD_DB_PREFIX_UNKNOWN="${RC_DB_PREFIX_UNKNOWN:-0}"

if [[ "$OLD_DB_PREFIX_UNKNOWN" == "1" ]]; then
	skip_migration "Roundcube has a non-literal db_prefix setting; cannot safely preserve table names"
fi
if [[ -n "$OLD_DB_PREFIX" ]]; then
	skip_migration "Roundcube uses table prefix '$OLD_DB_PREFIX'; Hestia does not support preserving table prefixes"
fi
if [[ "$OLD_DB_NAME" != "roundcube" && "$OLD_DB_USER" == "roundcube" ]]; then
	skip_migration "Roundcube uses a custom database name with the default 'roundcube' user; migrate it manually to avoid orphaning its grants"
fi

# v-add-sys-roundcube always uses these names for a MySQL install.
NEW_DB_NAME="roundcube"
NEW_DB_USER="roundcube"

if [[ "${RC_DB_BACKEND:-}" != "mysql" ]]; then
	skip_migration "unsupported Roundcube database backend: ${RC_DB_BACKEND:-unknown}"
fi

if [[ ! "$RC_DB_NAME" =~ ^[A-Za-z0-9_.-]+$ ]] \
	|| [[ ! "$RC_DB_USER" =~ ^[A-Za-z0-9_.-]+$ ]] \
	|| [[ ! "$RC_DB_HOST" =~ ^[A-Za-z0-9_.:-]+$ ]] \
	|| [[ ! "$RC_DB_PORT" =~ ^[0-9]*$ ]]; then
	skip_migration "the distribution Roundcube database settings contain unsupported characters"
fi

if [[ "$RC_DB_HOST" != "localhost" && "$RC_DB_HOST" != "127.0.0.1" && "$RC_DB_HOST" != "::1" ]]; then
	skip_migration "the Roundcube database is not local (host: $RC_DB_HOST)"
fi

table_list="$(mysql_query_raw "SELECT table_name FROM information_schema.tables WHERE table_schema = '$(mysql_sql_escape "$OLD_DB_NAME")' AND table_type = 'BASE TABLE' ORDER BY table_name")"
if [[ -z "$table_list" ]]; then
	skip_migration "database '$OLD_DB_NAME' has no tables; refusing to destroy an unexpected database"
fi
if ! grep -qx 'users' <<< "$table_list" || ! grep -qx 'identities' <<< "$table_list"; then
	skip_migration "database '$OLD_DB_NAME' does not contain Roundcube's users/identities tables; refusing to replace it"
fi

# v-add-sys-roundcube uses the fixed names "roundcube"/"roundcube". Do not
# touch them when the old install uses different names: that would destroy an
# unrelated existing Hestia Roundcube database or user.
if [[ "$OLD_DB_NAME" != "$NEW_DB_NAME" ]]; then
	if ! new_db_exists="$(mysql_query_raw "SELECT SCHEMA_NAME FROM information_schema.schemata WHERE schema_name = '$NEW_DB_NAME'")"; then
		skip_migration "could not check whether database '$NEW_DB_NAME' already exists"
	fi
	[[ -z "$new_db_exists" ]] || skip_migration "database '$NEW_DB_NAME' already exists; refusing to overwrite it"
fi
if [[ "$OLD_DB_USER" != "$NEW_DB_USER" ]]; then
	if ! new_user_exists="$(mysql_query_raw "SELECT User FROM mysql.user WHERE User = '$NEW_DB_USER' LIMIT 1")"; then
		skip_migration "could not check whether database user '$NEW_DB_USER' already exists"
	fi
	[[ -z "$new_user_exists" ]] || skip_migration "database user '$NEW_DB_USER' already exists; refusing to overwrite it"
fi

if [[ "$OLD_DB_USER" == "$NEW_DB_USER" ]]; then
	if ! other_user_hosts="$(mysql_query_raw "SELECT Host FROM mysql.user WHERE User = '$NEW_DB_USER' AND Host <> '$(mysql_sql_escape "$OLD_DB_HOST")'")"; then
		skip_migration "could not check existing database user hosts"
	fi
	if [[ -n "$other_user_hosts" ]]; then
		skip_migration "database user '$OLD_DB_USER' exists for multiple hosts; refusing to drop its grants"
	fi

	if ! user_grants="$(mysql_query_raw "SHOW GRANTS FOR '$(mysql_sql_escape "$OLD_DB_USER")'@'$(mysql_sql_escape "$OLD_DB_HOST")'")"; then
		skip_migration "could not inspect grants for database user '$OLD_DB_USER'"
	fi
	unexpected_grants="$(while IFS= read -r grant_line; do
		[[ -n "$grant_line" ]] || continue
		[[ "$grant_line" == "GRANT USAGE ON *.*"* ]] && continue
		[[ "$grant_line" == *" ON \`$OLD_DB_NAME\`.* "* ]] && continue
		printf '%s\n' "$grant_line"
	done <<< "$user_grants")"
	if [[ -n "$unexpected_grants" ]]; then
		skip_migration "database user '$OLD_DB_USER' has grants outside the Roundcube database; refusing to drop them"
	fi
fi

if [[ "$ASSUME_YES" != "yes" ]]; then
	if [[ ! -t 0 ]]; then
		echo "[ ! ] Non-interactive session and --yes was not supplied; aborting"
		exit 0
	fi
	echo "Roundcube will be migrated from the distribution package to the Hestia-managed install."
	echo "The existing data and configuration are backed up under $RC_BACKUP_ROOT before any change."
	read -r -p "Continue? [y/N] " answer
	if [[ ! "$answer" =~ ^[Yy]$ ]]; then
		echo "Aborted"
		exit 0
	fi
fi

#----------------------------------------------------------#
#                    Backup & dump                         #
#----------------------------------------------------------#

timestamp="$(date +%Y%m%d_%H%M%S)"
BACKUP_DIR="$RC_BACKUP_ROOT/roundcube-apt-migration-$timestamp"
suffix=0
while [[ -e "$BACKUP_DIR" ]]; do
	suffix=$((suffix + 1))
	BACKUP_DIR="$RC_BACKUP_ROOT/roundcube-apt-migration-${timestamp}_$suffix"
done

mkdir -p "$BACKUP_DIR/db" "$BACKUP_DIR/files" "$BACKUP_DIR/packages" || abort_early "could not create backup directory $BACKUP_DIR"
chmod 700 "$BACKUP_DIR" || abort_early "could not secure backup directory $BACKUP_DIR"
echo "[ * ] Backup directory: $BACKUP_DIR"

if ! installed_packages="$(installed_roundcube_packages)"; then
	abort_early "could not determine which Roundcube packages are installed"
fi
printf '%s\n' "$installed_packages" > "$BACKUP_DIR/packages/installed.tsv"
cut -f 1 <<< "$installed_packages" | sed '/^$/d' > "$BACKUP_DIR/packages/names.txt"

{
	printf 'timestamp=%s\n' "$timestamp"
	printf 'source_version=%s\n' "$old_version"
	printf 'target_version=%s\n' "$rc_v"
	printf 'package_manager=%s\n' "$PACKAGE_MANAGER"
	printf 'db_backend=%s\n' "$OLD_DB_BACKEND"
	printf 'db_name=%s\n' "$OLD_DB_NAME"
	printf 'db_user=%s\n' "$OLD_DB_USER"
	printf 'db_host=%s\n' "$OLD_DB_HOST"
	printf 'db_port=%s\n' "$OLD_DB_PORT"
	printf 'db_pass=%s\n' "$OLD_DB_PASS"
} > "$BACKUP_DIR/metadata"
chmod 600 "$BACKUP_DIR/metadata"

if ! capture_row_counts "$BACKUP_DIR/db/rowcounts-before.tsv" "$OLD_DB_NAME"; then
	abort_early "could not record row counts from database $OLD_DB_NAME"
fi

echo "[ * ] Backing up the Roundcube database"
if ! "$MYSQL_DUMP_BIN" --defaults-file="$mycnf" --single-transaction --quick \
	--routines --events --add-drop-table --skip-lock-tables \
	"$OLD_DB_NAME" > "$BACKUP_DIR/db/roundcube.sql" 2> "$BACKUP_DIR/db/dump.err"; then
	abort_early "mysqldump failed: $(tail -n 1 "$BACKUP_DIR/db/dump.err")"
fi

if [[ ! -s "$BACKUP_DIR/db/roundcube.sql" ]] || ! grep -q '^CREATE TABLE' "$BACKUP_DIR/db/roundcube.sql"; then
	abort_early "the database dump is empty or does not contain a schema"
fi

OLD_DB_DUMP="$BACKUP_DIR/db/roundcube.sql"
OLD_DB_BASELINE="$BACKUP_DIR/db/rowcounts-before.tsv"

table_count="$(grep -c '^CREATE TABLE' "$BACKUP_DIR/db/roundcube.sql")"
expected_table_count="$(grep -c . <<< "$table_list")"
if [[ "$table_count" -lt "$expected_table_count" ]]; then
	abort_early "database dump contains $table_count of $expected_table_count tables"
fi

# Prove the dump can be imported before dropping the live database.
if ! verify_dump_file "$BACKUP_DIR/db/roundcube.sql" "$BACKUP_DIR/db/rowcounts-before.tsv" "initial"; then
	abort_early "the database dump could not be imported and verified in a scratch database"
fi
OLD_DB_BASELINE="$VERIFIED_DUMP_BASELINE"

#----------------------------------------------------------#
#                 Move files and reset DB                  #
#----------------------------------------------------------#

echo "[ * ] Moving the distribution Roundcube files into the backup"
DESTRUCTIVE_STARTED=1

if [[ -e "$RC_SHARE_DIR" || -L "$RC_SHARE_DIR" ]]; then
	ORIGINAL_SHARE_EXISTED=1
	backup_path_atomically "$RC_SHARE_DIR" "$BACKUP_DIR/files/usr-share-roundcube" || fail "could not back up $RC_SHARE_DIR"
	BACKED_UP_SHARE=1
	rm -rf "$RC_SHARE_DIR" || fail "could not remove $RC_SHARE_DIR"
fi
if [[ -e "$RC_CONFIG_DIR" || -L "$RC_CONFIG_DIR" ]]; then
	ORIGINAL_CONFIG_EXISTED=1
	backup_path_atomically "$RC_CONFIG_DIR" "$BACKUP_DIR/files/etc-roundcube" || fail "could not back up $RC_CONFIG_DIR"
	BACKED_UP_CONFIG=1
	rm -rf "$RC_CONFIG_DIR" || fail "could not remove $RC_CONFIG_DIR"
fi
if [[ -e "$RC_INSTALL_DIR" || -L "$RC_INSTALL_DIR" ]]; then
	ORIGINAL_INSTALL_EXISTED=1
	backup_path_atomically "$RC_INSTALL_DIR" "$BACKUP_DIR/files/var-lib-roundcube" || fail "could not back up $RC_INSTALL_DIR"
	BACKED_UP_INSTALL=1
	rm -rf "$RC_INSTALL_DIR" || fail "could not remove $RC_INSTALL_DIR"
fi

# The webmail files are gone, so no new requests can start. Take one final
# snapshot to minimize the window in which writes can be lost between the
# initial backup and the database swap.
echo "[ * ] Taking a final database snapshot"
if ! capture_row_counts "$BACKUP_DIR/db/rowcounts-before-import.tsv" "$OLD_DB_NAME"; then
	fail "could not record row counts before the final snapshot"
fi
if ! "$MYSQL_DUMP_BIN" --defaults-file="$mycnf" --single-transaction --quick \
	--routines --events --add-drop-table --skip-lock-tables \
	"$OLD_DB_NAME" > "$BACKUP_DIR/db/roundcube-final.sql" 2> "$BACKUP_DIR/db/dump-final.err"; then
	fail "final mysqldump failed: $(tail -n 1 "$BACKUP_DIR/db/dump-final.err")"
fi
if ! verify_dump_file "$BACKUP_DIR/db/roundcube-final.sql" "$BACKUP_DIR/db/rowcounts-before-import.tsv" "final"; then
	fail "the final database snapshot could not be verified"
fi
OLD_DB_DUMP="$BACKUP_DIR/db/roundcube-final.sql"
OLD_DB_BASELINE="$VERIFIED_DUMP_BASELINE"

echo "[ * ] Removing the old Roundcube database/user so v-add-sys-roundcube can create fresh ones"
if [[ "$OLD_DB_NAME" == "roundcube" ]]; then
	mysql_query "DROP DATABASE IF EXISTS \`roundcube\`" || fail "could not drop the old Roundcube database"
	OLD_DB_DROPPED=1
fi
if [[ "$OLD_DB_USER" == "roundcube" ]]; then
	escaped_user="$(mysql_sql_escape "$OLD_DB_USER")"
	escaped_host="$(mysql_sql_escape "$OLD_DB_HOST")"
	mysql_query "DROP USER IF EXISTS 'roundcube'@'$escaped_host'" \
		|| fail "could not drop the old Roundcube database user"
	OLD_USER_DROPPED=1
fi

#----------------------------------------------------------#
#                 Install Hestia Roundcube                 #
#----------------------------------------------------------#

echo "[ * ] Installing the Hestia-managed Roundcube $rc_v"
VADD_ATTEMPTED=1
if ! "$BIN/v-add-sys-roundcube"; then
	fail "v-add-sys-roundcube failed"
fi

if [[ ! -f "$RC_INSTALL_DIR/program/include/iniset.php" ]]; then
	fail "new Roundcube installation is missing $RC_INSTALL_DIR/program/include/iniset.php"
fi
new_version="$(roundcube_version "$RC_INSTALL_DIR/program/include/iniset.php")"
if [[ "$new_version" != "$rc_v" ]]; then
	fail "new Roundcube version is ${new_version:-unknown}, expected $rc_v"
fi
if [[ ! -f "$RC_CONFIG_DIR/config.inc.php" ]]; then
	fail "new Roundcube configuration is missing $RC_CONFIG_DIR/config.inc.php"
fi

# Roundcube 1.7.6's Pdo\Mysql class is only in PHP 8.4+ or the
# symfony/polyfill-php84 package. Ensure it exists before update.sh.
if ! ensure_roundcube_polyfill; then
	fail "Roundcube's required Pdo\\Mysql polyfill could not be installed"
fi

echo "[ * ] Preserving custom plugins, skins and plugin configuration"
if ! "$PHP_CLI" "$RC_CONFIG_TOOL" migrate-assets \
	--old-config-dir "$BACKUP_DIR/files/etc-roundcube" \
	--new-config-dir "$RC_CONFIG_DIR" \
	--old-install-dir "$BACKUP_DIR/files/usr-share-roundcube" \
	--new-install-dir "$RC_INSTALL_DIR"; then
	fail "could not copy custom plugins/skins or plugin configuration"
fi

echo "[ * ] Merging the old Roundcube configuration"
if ! "$PHP_CLI" "$RC_CONFIG_TOOL" merge-config \
	--old "$BACKUP_DIR/files/etc-roundcube/config.inc.php" \
	--new "$RC_CONFIG_DIR/config.inc.php" \
	--install-dir "$RC_INSTALL_DIR" \
	--config-dir "$RC_CONFIG_DIR"; then
	fail "could not merge the old Roundcube configuration"
fi

if ! "$PHP_CLI" -l "$RC_CONFIG_DIR/config.inc.php" > /dev/null; then
	fail "PHP syntax check failed for $RC_CONFIG_DIR/config.inc.php"
fi
while IFS= read -r -d '' config_file; do
	if ! "$PHP_CLI" -l "$config_file" > /dev/null; then
		fail "PHP syntax check failed for $config_file"
	fi
done < <(find "$RC_CONFIG_DIR/plugins" -type f -name '*.php' -print0 2> /dev/null)

if getent passwd hestiamail > /dev/null 2>&1; then
	chown -R hestiamail:www-data "$RC_CONFIG_DIR" "$RC_INSTALL_DIR" 2> /dev/null || true
fi

echo "[ * ] Restoring the Roundcube database contents"
# v-add-sys-roundcube creates the current schema. Drop its tables so the old
# snapshot is imported into an empty database; otherwise update.sh sees tables
# that did not exist in the old version (e.g. "uploads already exists").
if ! mysql_query "DROP DATABASE IF EXISTS \`$NEW_DB_NAME\`"; then
	fail "could not reset database '$NEW_DB_NAME' before importing the old data"
fi
if ! mysql_query "CREATE DATABASE IF NOT EXISTS \`$NEW_DB_NAME\` CHARACTER SET utf8"; then
	fail "could not recreate database '$NEW_DB_NAME' before importing the old data"
fi
if ! "$MYSQL_CLIENT_BIN" --defaults-file="$mycnf" "$NEW_DB_NAME" < "$(new_db_repair_dump)"; then
	fail "could not import the old database into database '$NEW_DB_NAME'"
fi

if ! capture_row_counts "$BACKUP_DIR/db/rowcounts-after-import.tsv" "$NEW_DB_NAME"; then
	fail "could not verify row counts after the database import"
fi
if ! compare_row_counts "${OLD_DB_BASELINE:-$BACKUP_DIR/db/rowcounts-before.tsv}" "$BACKUP_DIR/db/rowcounts-after-import.tsv" "after import"; then
	fail "database rows were not preserved by the import"
fi

echo "[ * ] Running the Roundcube database update from $old_version"
export COMPOSER_ALLOW_SUPERUSER=1
if ! "$PHP_CLI" -d disable_functions= "$RC_INSTALL_DIR/bin/update.sh" \
	--version "$old_version" < /dev/null >> "$BACKUP_DIR/update.log" 2>&1; then
	tail -n 20 "$BACKUP_DIR/update.log" >&2 || true
	fail "Roundcube update.sh failed (version $old_version -> $rc_v)"
fi

if ! capture_row_counts "$BACKUP_DIR/db/rowcounts-after-update.tsv" "$NEW_DB_NAME"; then
	fail "could not verify row counts after the Roundcube update"
fi
if ! compare_row_counts "${OLD_DB_BASELINE:-$BACKUP_DIR/db/rowcounts-before.tsv}" "$BACKUP_DIR/db/rowcounts-after-update.tsv" "after update"; then
	fail "database rows were not preserved by the Roundcube update"
fi

# Verify that Roundcube's own configuration can connect to the restored DB.
if ! new_db_values="$("$PHP_CLI" "$RC_CONFIG_TOOL" db-config "$RC_CONFIG_DIR/config.inc.php")"; then
	fail "could not read the new Roundcube database configuration"
fi
eval "$new_db_values"
if [[ "$RC_DB_HOST" == "localhost" ]]; then
	MYSQL_PWD="$RC_DB_PASS" "$MYSQL_CLIENT_BIN" --no-defaults -u "$RC_DB_USER" "$RC_DB_NAME" -e 'SELECT 1' > /dev/null 2>&1 \
		|| fail "Roundcube's database credentials do not work after the migration"
else
	MYSQL_PWD="$RC_DB_PASS" "$MYSQL_CLIENT_BIN" --no-defaults -h "$RC_DB_HOST" -P "${RC_DB_PORT:-3306}" \
		-u "$RC_DB_USER" "$RC_DB_NAME" -e 'SELECT 1' > /dev/null 2>&1 \
		|| fail "Roundcube's database credentials do not work after the migration"
fi

# The migration is complete at this point; package removal may be deferred.
touch "$BACKUP_DIR/migration-complete" || fail "could not write the migration completion marker"

#----------------------------------------------------------#
#                 Remove distribution packages              #
#----------------------------------------------------------#

if [[ "$DEFER_PACKAGE_REMOVAL" == "yes" ]]; then
	if schedule_package_removal; then
		echo "[ * ] Distribution package removal scheduled to run after apt releases its lock"
		add_rc_message "Roundcube was migrated to the Hestia-managed install at $RC_INSTALL_DIR. Removal of the distribution packages was scheduled to run after the Hestia package upgrade releases the apt/dpkg lock; the migration itself is complete. Backup: $BACKUP_DIR"
	else
		echo "[ ! ] Could not schedule distribution package removal; remove the packages manually" >&2
		add_rc_message "Roundcube was migrated to the Hestia-managed install at $RC_INSTALL_DIR, but the distribution packages could not be scheduled for removal while apt held its lock. Remove the remaining roundcube* packages manually. Backup: $BACKUP_DIR"
	fi
else
	purge_distribution_packages
	purge_rc=$?
	if [[ "$purge_rc" -eq 2 ]]; then
		fail "package removal left the Hestia install in an inconsistent state"
	elif [[ "$purge_rc" -eq 1 ]]; then
		echo "[ ! ] Roundcube works from $RC_INSTALL_DIR, but the distribution packages remain installed" >&2
		add_rc_message "Roundcube was migrated to the Hestia-managed install at $RC_INSTALL_DIR, but the distribution packages could not be removed completely. Remove them manually after checking the system. Backup: $BACKUP_DIR"
	else
		echo "[ + ] Distribution Roundcube packages removed"
	fi
fi

if [[ -e "$RC_SHARE_DIR" ]]; then
	echo "[ ! ] Warning: $RC_SHARE_DIR is still present after package removal" >&2
fi

echo "[ + ] Roundcube migrated from the distribution package to Hestia-managed $rc_v"
echo "[ + ] Backup (kept): $BACKUP_DIR"
if [[ "$OLD_DB_NAME" != "roundcube" ]]; then
	echo "[ ! ] The old custom database '$OLD_DB_NAME' was left in place; its dump is in $BACKUP_DIR/db/roundcube.sql"
fi

if [[ "$DEFER_PACKAGE_REMOVAL" != "yes" ]]; then
	if [[ "$PACKAGES_REMOVED" -eq 1 ]]; then
		add_rc_message "Roundcube was migrated automatically from the distribution package to the Hestia-managed version $rc_v.\n\nPreserved: database tables and rows, the old des_key, custom config values, plugin configuration, and compatible custom plugins/skins. The distribution packages were removed (no autoremove was used).\n\nBackup (never deleted automatically): $BACKUP_DIR"
	else
		add_rc_message "Roundcube was migrated to the Hestia-managed version $rc_v, but the distribution packages remain installed. Remove them manually.\n\nBackup (never deleted automatically): $BACKUP_DIR"
	fi
fi

exit 0
