#!/usr/bin/env bats

if [ "${PATH#*/usr/local/hestia/bin*}" = "$PATH" ]; then
	. /etc/profile.d/hestia.sh
fi

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
load 'test_helper/bats-file/load'

function setup() {
	source /etc/hestiacp/hestia.conf || true
	source $HESTIA/conf/hestia.conf
	source $HESTIA/func/main.sh
	source $HESTIA/func/db.sh

	tmp_mysql_conf="$(mktemp)"
	cp "$HESTIA/conf/mysql.conf" "$tmp_mysql_conf"
	tmp_user_data="$(mktemp -d)"
	tmp_pma_conf="$(mktemp -d)"
}

function teardown() {
	cp "$tmp_mysql_conf" "$HESTIA/conf/mysql.conf"
	rm -f "$tmp_mysql_conf"
	rm -rf "$tmp_user_data"
	rm -rf "$tmp_pma_conf"
}

function write_duplicate_mysql_hosts() {
	cat > "$HESTIA/conf/mysql.conf" <<-EOF
	HOST='127.0.0.1' USER='root' PASSWORD='testpass' CHARSETS='UTF8,UTF8MB4' MAX_DB='500' U_SYS_USERS='' U_DB_BASES='0' SUSPENDED='no' TIME='00:00:00' DATE='2026-05-30' PORT='3306'
	HOST='127.0.0.1' USER='root' PASSWORD='testpass' CHARSETS='UTF8,UTF8MB4' MAX_DB='500' U_SYS_USERS='' U_DB_BASES='0' SUSPENDED='no' TIME='00:00:00' DATE='2026-05-30' PORT='3307'
	EOF
}

function write_pma_mysql_hosts() {
	cat > "$HESTIA/conf/mysql.conf" <<-EOF
	HOST='localhost' USER='root' PASSWORD='testpass' CHARSETS='UTF8,UTF8MB4' MAX_DB='500' U_SYS_USERS='' U_DB_BASES='0' SUSPENDED='no' TIME='00:00:00' DATE='2026-05-30' PORT='3306'
	HOST='127.0.0.1' USER='root' PASSWORD='testpass' CHARSETS='UTF8,UTF8MB4' MAX_DB='500' U_SYS_USERS='' U_DB_BASES='0' SUSPENDED='no' TIME='00:00:00' DATE='2026-05-30' PORT='3307'
	EOF
}

@test "database host lookup requires port when host has multiple endpoints" {
	write_duplicate_mysql_hosts

	run v-list-database-host mysql 127.0.0.1 json

	assert_failure
	assert_output --partial "multiple database hosts match 127.0.0.1"
}

@test "database host lookup can select same host by port" {
	write_duplicate_mysql_hosts

	run v-list-database-host mysql 127.0.0.1 json 3307

	assert_success
	assert_output --partial '"HOST": "127.0.0.1"'
	assert_output --partial '"PORT": "3307"'
	assert_output --partial '"ENDPOINT": "127.0.0.1:3307"'
	assert_output --partial '"USER": "root"'
}

@test "database host delete removes only the matching endpoint" {
	write_duplicate_mysql_hosts

	run v-delete-database-host mysql 127.0.0.1 3307

	assert_success
	refute_output
	run grep -c "HOST='127.0.0.1'" "$HESTIA/conf/mysql.conf"
	assert_success
	assert_output "1"
	run grep "PORT='3306'" "$HESTIA/conf/mysql.conf"
	assert_success
}

@test "legacy database rows default mysql port when PORT is missing" {
	USER_DATA="$tmp_user_data"
	database="legacy_db"
	cat > "$USER_DATA/db.conf" <<-EOF
	DB='legacy_db' DBUSER='legacy_user' MD5='hash' HOST='127.0.0.1' TYPE='mysql' CHARSET='UTF8MB4' U_DISK='0' SUSPENDED='no' TIME='00:00:00' DATE='2026-05-30'
	EOF

	get_database_values

	[ "$HOST" = "127.0.0.1" ]
	[ "$PORT" = "3306" ]
}

