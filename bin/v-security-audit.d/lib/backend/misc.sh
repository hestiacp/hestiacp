#!/bin/bash

check_wordpress() {
	local docroot="$1" user="$2" domain="$3"

	local wpconfig="${docroot}/wp-config.php"
	if [ -f "$wpconfig" ]; then
		local perms
		perms=$(stat -c '%a' "$wpconfig" 2> /dev/null)
		if is_more_permissive_than "$perms" "640"; then
			result_fail "B60" "wp-config.php is ${perms} (should be ≤640) [${domain}]"
		else
			result_pass "B60" "wp-config.php permissions OK (${perms}) [${domain}]"
		fi
	fi

	local debuglog="${docroot}/wp-content/debug.log"
	if [ -f "$debuglog" ]; then
		local dperms
		dperms=$(stat -c '%a' "$debuglog" 2> /dev/null)
		if is_more_permissive_than "$dperms" "640"; then
			result_fail "B61" "debug.log exists and is ${dperms} (should be ≤640 or removed) [${domain}]"
		else
			result_warn "B61" "debug.log exists (consider removing in production) [${domain}]"
		fi
	else
		result_pass "B61" "No debug.log file [${domain}]"
	fi

	if [ -f "$wpconfig" ]; then
		local file_edit
		file_edit=$(grep -c "DISALLOW_FILE_EDIT" "$wpconfig" 2> /dev/null || true)
		if [ "${file_edit:-0}" -gt 0 ] 2> /dev/null; then
			result_pass "B62" "File editing is disabled (DISALLOW_FILE_EDIT) [${domain}]"
		else
			result_warn "B62" "File editing not disabled in wp-config.php [${domain}]"
		fi

		local table_prefix
		table_prefix=$(grep "table_prefix" "$wpconfig" 2> /dev/null | head -1 | sed "s/.*'\([^']*\)'.*/\1/")
		if [ "$table_prefix" = "wp_" ]; then
			result_warn "B63" "Default table prefix 'wp_' (recommend changing) [${domain}]"
		else
			result_pass "B63" "Non-default table prefix (${table_prefix}) [${domain}]"
		fi
	fi

	if [ -d "${docroot}/wp-content/uploads" ]; then
		local uploads_htaccess="${docroot}/wp-content/uploads/.htaccess"
		local nginx_deny=false
		local user_nginx_conf="/home/${user}/conf/web/${domain}"
		if [ -d "$user_nginx_conf" ]; then
			if grep -rq "location.*uploads.*\.php" "$user_nginx_conf" 2> /dev/null; then
				nginx_deny=true
			fi
		fi
		if [ -f "$uploads_htaccess" ] && grep -qi "php" "$uploads_htaccess" 2> /dev/null; then
			result_pass "B64" "PHP execution blocked in uploads via .htaccess [${domain}]"
		elif $nginx_deny; then
			result_pass "B64" "PHP execution blocked in uploads via Nginx [${domain}]"
		else
			result_fail "B64" "PHP execution NOT blocked in wp-content/uploads [${domain}]"
		fi
	fi
}

check_laravel() {
	local docroot="$1" user="$2" domain="$3" project_root="$4"

	if [ -f "${docroot}/.env" ]; then
		result_critical "B29" ".env file inside public/ directory [${domain}]"
	else
		result_pass "B29" ".env is outside public directory [${domain}]"
	fi

	local env_file="${project_root}/.env"
	if [ -f "$env_file" ]; then
		local app_debug
		app_debug=$(grep "^APP_DEBUG=" "$env_file" 2> /dev/null | cut -d= -f2 | tr -d ' "' | tr '[:upper:]' '[:lower:]')
		if [ "$app_debug" = "true" ]; then
			result_critical "B30" "APP_DEBUG=true in production [${domain}]"
		else
			result_pass "B30" "APP_DEBUG is false [${domain}]"
		fi

		local app_key
		app_key=$(grep "^APP_KEY=" "$env_file" 2> /dev/null | cut -d= -f2)
		if [ -z "$app_key" ] || [ "$app_key" = "" ]; then
			result_critical "B31" "APP_KEY is not set [${domain}]"
		else
			result_pass "B31" "APP_KEY is configured [${domain}]"
		fi
	fi
}

check_drupal() {
	local docroot="$1" user="$2" domain="$3"

	local settings="${docroot}/sites/default/settings.php"
	if [ -f "$settings" ]; then
		local perms
		perms=$(stat -c '%a' "$settings" 2> /dev/null)
		if is_more_permissive_than "$perms" "444"; then
			result_fail "B32" "Drupal settings.php is ${perms} (should be ≤444) [${domain}]"
		else
			result_pass "B32" "Drupal settings.php permissions OK (${perms}) [${domain}]"
		fi
	fi
}

check_joomla() {
	local docroot="$1" user="$2" domain="$3"

	local config="${docroot}/configuration.php"
	if [ -f "$config" ]; then
		local perms
		perms=$(stat -c '%a' "$config" 2> /dev/null)
		if is_more_permissive_than "$perms" "640"; then
			result_fail "B33" "Joomla configuration.php is ${perms} (should be ≤640) [${domain}]"
		else
			result_pass "B33" "Joomla configuration.php permissions OK (${perms}) [${domain}]"
		fi
	fi
}

check_magento() {
	local docroot="$1" user="$2" domain="$3" project_root="$4"

	local env_php=""
	if [ -f "${docroot}/app/etc/env.php" ]; then
		env_php="${docroot}/app/etc/env.php"
	elif [ -f "${project_root}/app/etc/env.php" ]; then
		env_php="${project_root}/app/etc/env.php"
	fi

	if [ -n "$env_php" ]; then
		local perms
		perms=$(stat -c '%a' "$env_php" 2> /dev/null)
		if is_more_permissive_than "$perms" "640"; then
			result_fail "B34" "Magento env.php is ${perms} (should be ≤640) [${domain}]"
		else
			result_pass "B34" "Magento env.php permissions OK (${perms}) [${domain}]"
		fi
	fi
}

check_prestashop() {
	local docroot="$1" user="$2" domain="$3"

	local params=""
	if [ -f "${docroot}/app/config/parameters.php" ]; then
		params="${docroot}/app/config/parameters.php"
	elif [ -f "${docroot}/config/settings.inc.php" ]; then
		params="${docroot}/config/settings.inc.php"
	fi

	if [ -n "$params" ]; then
		local perms
		perms=$(stat -c '%a' "$params" 2> /dev/null)
		if is_more_permissive_than "$perms" "640"; then
			result_fail "B35" "PrestaShop config is ${perms} (should be ≤640) [${domain}]"
		else
			result_pass "B35" "PrestaShop config permissions OK (${perms}) [${domain}]"
		fi
	fi
}
