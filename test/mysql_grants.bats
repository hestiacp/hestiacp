#!/usr/bin/env bats
#
# Unit tests for MySQL database grant escaping (GHSA-g95g-h6p5-m6c6).
#
# In a database-level GRANT, "_" and "%" are wildcards. Hestia names databases
# <user>_<name>, so an unescaped GRANT on `mys_l`.* also covered the `mysql`
# system schema, and `johnv_y_shop`.* covered `johnvny_shop` of another user.
#
# These tests need no MySQL server: mysql_query is replaced by a stub that
# records each query, so they also run outside the CI container.

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'

setup() {
	REPO_ROOT="$(cd "${BATS_TEST_DIRNAME}/.." && pwd)"
	# shellcheck source=func/db.sh
	source "${REPO_ROOT}/func/db.sh"

	QUERY_LOG="$(mktemp)"
	export QUERY_LOG
	mysql_query() { printf '%s\n' "$1" >> "$QUERY_LOG"; }
	mysql_connect() { printf 'CONNECT %s\n' "$1" >> "$QUERY_LOG"; }
}

teardown() {
	rm -f "$QUERY_LOG"
	[ -z "${FAKE_HESTIA:-}" ] || rm -rf "$FAKE_HESTIA"
}

#----------------------------------------------------------#
#                 mysql_grant_db_name                      #
#----------------------------------------------------------#

@test "MySQL grants: underscore is escaped" {
	run mysql_grant_db_name "mys_l"
	assert_success
	assert_output 'mys\_l'
}

@test "MySQL grants: every underscore is escaped" {
	run mysql_grant_db_name "johnv_y_shop"
	assert_output 'johnv\_y\_shop'
}

@test "MySQL grants: percent is escaped" {
	run mysql_grant_db_name "user_a%b"
	assert_output 'user\_a\%b'
}

@test "MySQL grants: backslash is escaped before wildcards" {
	run mysql_grant_db_name 'user_a\b'
	assert_output 'user\_a\\b'
}

@test "MySQL grants: name without wildcards is unchanged" {
	run mysql_grant_db_name "plaindb"
	assert_output "plaindb"
}

#----------------------------------------------------------#
#                 Revoke helpers                           #
#----------------------------------------------------------#

@test "MySQL grants: revoke removes escaped and legacy grants on both hosts" {
	mysql_revoke_db_grants "johnv_shop" "johnv_user"
	run cat "$QUERY_LOG"
	assert_line 'REVOKE ALL ON `johnv\_shop`.* FROM `johnv_user`@`%`'
	assert_line 'REVOKE ALL ON `johnv\_shop`.* FROM `johnv_user`@localhost'
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_user`@`%`'
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_user`@localhost'
}

@test "MySQL grants: legacy revoke only touches the unescaped grant" {
	mysql_revoke_legacy_db_grants "johnv_shop" "johnv_user"
	run cat "$QUERY_LOG"
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_user`@`%`'
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_user`@localhost'
	refute_output --partial 'johnv\_shop'
}

#----------------------------------------------------------#
#                 Database actions                         #
#----------------------------------------------------------#

@test "MySQL grants: unsuspend grants the escaped name and drops the legacy grant" {
	HOST=localhost database="johnv_shop" DBUSER="johnv_user"
	unsuspend_mysql_database
	run cat "$QUERY_LOG"
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_user`@`%`'
	assert_line 'GRANT ALL ON `johnv\_shop`.* TO `johnv_user`@`%`'
	assert_line 'GRANT ALL ON `johnv\_shop`.* TO `johnv_user`@localhost'
	refute_line --partial 'GRANT ALL ON `johnv_shop`'
}

@test "MySQL grants: suspend revokes both grant forms" {
	HOST=localhost database="johnv_shop" DBUSER="johnv_user"
	suspend_mysql_database
	run cat "$QUERY_LOG"
	assert_line 'REVOKE ALL ON `johnv\_shop`.* FROM `johnv_user`@`%`'
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_user`@`%`'
	refute_output --partial 'GRANT ALL'
}