@test "default database host selection does not reuse previous endpoint port for legacy rows" {
	cat > "$HESTIA/conf/mysql.conf" <<-EOF
	HOST='127.0.0.1' USER='root' PASSWORD='testpass' CHARSETS='UTF8,UTF8MB4' MAX_DB='1' U_SYS_USERS='admin' U_DB_BASES='1' SUSPENDED='no' TIME='00:00:00' DATE='2026-05-30' PORT='3307'
	HOST='localhost' USER='root' PASSWORD='testpass' CHARSETS='UTF8,UTF8MB4' MAX_DB='500' U_SYS_USERS='' U_DB_BASES='0' SUSPENDED='no' TIME='00:00:00' DATE='2026-05-30'
	EOF
	type="mysql"
	host=""
	port=""

	get_next_dbhost

	[ "$host" = "localhost" ]
	[ "$port" = "3306" ]
}

@test "phpMyAdmin host refresh creates generated config only for non-default endpoints" {
	write_pma_mysql_hosts
	echo "<?php // default localhost config" > "$tmp_pma_conf/01-localhost.php"

	run env PMA_CONF_DIR="$tmp_pma_conf" v-update-sys-pma-hosts

	assert_success
	assert_file_not_exist "$tmp_pma_conf/20-hestia-dbhost-localhost_3306.php"
	assert_file_exist "$tmp_pma_conf/20-hestia-dbhost-127.0.0.1_3307.php"
	run grep -F "\$cfg['Servers'][\$i]['host'] = '127.0.0.1';" "$tmp_pma_conf/20-hestia-dbhost-127.0.0.1_3307.php"
	assert_success
	run grep -F "\$cfg['Servers'][\$i]['port'] = '3307';" "$tmp_pma_conf/20-hestia-dbhost-127.0.0.1_3307.php"
	assert_success
}

@test "phpMyAdmin host refresh is idempotent and removes stale generated configs" {
	write_pma_mysql_hosts
	touch "$tmp_pma_conf/20-hestia-dbhost-stale_3308.php"

	run env PMA_CONF_DIR="$tmp_pma_conf" v-update-sys-pma-hosts
	assert_success
	run env PMA_CONF_DIR="$tmp_pma_conf" v-update-sys-pma-hosts
	assert_success

	assert_file_not_exist "$tmp_pma_conf/20-hestia-dbhost-stale_3308.php"
	run find "$tmp_pma_conf" -name '20-hestia-dbhost-*.php' -type f
	assert_success
	assert_line --index 0 "$tmp_pma_conf/20-hestia-dbhost-127.0.0.1_3307.php"
	[ "${#lines[@]}" -eq 1 ]
}

@test "phpMyAdmin host refresh removes generated config after endpoint deletion" {
	write_pma_mysql_hosts
	run env PMA_CONF_DIR="$tmp_pma_conf" v-update-sys-pma-hosts
	assert_success

	sed -i "/PORT='3307'/d" "$HESTIA/conf/mysql.conf"
	run env PMA_CONF_DIR="$tmp_pma_conf" v-update-sys-pma-hosts

	assert_success
	assert_file_not_exist "$tmp_pma_conf/20-hestia-dbhost-127.0.0.1_3307.php"
}

@test "phpMyAdmin SSO template passes endpoint host and port" {
	run grep -F '"arg6" => $port' install/deb/phpmyadmin/hestia-sso.php
	assert_success
	run grep -F 'PMA_single_signon_port' install/deb/phpmyadmin/hestia-sso.php
	assert_success
	run grep -F 'PMA_single_signon_cfgupdate' install/deb/phpmyadmin/hestia-sso.php
	assert_success
	run grep -F 'verify_token($database, $user, $ip, $time, $token, $host, $port' install/deb/phpmyadmin/hestia-sso.php
	assert_success
}

