#!/bin/bash

check_file_exposure() {
	local docroot="$1" user="$2" domain="$3"
	local project_root
	project_root=$(dirname "$docroot")

	local env_files
	env_files=$(find "$docroot" -maxdepth 3 -name ".env" -o -name ".env.local" -o -name ".env.production" -o -name ".env.backup" 2> /dev/null)
	if [ -n "$env_files" ]; then
		result_critical "B01" ".env file(s) found inside public_html [${domain}]"
	else
		result_pass "B01" "No .env files in public_html [${domain}]"
	fi

	if [ -d "${docroot}/.git" ]; then
		result_critical "B02" ".git/ directory exposed in public_html [${domain}]"
	else
		result_pass "B02" "No .git/ directory in public_html [${domain}]"
	fi

	local sql_dumps
	sql_dumps=$(find "$docroot" -maxdepth 3 \( -name "*.sql" -o -name "*.sql.gz" -o -name "*.sql.bz2" -o -name "dump.sql" -o -name "database.sql" -o -name "backup.sql" \) 2> /dev/null | head -5)
	if [ -n "$sql_dumps" ]; then
		local count
		count=$(echo "$sql_dumps" | wc -l)
		result_critical "B03" "${count} SQL dump file(s) in public_html [${domain}]"
	else
		result_pass "B03" "No SQL dumps in public_html [${domain}]"
	fi

	local config_backups
	config_backups=$(find "$docroot" -maxdepth 2 \( -name "wp-config.php.bak" -o -name "wp-config.php.save" -o -name "wp-config.php.old" -o -name "wp-config.php~" -o -name "configuration.php.bak" -o -name "settings.php.bak" -o -name "config.php.bak" \) 2> /dev/null)
	if [ -n "$config_backups" ]; then
		result_fail "B04" "Config backup files found in public_html [${domain}]"
	else
		result_pass "B04" "No config backup files [${domain}]"
	fi

	local debug_files
	debug_files=$(find "$docroot" -maxdepth 2 \( -name "phpinfo.php" -o -name "info.php" -o -name "test.php" -o -name "debug.php" -o -name "adminer.php" -o -name "phpmyadmin.php" \) 2> /dev/null)
	if [ -n "$debug_files" ]; then
		local names
		names=$(echo "$debug_files" | xargs -I{} basename {} | sort -u | tr '\n' ', ' | sed 's/,$//')
		result_fail "B05" "Debug/info files found: ${names} [${domain}]"
	else
		result_pass "B05" "No debug/info files [${domain}]"
	fi

	if [ -f "${docroot}/composer.json" ] || [ -f "${docroot}/composer.lock" ]; then
		result_warn "B06" "composer.json/lock exposed in public_html [${domain}]"
	else
		result_pass "B06" "No composer files exposed [${domain}]"
	fi

	if [ -f "${docroot}/package.json" ] || [ -f "${docroot}/yarn.lock" ] || [ -f "${docroot}/package-lock.json" ]; then
		result_warn "B07" "package.json/lock exposed in public_html [${domain}]"
	else
		result_pass "B07" "No npm/yarn files exposed [${domain}]"
	fi

	if [ -f "${docroot}/Dockerfile" ] || [ -f "${docroot}/docker-compose.yml" ] || [ -f "${docroot}/docker-compose.yaml" ]; then
		result_warn "B08" "Docker files exposed in public_html [${domain}]"
	else
		result_pass "B08" "No Docker files exposed [${domain}]"
	fi

	local htaccess_creds
	htaccess_creds=$(grep -rl "AuthUserFile" "$docroot" --include=".htaccess" 2> /dev/null | head -1)
	if [ -n "$htaccess_creds" ]; then
		result_fail "B09" ".htaccess with AuthUserFile found [${domain}]"
	else
		result_pass "B09" "No .htaccess credential leaks [${domain}]"
	fi

	local private_keys
	private_keys=$(find "$docroot" -maxdepth 3 \( -name "id_rsa" -o -name "id_ed25519" -o -name "*.pem" -o -name "*.key" \) ! -path "*/ssl/*" 2> /dev/null | head -3)
	if [ -n "$private_keys" ]; then
		result_critical "B10" "Private key files found in public_html [${domain}]"
	else
		result_pass "B10" "No private keys in public_html [${domain}]"
	fi

	local install_dirs=""
	for d in install setup installer _installer Installation; do
		if [ -d "${docroot}/${d}" ]; then
			install_dirs="${install_dirs} ${d}"
		fi
	done
	if [ -n "$install_dirs" ]; then
		result_warn "B11" "Leftover installer dirs:${install_dirs} [${domain}]"
	else
		result_pass "B11" "No leftover installer directories [${domain}]"
	fi

	local adminer_found=false
	if [ -f "${docroot}/adminer.php" ]; then adminer_found=true; fi
	local phpmyadmin_found=false
	if [ -d "${docroot}/phpmyadmin" ] || [ -d "${docroot}/phpMyAdmin" ] || [ -d "${docroot}/pma" ]; then phpmyadmin_found=true; fi
	if $adminer_found || $phpmyadmin_found; then
		result_critical "B12" "Standalone DB manager found in public_html [${domain}]"
	else
		result_pass "B12" "No standalone DB managers exposed [${domain}]"
	fi

	# Automated Symlink Bypass Testing
	local symlink_test="${docroot}/.symlink_test_audit"
	CLEANUP_FILES+=("$symlink_test")
	if ln -s /etc/passwd "$symlink_test" 2> /dev/null; then
		local symlink_resp
		symlink_resp=$(curl -s -m 5 "http://${domain}/.symlink_test_audit" 2> /dev/null | head -50)
		if echo "$symlink_resp" | grep -q "root:x:0:0"; then
			result_critical "B24" "Symlink bypass allowed! Web server follows symlinks to /etc/passwd [${domain}]"
		else
			result_pass "B24" "Symlink resolution outside docroot is blocked [${domain}]"
		fi
		rm -f "$symlink_test"
	else
		result_info "B24" "Could not create symlink to test FollowSymLinks enforcement [${domain}]"
	fi
}
