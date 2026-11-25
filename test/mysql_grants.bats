#!/usr/bin/env bats
#
# Unit tests for MySQL database grant escaping (GHSA-g95g-h6p5-m6c6).
#
# Hestia database names contain underscores. MySQL with partial_revokes
# enabled matches these names literally. Other servers require escaping.
#
# These tests need no MySQL server: mysql_query is replaced by a stub that
# records each query, so they also run outside the CI container.

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'

setup() {
	REPO_ROOT="$(cd "${BATS_TEST_DIRNAME}/.." && pwd)"
	# shellcheck source=func/db.sh
	source "${REPO_ROOT}/func/db.sh"
	# shellcheck source=func/rebuild.sh
	source "${REPO_ROOT}/func/rebuild.sh"

	QUERY_LOG="$(mktemp)"
	export QUERY_LOG
	mysql_fork=mariadb
	mysql_ver=10.11.0
	mysql_query() { printf '%s\n' "$1" >> "$QUERY_LOG"; }
	mysql_connect() { printf 'CONNECT %s\n' "$1" >> "$QUERY_LOG"; }
	mysql_query_raw() { return 1; }
	check_result() { if [ "$1" -ne 0 ]; then exit "$1"; fi; }
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

@test "MySQL grants: MySQL escapes database names when partial revokes are off or unavailable" {
	mysql_fork=mysql mysql_ver=8.0.36
	mysql_query_raw() { printf '%s\n' "$partial_revokes_result"; }
	for partial_revokes_result in $'partial_revokes\tOFF' ''; do
		run mysql_grant_db_name 'johnv_shop'
		assert_success
		assert_output 'johnv\_shop'
	done
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

@test "MySQL grants: with wildcard matching only the unescaped grant is obsolete" {
	mysql_revoke_obsolete_db_grants 'johnv_shop' 'johnv_user' 'johnv\_shop'
	run cat "$QUERY_LOG"
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_user`@`%`'
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `johnv_user`@localhost'
	refute_output --partial 'johnv\_shop'
}

#----------------------------------------------------------#
#                 Database actions                         #
#----------------------------------------------------------#

@test "MySQL grants: regular database creation grants the literal name with partial revokes" {
	host=db2 database=johnv_shop dbuser=johnv_user charset=UTF8MB4
	mysql_fork=mysql mysql_ver=8.0.36
	mysql_query_raw() { printf 'partial_revokes\tON\n'; }
	add_mysql_database
	run cat "$QUERY_LOG"
	assert_line 'CONNECT db2'
	assert_line 'CREATE DATABASE `johnv_shop` CHARACTER SET UTF8MB4'
	assert_line 'GRANT ALL ON `johnv_shop`.* TO `johnv_user`@`%`'
	assert_line 'GRANT ALL ON `johnv_shop`.* TO `johnv_user`@localhost'
	refute_output --partial 'GRANT ALL ON `johnv\_shop`'
}

@test "MySQL grants: regular database creation escapes the name without partial revokes" {
	host=db2 database=johnv_shop dbuser=johnv_user charset=UTF8MB4
	mysql_fork=mysql mysql_ver=8.0.36
	mysql_query_raw() { printf 'partial_revokes\tOFF\n'; }
	add_mysql_database
	run cat "$QUERY_LOG"
	assert_line 'GRANT ALL ON `johnv\_shop`.* TO `johnv_user`@`%`'
	assert_line 'GRANT ALL ON `johnv\_shop`.* TO `johnv_user`@localhost'
	refute_output --partial 'GRANT ALL ON `johnv_shop`'
}

@test "MySQL grants: settings lookup failure stops database and temp user creation" {
	host=db2 database=johnv_shop dbuser=johnv_user
	mysql_fork=mysql mysql_ver=8.0.36
	for action in add_mysql_database add_mysql_database_temp_user; do
		: > "$QUERY_LOG"
		run "$action"
		assert_failure
		run cat "$QUERY_LOG"
		assert_output 'CONNECT db2'
	done
}

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
	add_mysql_database_temp_user
	run cat "$QUERY_LOG"
	assert_output --partial 'GRANT ALL ON `johnv\_shop`.* TO `mysql_sso_johnv_shop_x`@`%`'
	refute_output --partial 'GRANT ALL ON `johnv_shop`'
}

@test "MySQL grants: temp user grant uses the literal name with partial revokes" {
	host=db2 database=johnv_shop dbuser=mysql_sso_johnv_shop_x
	mysql_fork=mysql mysql_ver=8.0.36
	mysql_query_raw() { printf 'partial_revokes\tON\n'; }
	add_mysql_database_temp_user
	run cat "$QUERY_LOG"
	assert_line 'GRANT ALL ON `johnv_shop`.* TO `mysql_sso_johnv_shop_x`@`%`'
	refute_output --partial 'GRANT ALL ON `johnv\_shop`'
}

@test "MySQL grants: a failed grant removes the newly created temp user" {
	host=db2 database=johnv_shop dbuser=mysql_sso_johnv_shop_x
	mysql_fork=mysql mysql_ver=8.0.36
	mysql_query_raw() { printf 'partial_revokes\tON\n'; }
	mysql_query() {
		printf '%s\n' "$1" >> "$QUERY_LOG"
		[[ "$1" != 'GRANT ALL '* ]]
	}
	run add_mysql_database_temp_user
	assert_failure
	run cat "$QUERY_LOG"
	assert_line 'GRANT ALL ON `johnv_shop`.* TO `mysql_sso_johnv_shop_x`@`%`'
	assert_line "DROP USER 'mysql_sso_johnv_shop_x'@'%'"
}

@test "MySQL grants: temp user removal revokes both grant forms" {
	host=localhost database="johnv_shop" dbuser="mysql_sso_johnv_shop_x"
	delete_mysql_database_temp_user
	run cat "$QUERY_LOG"
	assert_line 'REVOKE ALL ON `johnv\_shop`.* FROM `mysql_sso_johnv_shop_x`@`%`'
	assert_line 'REVOKE ALL ON `johnv_shop`.* FROM `mysql_sso_johnv_shop_x`@`%`'
}

@test "MySQL grants: password change preserves the literal grant with partial revokes" {
	HOST=db2 database=johnv_shop DBUSER=johnv_user
	mysql_fork=mysql mysql_ver=8.0.36
	mysql_query_raw() { printf 'partial_revokes\tON\n'; }
	change_mysql_password
	run cat "$QUERY_LOG"
	assert_line 'REVOKE ALL ON `johnv\_shop`.* FROM `johnv_user`@`%`'
	refute_output --partial 'REVOKE ALL ON `johnv_shop`'
	assert_output --partial 'SET PASSWORD FOR `johnv_user`@`%`'
}

@test "MySQL grants: rebuild and unsuspend replace the escaped grant with partial revokes" {
	HOST=db2 DB=johnv_shop database=johnv_shop DBUSER=johnv_user CHARSET=UTF8MB4
	mysql_fork=mysql mysql_ver=8.0.36
	mysql_query_raw() { printf 'partial_revokes\tON\n'; }
	for action in rebuild_mysql_database unsuspend_mysql_database; do
		: > "$QUERY_LOG"
		"$action"
		run cat "$QUERY_LOG"
		assert_line 'REVOKE ALL ON `johnv\_shop`.* FROM `johnv_user`@`%`'
		refute_output --partial 'REVOKE ALL ON `johnv_shop`'
		assert_line 'GRANT ALL ON `johnv_shop`.* TO `johnv_user`@`%`'
		assert_line 'GRANT ALL ON `johnv_shop`.* TO `johnv_user`@localhost'
	done
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
	mysql_query_raw() { printf 'partial_revokes\tON\n'; }
	mysql_connect() {
		printf 'CONNECT %s\n' "$1" >> "$QUERY_LOG"
		if [ "$1" = db2 ]; then
			mysql_fork=mysql mysql_ver=8.0.36
		fi
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
	# Suspended database: both forms revoked, nothing granted
	assert_line 'REVOKE ALL ON `johnv_old`.* FROM `johnv_old`@`%`'
	assert_line 'REVOKE ALL ON `johnv\_old`.* FROM `johnv_old`@`%`'
	refute_output --regexp 'GRANT ALL ON `johnv\\?_old`'
	# PostgreSQL databases are left alone
	refute_output --partial 'johnv_pg'
	# The remote host uses literal grants
	assert_line 'CONNECT db2'
	assert_line 'REVOKE ALL ON `other\_db`.* FROM `other_u`@`%`'
	assert_line 'GRANT ALL ON `other_db`.* TO `other_u`@`%`'
	refute_output --partial 'REVOKE ALL ON `other_db`'
	refute_output --partial 'GRANT ALL ON `johnv_shop`'
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
