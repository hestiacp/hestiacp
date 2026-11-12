#!/usr/bin/env bats
#
# Tests for the 1.10.7 migration from distribution-packaged Roundcube to the
# Hestia-managed upstream installation.
#
# The script is exercised against fixture directories and mocked package/MySQL
# commands. This covers detection, config/data preservation, package removal
# and the rollback path without touching the host installation.

load '../test_helper/bats-support/load'
load '../test_helper/bats-assert/load'

setup() {
	REPO_ROOT="$(cd "$BATS_TEST_DIRNAME/../.." && pwd)"
	WORK="$BATS_TEST_TMPDIR/migration"
	HESTIA="$WORK/hestia"
	SHARE_DIR="$WORK/usr/share/roundcube"
	CONFIG_DIR="$WORK/etc/roundcube"
	INSTALL_DIR="$WORK/var/lib/roundcube"
	BACKUP_ROOT="$WORK/root"
	MOCK_BIN="$WORK/bin"

	mkdir -p "$WORK" "$BACKUP_ROOT" "$MOCK_BIN"
	mkdir -p "$HESTIA/func" "$HESTIA/bin" "$HESTIA/conf" "$HESTIA/install/upgrade"
	mkdir -p "$SHARE_DIR/program/include" "$SHARE_DIR/plugins/managesieve"
	mkdir -p "$CONFIG_DIR/plugins/managesieve"
	mkdir -p "$INSTALL_DIR"
	ln -s "$SHARE_DIR/program" "$INSTALL_DIR/program"
	ln -s "$CONFIG_DIR" "$INSTALL_DIR/config"

	cp "$REPO_ROOT/install/upgrade/roundcube-config-tool.php" "$HESTIA/install/upgrade/"
	printf "rc_v='1.7.6'\n" > "$HESTIA/install/upgrade/upgrade.conf"
	printf "WEBMAIL_SYSTEM='roundcube'\nDB_SYSTEM='mysql'\n" > "$HESTIA/conf/hestia.conf"

	cat > "$HESTIA/func/main.sh" <<'EOF'
source_conf() { source "$1"; }
generate_password() { echo testpass; }
log_event() { :; }
EOF

	cat > "$HESTIA/func/db.sh" <<'EOF'
mysql_connect() { mkdir -p "$HESTIA/conf"; : > "$HESTIA/conf/.mysql.localhost"; mycnf="$HESTIA/conf/.mysql.localhost"; return 0; }
mysql_sql_escape() { printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e "s/'/''/g"; }
mysql_query() { printf '%s\n' "$1" >> "$RC_TEST_SQL_LOG"; return 0; }
mysql_query_raw() {
	if [[ "$1" == SHOW\ GRANTS* ]]; then
		return 0
	fi
	if [[ "$1" == *information_schema.schemata* || "$1" == *mysql.user* ]]; then
		return 0
	fi
	if [[ "$1" == *information_schema.tables* ]]; then
		printf 'users\nidentities\n'
		return 0
	fi
	if [[ "$1" == *'.`users`' ]]; then
		echo 2
		return 0
	fi
	if [[ "$1" == *'.`identities`' ]]; then
		echo 3
		return 0
	fi
	echo 0
}
EOF

	cat > "$SHARE_DIR/program/include/iniset.php" <<'EOF'
<?php
define('RCMAIL_VERSION', '1.6.11');
EOF
	printf 'old plugin\n' > "$SHARE_DIR/plugins/managesieve/plugin.php"
	printf 'old var marker\n' > "$INSTALL_DIR/marker"

	cat > "$CONFIG_DIR/debian-db.php" <<'EOF'
<?php
$dbtype = 'mysql';
$dbuser = 'roundcube';
$dbpass = 'oldpass';
$dbname = 'roundcube';
$dbserver = 'localhost';
$dbport = '';
EOF

	cat > "$CONFIG_DIR/config.inc.php" <<'EOF'
<?php
$config['db_dsnw'] = 'mysql://roundcube:oldpass@localhost/roundcube';
$config['imap_host'] = 'mail.example.com:993';
$config['smtp_host'] = 'mail.example.com:587';
$config['des_key'] = 'abcdefghijklmnopqrstuvwx';
$config['plugins'] = [
    'managesieve',
    'password',
    // 'commentedplugin',
    'missingplugin',
];
$config['product_name'] = 'Example Mail';
if (getenv('RC_TEST_CONDITIONAL')) $config['conditional_one'] = 'should-not-copy';
EOF

	printf '<?php $config["foo"] = "bar";\n' > "$CONFIG_DIR/plugins/managesieve/config.inc.php"

	cat > "$MOCK_BIN/mysqldump" <<'EOF'
#!/bin/bash
cat <<'SQL'
-- fixture dump
CREATE TABLE `users` (`id` int);
CREATE TABLE `identities` (`id` int);
INSERT INTO `users` VALUES (1), (2);
INSERT INTO `identities` VALUES (1), (2), (3);
SQL
EOF

	cat > "$MOCK_BIN/mysql" <<'EOF'
#!/bin/bash
for argument in "$@"; do
	if [[ "$argument" == "-e" ]]; then
		exit 0
	fi
	last="$argument"
done
if [[ "${last:-}" == "roundcube" ]]; then
	cat > "$RC_TEST_IMPORT_LOG"
fi
exit 0
EOF

	cat > "$MOCK_BIN/dpkg-query" <<'EOF'
#!/bin/bash
if [[ -f "$RC_TEST_PURGED" ]]; then
	exit 1
else
	printf 'ii \troundcube-core\t1.6.11\n'
	printf 'ii \troundcube-mysql\t1.6.11\n'
	printf 'ii \troundcube-plugins\t1.6.11\n'
	printf 'ii \tphp-roundcube-common\t1\n'
fi
EOF

	cat > "$MOCK_BIN/apt-get" <<'EOF'
#!/bin/bash
printf '%s\n' "$*" >> "$RC_TEST_APT_LOG"
touch "$RC_TEST_PURGED"
exit 0
EOF

	ln -s "$MOCK_BIN/mysqldump" "$MOCK_BIN/mariadb-dump"
	ln -s "$MOCK_BIN/mysql" "$MOCK_BIN/mariadb"

	cat > "$MOCK_BIN/systemd-run" <<'EOF'
#!/bin/bash
printf '%s\n' "$*" >> "$RC_TEST_SYSTEMD_LOG"
exit 0
EOF
	chmod +x "$MOCK_BIN/systemd-run"

	cat > "$HESTIA/bin/v-add-sys-roundcube" <<'EOF'
#!/bin/bash
mkdir -p "$RC_INSTALL_DIR/program/include" "$RC_INSTALL_DIR/bin" "$RC_INSTALL_DIR/plugins/managesieve"
mkdir -p "$RC_CONFIG_DIR/plugins/password" "$RC_CONFIG_DIR/plugins/newmail_notifier" "$RC_CONFIG_DIR/plugins/zipdownload"
cat > "$RC_INSTALL_DIR/program/include/iniset.php" <<PHP
<?php
define('RCMAIL_VERSION', '$RC_TEST_VERSION');
PHP
cat > "$RC_CONFIG_DIR/config.inc.php" <<'PHP'
<?php
$config["db_dsnw"] = "mysql://roundcube:newpass@localhost/roundcube";
$config["des_key"] = "newkeynewkeynewkeynewke";
$config["plugins"] = ["password", "newmail_notifier", "zipdownload", "archive"];
PHP
printf '#!/usr/bin/env php\n<?php file_put_contents(getenv("RC_TEST_UPDATE_LOG"), "update\\n", FILE_APPEND);\n' > "$RC_INSTALL_DIR/bin/update.sh"
chmod +x "$RC_INSTALL_DIR/bin/update.sh"
EOF

	chmod +x "$MOCK_BIN"/* "$HESTIA/bin/v-add-sys-roundcube"
}

teardown() {
	rm -rf "$WORK"
}

run_script() {
	run env -i \
		PATH="$MOCK_BIN:/usr/bin:/bin" \
		HOME=/root \
		HESTIA="$HESTIA" \
		RC_HESTIA_CONF=/dev/null \
		RC_SHARE_DIR="$SHARE_DIR" \
		RC_CONFIG_DIR="$CONFIG_DIR" \
		RC_INSTALL_DIR="$INSTALL_DIR" \
		RC_BACKUP_ROOT="$BACKUP_ROOT" \
		RC_TEST_VERSION=1.7.6 \
		RC_TEST_SQL_LOG="$WORK/sql.log" \
		RC_TEST_IMPORT_LOG="$WORK/import.sql" \
		RC_TEST_APT_LOG="$WORK/apt.log" \
		RC_TEST_PURGED="$WORK/purged" \
		RC_TEST_UPDATE_LOG="$WORK/update.log" \
		RC_TEST_SYSTEMD_LOG="$WORK/systemd.log" \
		HESTIA_BACKUP="$WORK/upgrade-backup" \
		bash "$REPO_ROOT/install/upgrade/migrate_roundcube.sh" "$@"
}

run_migration() {
	run_script --yes
}

@test "Roundcube config helper preserves custom values, des_key and plugins" {
	mkdir -p "$WORK/new-config" "$WORK/new-install/plugins/managesieve"
	cat > "$WORK/new-config/config.inc.php" <<'EOF'
<?php
$config["db_dsnw"] = "mysql://roundcube:newpass@localhost/roundcube";
$config["des_key"] = "newkeynewkeynewkeynewke";
$config["plugins"] = ["password", "newmail_notifier", "zipdownload", "archive"];
EOF

	run php "$HESTIA/install/upgrade/roundcube-config-tool.php" db-config "$CONFIG_DIR/config.inc.php"
	assert_success
	assert_line "RC_DB_NAME='roundcube'"
	assert_line "RC_DB_USER='roundcube'"
	assert_line "RC_DB_PASS='oldpass'"
	assert_line "RC_DB_HOST='localhost'"

	run php "$HESTIA/install/upgrade/roundcube-config-tool.php" merge-config \
		--old "$CONFIG_DIR/config.inc.php" \
		--new "$WORK/new-config/config.inc.php" \
		--install-dir "$WORK/new-install" \
		--config-dir "$WORK/new-config"
	assert_success
	assert_output --partial "Plugin 'missingplugin' is not available"
	assert_output --partial "des_key: old key preserved"

	run grep -q "mail.example.com:993" "$WORK/new-config/config.inc.php"
	assert_success
	run grep -q "Example Mail" "$WORK/new-config/config.inc.php"
	assert_success
	run grep -q "abcdefghijklmnopqrstuvwx" "$WORK/new-config/config.inc.php"
	assert_success
	run grep -q "managesieve" "$WORK/new-config/config.inc.php"
	assert_success
	run grep -q "commentedplugin" "$WORK/new-config/config.inc.php"
	assert_failure
	run grep -q "should-not-copy" "$WORK/new-config/config.inc.php"
	assert_failure
	run grep -q "newpass@localhost/roundcube" "$WORK/new-config/config.inc.php"
	assert_success
	run grep -q "oldpass@localhost" "$WORK/new-config/config.inc.php"
	assert_failure
}

@test "Roundcube db helper reads Debian dbconfig-common settings" {
	local config="$WORK/debian-only-config"
	mkdir -p "$config"
	cat > "$config/config.inc.php" <<'EOF'
<?php
include("/etc/roundcube/debian-db-roundcube.php");
$config['des_key'] = 'abcdefghijklmnopqrstuvwx';
EOF
	cp "$CONFIG_DIR/debian-db.php" "$config/debian-db.php"

	run php "$HESTIA/install/upgrade/roundcube-config-tool.php" db-config "$config/config.inc.php"
	assert_success
	assert_line "RC_DB_NAME='roundcube'"
	assert_line "RC_DB_USER='roundcube'"
	assert_line "RC_DB_PASS='oldpass'"
}

@test "Roundcube migration preserves data/config and purges only Roundcube packages" {
	run_migration
	assert_success
	assert_output --partial "Roundcube migrated from the distribution package"
	assert_output --partial "Distribution Roundcube packages removed"

	# New Hestia install and merged configuration
	[ -f "$INSTALL_DIR/program/include/iniset.php" ]
	[ -f "$CONFIG_DIR/config.inc.php" ]
	run grep -q "abcdefghijklmnopqrstuvwx" "$CONFIG_DIR/config.inc.php"
	assert_success
	run grep -q "mail.example.com:993" "$CONFIG_DIR/config.inc.php"
	assert_success
	run grep -q "managesieve" "$CONFIG_DIR/config.inc.php"
	assert_success
	run grep -q "newpass@localhost/roundcube" "$CONFIG_DIR/config.inc.php"
	assert_success
	[ -f "$CONFIG_DIR/plugins/managesieve/config.inc.php" ]

	# Database dump and import, with no package autoremove
	run grep -q "INSERT INTO \`users\`" "$WORK/import.sql"
	assert_success
	run grep -q "INSERT INTO \`identities\`" "$WORK/import.sql"
	assert_success
	run grep -q "INSERT INTO" "$BACKUP_ROOT"/roundcube-apt-migration-*/db/roundcube.sql
	assert_success
	run grep -q "autoremove" "$WORK/apt.log"
	assert_failure
	run grep -q "purge .*roundcube-core.*roundcube-mysql.*roundcube-plugins" "$WORK/apt.log"
	assert_success
	run grep -q "php-roundcube-common" "$WORK/apt.log"
	assert_failure

	# Backup retained, with old files and package list
	local backup
	backup="$(find "$BACKUP_ROOT" -maxdepth 1 -type d -name 'roundcube-apt-migration-*' | head -n 1)"
	[ -n "$backup" ]
	[ -f "$backup/db/roundcube.sql" ]
	[ -f "$backup/files/etc-roundcube/config.inc.php" ]
	[ -f "$backup/files/usr-share-roundcube/program/include/iniset.php" ]
	[ -f "$backup/packages/installed.tsv" ]
	run grep -q "roundcube-core" "$backup/packages/installed.tsv"
	assert_success

	# Old distribution tree is gone; new install is not staged away.
	[ ! -d "$SHARE_DIR" ]
	[ -d "$INSTALL_DIR" ]
	[ -f "$WORK/update.log" ]
}