@test "database list phpMyAdmin SSO URL signs and includes endpoint host and port" {
	run grep -F '$data[$key]["HOST"] . $data[$key]["PORT"]' web/templates/pages/list_db.php
	assert_success
	run grep -F '"host" => $data[$key]["HOST"]' web/templates/pages/list_db.php
	assert_success
	run grep -F '"port" => $data[$key]["PORT"]' web/templates/pages/list_db.php
	assert_success
}

@test "phpMyAdmin temp users are granted for TCP and local connections" {
	run grep -F 'TO \`$dbuser\`@\`%\`' func/db.sh
	assert_success
	run grep -F "DROP USER '\$dbuser'@'%'" func/db.sh
	assert_success
}

@test "add database UI marks endpoints by database type and filters host choices" {
	run grep -F 'data-type=' web/templates/pages/add_db.php
	assert_success
	run grep -F 'x-model="selectedType"' web/templates/pages/add_db.php
	assert_success
	run grep -F 'syncHost()' web/templates/pages/add_db.php
	assert_success
}

@test "add database controller rejects endpoint type mismatches before command execution" {
	run grep -F 'database_endpoint_exists' web/add/db/index.php
	assert_success
	run grep -F 'Selected database host does not support the selected database type.' web/add/db/index.php
	assert_success
}

@test "server configure posts mysql password changes per endpoint" {
	run grep -F 'name="v_mysql_password[' web/templates/pages/edit_server.php
	assert_success
	run grep -F 'name="v_mysql_host[' web/templates/pages/edit_server.php
	assert_success
	run grep -F 'name="v_mysql_port[' web/templates/pages/edit_server.php
	assert_success
	run grep -F 'foreach ($_POST["v_mysql_password"]' web/edit/server/index.php
	assert_success
	run grep -F 'v-change-database-host-password mysql ' web/edit/server/index.php
	assert_success
}

@test "database host value updates target exact endpoint" {
	write_duplicate_mysql_hosts

	database_update_host_value mysql 127.0.0.1 3307 '$PASSWORD' "changedpass"

	run grep "PORT='3307'" "$HESTIA/conf/mysql.conf"
	assert_success
	assert_output --partial "PASSWORD='changedpass'"
	run grep "PORT='3306'" "$HESTIA/conf/mysql.conf"
	assert_success
	refute_output --partial "PASSWORD='changedpass'"
}

@test "database host management UI routes are present and admin-only" {
	run test -f web/list/db-host/index.php
	assert_success
	run test -f web/add/db-host/index.php
	assert_success
	run test -f web/edit/db-host/index.php
	assert_success
	run test -f web/delete/db-host/index.php
	assert_success
	run test -f web/suspend/db-host/index.php
	assert_success
	run test -f web/unsuspend/db-host/index.php
	assert_success
	run grep -F '$_SESSION["userContext"] !== "admin"' web/list/db-host/index.php
	assert_success
	run grep -F '$_SESSION["userContext"] !== "admin"' web/add/db-host/index.php
	assert_success
	run grep -F 'verify_csrf($_POST)' web/add/db-host/index.php
	assert_success
	run grep -F 'verify_csrf($_POST)' web/edit/db-host/index.php
	assert_success
	run grep -F 'verify_csrf($_GET)' web/delete/db-host/index.php
	assert_success
	run grep -F 'verify_csrf($_GET)' web/suspend/db-host/index.php
	assert_success
	run grep -F 'verify_csrf($_GET)' web/unsuspend/db-host/index.php
	assert_success
}

