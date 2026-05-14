#!/bin/bash

check_cms_hardening() {
	local docroot="$1" user="$2" domain="$3" cms="$4"
	local project_root
	project_root=$(dirname "$docroot")

	case "$cms" in
		wordpress) check_wordpress "$docroot" "$user" "$domain" ;;
		laravel) check_laravel "$docroot" "$user" "$domain" "$project_root" ;;
		drupal) check_drupal "$docroot" "$user" "$domain" ;;
		joomla) check_joomla "$docroot" "$user" "$domain" ;;
		magento) check_magento "$docroot" "$user" "$domain" "$project_root" ;;
		prestashop) check_prestashop "$docroot" "$user" "$domain" ;;
		opencart) check_opencart "$docroot" "$user" "$domain" ;;
		moodle) check_moodle "$docroot" "$user" "$domain" ;;
		*) check_generic_php "$docroot" "$user" "$domain" ;;
	esac
}

check_opencart() {
	local docroot="$1" user="$2" domain="$3"

	local config_file="${docroot}/config.php"
	if [ -f "$config_file" ]; then
		local perms
		perms=$(stat -c '%a' "$config_file" 2> /dev/null)
		if is_more_permissive_than "$perms" "640"; then
			result_fail "B70" "OpenCart config.php is ${perms} (should be ≤640) [${domain}]"
		else
			result_pass "B70" "OpenCart config.php permissions OK (${perms}) [${domain}]"
		fi
	fi

	local admin_config="${docroot}/admin/config.php"
	if [ -f "$admin_config" ]; then
		local aperms
		aperms=$(stat -c '%a' "$admin_config" 2> /dev/null)
		if is_more_permissive_than "$aperms" "640"; then
			result_fail "B71" "OpenCart admin/config.php is ${aperms} (should be ≤640) [${domain}]"
		else
			result_pass "B71" "OpenCart admin/config.php permissions OK [${domain}]"
		fi
	fi

	if [ -d "${docroot}/system/storage/logs" ]; then
		local log_count
		log_count=$(find "${docroot}/system/storage/logs" -name "*.log" -size +0 2> /dev/null | wc -l)
		if [ "$log_count" -gt 0 ]; then
			result_warn "B72" "OpenCart has ${log_count} log file(s) in system/storage/logs [${domain}]"
		fi
	fi
}

check_moodle() {
	local docroot="$1" user="$2" domain="$3"

	local moodle_config="${docroot}/config.php"
	if [ -f "$moodle_config" ]; then
		local perms
		perms=$(stat -c '%a' "$moodle_config" 2> /dev/null)
		if is_more_permissive_than "$perms" "640"; then
			result_fail "B73" "Moodle config.php is ${perms} (should be ≤640) [${domain}]"
		else
			result_pass "B73" "Moodle config.php permissions OK (${perms}) [${domain}]"
		fi

		if grep -qi "\\$CFG->debug" "$moodle_config" 2> /dev/null; then
			local debug_val
			debug_val=$(grep -i "\\$CFG->debug" "$moodle_config" 2> /dev/null | head -1)
			if echo "$debug_val" | grep -qE "DEBUG_DEVELOPER|38911|E_ALL"; then
				result_warn "B74" "Moodle debug mode enabled in config.php [${domain}]"
			fi
		fi
	fi
}

check_generic_php() {
	local docroot="$1" user="$2" domain="$3"

	for admin_dir in "admin" "administrator" "backend" "panel" "cpanel" "dashboard" "manage"; do
		if [ -d "${docroot}/${admin_dir}" ]; then
			local login_page=""
			for f in "login.php" "index.php" "auth.php" "signin.php"; do
				if [ -f "${docroot}/${admin_dir}/${f}" ]; then
					login_page="${admin_dir}/${f}"
					break
				fi
			done
			if [ -n "$login_page" ]; then
				result_info "B75" "Admin panel detected at /${login_page} [${domain}]"
			fi
		fi
	done

	if [ -d "${docroot}/uploads" ]; then
		local php_in_uploads
		php_in_uploads=$(find "${docroot}/uploads" -name "*.php" -o -name "*.phtml" -o -name "*.phar" 2> /dev/null | wc -l)
		if [ "$php_in_uploads" -gt 0 ]; then
			result_fail "B76" "${php_in_uploads} PHP file(s) in uploads/ directory [${domain}]"
		fi
	fi

	for env_file in ".env" ".env.local" ".env.production"; do
		if [ -f "${docroot}/${env_file}" ]; then
			local eperms
			eperms=$(stat -c '%a' "${docroot}/${env_file}" 2> /dev/null)
			if is_more_permissive_than "$eperms" "640"; then
				result_fail "B77" "${env_file} is ${eperms} (should be ≤640) [${domain}]"
			fi
		fi
	done
}