@test "Roundcube migration skips when a Hestia-managed install already exists" {
	rm -rf "$INSTALL_DIR"
	mkdir -p "$INSTALL_DIR/program/include"
	printf "<?php define('RCMAIL_VERSION', '1.7.6');\n" > "$INSTALL_DIR/program/include/iniset.php"

	run_migration
	assert_success
	assert_output --partial "both $SHARE_DIR and a real Hestia Roundcube install"
	[ -f "$CONFIG_DIR/config.inc.php" ]
	[ -f "$SHARE_DIR/program/include/iniset.php" ]
	run test -f "$WORK/apt.log"
	assert_failure
}

@test "Roundcube deferred purge-only mode removes packages and keeps the new install" {
	rm -rf "$INSTALL_DIR" "$SHARE_DIR"
	mkdir -p "$INSTALL_DIR/program/include" "$INSTALL_DIR/bin" "$CONFIG_DIR"
	cat > "$INSTALL_DIR/program/include/iniset.php" <<'EOF'
<?php
define('RCMAIL_VERSION', '1.7.6');
EOF
	cat > "$INSTALL_DIR/bin/update.sh" <<'EOF'
#!/usr/bin/env php
<?php file_put_contents(getenv("RC_TEST_UPDATE_LOG"), "update\n", FILE_APPEND);
EOF
	cat > "$CONFIG_DIR/config.inc.php" <<'EOF'
<?php
$config['db_dsnw'] = 'mysql://roundcube:newpass@localhost/roundcube';
$config['des_key'] = 'abcdefghijklmnopqrstuvwx';
EOF

	local backup="$WORK/deferred-backup"
	mkdir -p "$backup/db" "$backup/packages"
	printf 'users\t2\nidentities\t3\n' > "$backup/db/rowcounts-before.tsv"
	printf 'CREATE TABLE `users` (`id` int);\nCREATE TABLE `identities` (`id` int);\n' > "$backup/db/roundcube.sql"
	printf 'roundcube-core\t1.6.11\nroundcube-mysql\t1.6.11\n' > "$backup/packages/installed.tsv"
	cat > "$backup/metadata" <<'EOF'
source_version=1.6.11
target_version=1.7.6
db_name=roundcube
EOF
	touch "$backup/migration-complete"

	run_script --purge-only --backup-dir "$backup"
	assert_success
	assert_output --partial "Distribution Roundcube packages removed"
	run grep -q "purge .*roundcube-core" "$WORK/apt.log"
	assert_success
	run grep -q "autoremove" "$WORK/apt.log"
	assert_failure
	[ -f "$CONFIG_DIR/config.inc.php" ]
	[ -f "$INSTALL_DIR/program/include/iniset.php" ]
}