@test "database host list UI exposes endpoint actions by type host and port" {
	run grep -F 'v-list-database-hosts json' web/list/db-host/index.php
	assert_success
	run grep -F '"USER": "' bin/v-list-database-hosts
	assert_success
	run grep -F '/add/db-host/' web/templates/pages/list_db_host.php
	assert_success
	run grep -F '/edit/db-host/?' web/templates/pages/list_db_host.php
	assert_success
	run grep -F '/delete/db-host/?' web/templates/pages/list_db_host.php
	assert_success
	run grep -F '/suspend/db-host/?' web/templates/pages/list_db_host.php
	assert_success
	run grep -F '/unsuspend/db-host/?' web/templates/pages/list_db_host.php
	assert_success
	run grep -F '"port" => $value["PORT"]' web/templates/pages/list_db_host.php
	assert_success
}

@test "database host user field is added only to json output formats" {
	run grep -F 'USER   MAX_DB' bin/v-list-database-hosts
	assert_failure
	run grep -F '$type\t$USER\t$CHARSETS' bin/v-list-database-hosts
	assert_failure
	run grep -F 'HOST,PORT,TYPE,USER' bin/v-list-database-hosts
	assert_failure
	run grep -F 'echo "USER:' bin/v-list-database-host
	assert_failure
	run grep -F '$type\t$USER\t$CHARSETS' bin/v-list-database-host
	assert_failure
	run grep -F 'HOST,PORT,TYPE,USER' bin/v-list-database-host
	assert_failure
}

@test "database host add UI maps submitted fields to endpoint-aware CLI" {
	run grep -F 'v-add-database-host ' web/add/db-host/index.php
	assert_success
	run grep -F 'quoteshellarg($v_port)' web/add/db-host/index.php
	assert_success
	run grep -F 'name="v_type"' web/templates/pages/add_db_host.php
	assert_success
	run grep -F 'name="v_host"' web/templates/pages/add_db_host.php
	assert_success
	run grep -F 'name="v_port"' web/templates/pages/add_db_host.php
	assert_success
	run grep -F 'name="v_password"' web/templates/pages/add_db_host.php
	assert_success
}

@test "database host edit UI changes password for exact endpoint" {
	run grep -F 'v-list-database-host ' web/edit/db-host/index.php
	assert_success
	run grep -F 'v-change-database-host-password ' web/edit/db-host/index.php
	assert_success
	run grep -F 'quoteshellarg($v_port)' web/edit/db-host/index.php
	assert_success
	run grep -F 'name="v_password"' web/templates/pages/edit_db_host.php
	assert_success
}

@test "database host action routes pass type host and port to CLI" {
	run grep -F 'v-delete-database-host ' web/delete/db-host/index.php
	assert_success
	run grep -F 'v-suspend-database-host ' web/suspend/db-host/index.php
	assert_success
	run grep -F 'v-unsuspend-database-host ' web/unsuspend/db-host/index.php
	assert_success
	run grep -F 'quoteshellarg($_GET["port"])' web/delete/db-host/index.php
	assert_success
	run grep -F 'quoteshellarg($_GET["port"])' web/suspend/db-host/index.php
	assert_success
	run grep -F 'quoteshellarg($_GET["port"])' web/unsuspend/db-host/index.php
	assert_success
}

@test "database host delete route refuses endpoints with databases before deletion" {
	run grep -F 'v-list-database-host ' web/delete/db-host/index.php
	assert_success
	run grep -F 'U_DB_BASES' web/delete/db-host/index.php
	assert_success
	run grep -F 'Database server can not be deleted while databases exist on it.' web/delete/db-host/index.php
	assert_success
	run grep -F '$delete_disabled ? "#"' web/templates/pages/list_db_host.php
	assert_success
}

@test "server pages link to database host manager without adding service lifecycle rows" {
	run grep -F '/list/db-host/' web/templates/pages/list_services.php
	assert_success
	run grep -F '/list/db-host/' web/templates/pages/edit_server.php
	assert_success
	run grep -F 'v-list-sys-services json' web/list/server/index.php
	assert_success
	refute_output --partial 'v-list-database-hosts json'
}