@test "MySQL grants: temp user grant uses the escaped name" {
	host=localhost database="johnv_shop" dbuser="mysql_sso_johnv_shop_x"
	mysql_fork=mysql mysql_ver=8.0.36
	add_mysql_database_temp_user
	run cat "$QUERY_LOG"
	assert_output --partial 'GRANT ALL ON `johnv\_shop`.* TO `mysql_sso_johnv_shop_x`@`%`'
	refute_output --partial 'GRANT ALL ON `johnv_shop`'
}

@test "MySQL grants: temp user removal revokes both grant forms" {
	host=localhost database="johnv_shop" dbuser="mysql_sso_johnv_shop_x"
	delete_mysql_database_temp_user
	run cat "$QUERY_LOG"
	assert_line 'REVOKE ALL ON `johnv\_shop`.* FROM `mysql_sso_johnv_shop_x`@`%`'
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `mysql_sso_johnv_shop_x`@`%`'
}

#----------------------------------------------------------#
#                 Upgrade migration                        #
#----------------------------------------------------------#

@test "MySQL grants: migration replaces legacy grants and skips suspended databases" {
	# Minimal parser, enough for the fixed test lines below
	parse_object_kv_list() {
		local kv
		for kv in $1; do
			kv=${kv//\'/}
			printf -v "${kv%%=*}" '%s' "${kv#*=}"
		done
	}

	FAKE_HESTIA="$(mktemp -d)"
	HESTIA="$FAKE_HESTIA"
	mkdir -p "$HESTIA/data/users/johnv" "$HESTIA/data/users/other"
	cat > "$HESTIA/data/users/johnv/db.conf" << 'EOF'
DB='johnv_shop' DBUSER='johnv_shop' HOST='localhost' TYPE='mysql' SUSPENDED='no'
DB='johnv_old' DBUSER='johnv_old' HOST='localhost' TYPE='mysql' SUSPENDED='yes'
DB='johnv_pg' DBUSER='johnv_pg' HOST='localhost' TYPE='pgsql' SUSPENDED='no'
EOF
	printf "DB='other_db' DBUSER='other_u' HOST='db2' TYPE='mysql' SUSPENDED='no'\n" \
		> "$HESTIA/data/users/other/db.conf"

	mysql_migrate_legacy_db_grants

	run cat "$QUERY_LOG"
	# Active database: legacy grant revoked, exact grant added
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_shop`@`%`'
	assert_line 'GRANT ALL ON `johnv\_shop`.* TO `johnv_shop`@`%`'
	assert_line 'GRANT ALL ON `johnv\_shop`.* TO `johnv_shop`@localhost'
	# Suspended database: legacy grant revoked, nothing granted
	assert_line 'REVOKE ALL ON `johnv_old`.* FROM `johnv_old`@`%`'
	refute_line --partial 'GRANT ALL ON `johnv\_old`'
	# PostgreSQL databases are left alone
	refute_output --partial 'johnv_pg'
	# Remote hosts are handled too
	assert_line 'CONNECT db2'
	assert_line 'GRANT ALL ON `other\_db`.* TO `other_u`@`%`'
	# No wildcard grant is ever (re)created
	refute_output --regexp 'GRANT ALL ON `[^`\\]*_'
}

#----------------------------------------------------------#
#                 Source guard                             #
#----------------------------------------------------------#

@test "MySQL grants: no GRANT on an unescaped database name in the code" {
	# Every database-level GRANT must go through mysql_grant_db_name (or the
	# $db_grant variable holding its result). A new GRANT on a raw variable
	# such as `$database` would bring the wildcard issue back.
	run bash -c "grep -rnE 'GRANT [A-Z ,]+ ON \\\\\`\\\$' \
		'${REPO_ROOT}/func' '${REPO_ROOT}/bin' '${REPO_ROOT}/install/upgrade' \
		| grep -vE 'mysql_grant_db_name|\\\$db_grant'"
	assert_output ''
}