@test "Roundcube upgrade mode defers package removal while apt holds the lock" {
	run_script --yes --defer-package-removal
	assert_success
	assert_output --partial "Distribution package removal scheduled"
	run test -f "$WORK/apt.log"
	assert_failure
	run grep -q "cleanup-packages.sh" "$WORK/systemd.log"
	assert_success
	run grep -q "purge-only --backup-dir" "$BACKUP_ROOT"/roundcube-apt-migration-*/cleanup-packages.sh
	assert_success
}

@test "Roundcube migration restores files and database when installation fails" {
	cat > "$HESTIA/bin/v-add-sys-roundcube" <<'EOF'
#!/bin/bash
mkdir -p "$RC_CONFIG_DIR"
printf 'partial new install\n' > "$RC_CONFIG_DIR/config.inc.php"
mkdir -p "$RC_INSTALL_DIR/program"
printf 'partial new install\n' > "$RC_INSTALL_DIR/program/partial"
exit 1
EOF
	chmod +x "$HESTIA/bin/v-add-sys-roundcube"

	run_migration
	assert_failure
	assert_output --partial "Rolling back Roundcube migration"
	assert_output --partial "Original Roundcube installation restored"

	# Apt files and the old database user/database are back.
	run grep -q "RCMAIL_VERSION', '1.6.11'" "$SHARE_DIR/program/include/iniset.php"
	assert_success
	run grep -q "oldpass@localhost/roundcube" "$CONFIG_DIR/config.inc.php"
	assert_success
	[ "$(cat "$INSTALL_DIR/marker")" = "old var marker" ]
	[ ! -e "$INSTALL_DIR/program/partial" ]

	run grep -q "CREATE DATABASE IF NOT EXISTS \`roundcube\`" "$WORK/sql.log"
	assert_success
	run grep -q "CREATE USER 'roundcube'@'localhost' IDENTIFIED BY 'oldpass'" "$WORK/sql.log"
	assert_success
	run grep -q "INSERT INTO \`users\`" "$WORK/import.sql"
	assert_success

	# Backup is retained after rollback.
	local backup
	backup="$(find "$BACKUP_ROOT" -maxdepth 1 -type d -name 'roundcube-apt-migration-*' | head -n 1)"
	[ -n "$backup" ]
	[ -f "$backup/db/roundcube.sql" ]
	[ -f "$backup/files/etc-roundcube/config.inc.php" ]
}
