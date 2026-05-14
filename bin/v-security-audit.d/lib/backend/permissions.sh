#!/bin/bash

check_permissions() {
	local docroot="$1" user="$2" domain="$3"

	local world_writable
	world_writable=$(find "$docroot" -type f -perm -o+w 2> /dev/null | wc -l)
	local world_writable_dirs
	world_writable_dirs=$(find "$docroot" -type d -perm -o+w 2> /dev/null | wc -l)
	local total_ww=$((world_writable + world_writable_dirs))
	if [ "$total_ww" -gt 0 ]; then
		result_fail "B36" "${total_ww} world-writable items (${world_writable_dirs} dirs, ${world_writable} files) [${domain}]"
	else
		result_pass "B36" "No world-writable items [${domain}]"
	fi

	local pool_user="$user"
	local pool_conf
	pool_conf=$(find /etc/php/ -path "*/pool.d/${domain}.conf" 2> /dev/null | head -1)
	if [ -f "$pool_conf" ]; then
		local fpm_user
		fpm_user=$(grep "^user" "$pool_conf" 2> /dev/null | head -1 | awk '{print $3}')
		if [ -n "$fpm_user" ]; then pool_user="$fpm_user"; fi
	fi

	local wrong_owner
	wrong_owner=$(find "$docroot" -maxdepth 1 ! -user "$pool_user" 2> /dev/null | head -1)
	if [ -n "$wrong_owner" ]; then
		local actual_owner
		actual_owner=$(stat -c '%U:%G' "$docroot" 2> /dev/null)
		result_fail "B37" "Ownership mismatch: ${actual_owner} (expected ${pool_user}) [${domain}]"
	else
		result_pass "B37" "Ownership matches PHP-FPM pool user [${domain}]"
	fi

	local suid_files
	suid_files=$(find "$docroot" \( -perm -4000 -o -perm -2000 \) -type f 2> /dev/null | wc -l)
	if [ "$suid_files" -gt 0 ]; then
		result_critical "B38" "${suid_files} SUID/SGID binary(s) found in web root [${domain}]"
	else
		result_pass "B38" "No SUID/SGID binaries in web root [${domain}]"
	fi

	if [ -d "${docroot}/wp-content/uploads" ] || [ -d "${docroot}/uploads" ]; then
		local udir="${docroot}/wp-content/uploads"
		if [ ! -d "$udir" ]; then udir="${docroot}/uploads"; fi
		if [ -d "$udir" ]; then
			local exec_files
			exec_files=$(find "$udir" -type f -executable 2> /dev/null | wc -l)
			if [ "$exec_files" -gt 0 ]; then
				result_fail "B39" "${exec_files} executable file(s) in uploads [${domain}]"
			else
				result_pass "B39" "No executable files in uploads [${domain}]"
			fi
		fi
	fi

	local world_readable_logs
	world_readable_logs=$(find "$docroot" -name "*.log" -perm -o+r -type f 2> /dev/null | wc -l)
	if [ "$world_readable_logs" -gt 0 ]; then
		result_warn "B40" "${world_readable_logs} world-readable log file(s) [${domain}]"
	else
		result_pass "B40" "No world-readable log files [${domain}]"
	fi

	local user_ini
	user_ini=$(find "$docroot" -maxdepth 3 -name ".user.ini" 2> /dev/null)
	if [ -n "$user_ini" ]; then
		local malicious_ini
		malicious_ini=$(grep -lE 'auto_prepend_file|auto_append_file' $user_ini 2> /dev/null | wc -l)
		if [ "$malicious_ini" -gt 0 ]; then
			result_critical "B41" "${malicious_ini} .user.ini with auto_prepend/append (malware injection) [${domain}]"
		else
			result_info "B41" ".user.ini files found (review manually) [${domain}]"
		fi
	else
		result_pass "B41" "No .user.ini files [${domain}]"
	fi

	local recently_modified
	recently_modified=$(find "$docroot" -name "*.php" -mtime -1 -type f 2> /dev/null | wc -l)
	if [ "$recently_modified" -gt 50 ]; then
		result_warn "B42" "${recently_modified} PHP files modified in the last 24h (possible compromise) [${domain}]"
	elif [ "$recently_modified" -gt 0 ]; then
		result_info "B42" "${recently_modified} PHP file(s) modified in the last 24h [${domain}]"
	else
		result_pass "B42" "No PHP files modified in the last 24h [${domain}]"
	fi

	local hidden_files
	hidden_files=$(find "$docroot" -maxdepth 2 -name ".*" -not -name ".htaccess" -not -name ".htpasswd" -not -name ".user.ini" -not -name ".well-known" -type f 2> /dev/null | wc -l)
	if [ "$hidden_files" -gt 0 ]; then
		result_warn "B43" "${hidden_files} hidden file(s) in web root (may contain secrets) [${domain}]"
	else
		result_pass "B43" "No suspicious hidden files [${domain}]"
	fi

	if [ -f "${docroot}/timthumb.php" ] || find "$docroot" -maxdepth 3 -name "timthumb.php" 2> /dev/null | grep -q .; then
		result_critical "B44" "timthumb.php found (known RCE vulnerability) [${domain}]"
	else
		result_pass "B44" "No timthumb.php [${domain}]"
	fi

	local symlinks
	symlinks=$(find "$docroot" -type l 2> /dev/null)
	if [ -n "$symlinks" ]; then
		local bad_symlinks
		bad_symlinks=$(echo "$symlinks" | while read -r lnk; do
			local target
			target=$(readlink -f "$lnk" 2> /dev/null || true)
			if [ -n "$target" ] && [[ ! "$target" == /home/${user}/* ]]; then
				echo "$lnk"
			fi
		done | wc -l)
		if [ "$bad_symlinks" -gt 0 ]; then
			result_critical "B45" "${bad_symlinks} symlink(s) pointing outside user home [${domain}]"
		else
			result_pass "B45" "All symlinks point within user home [${domain}]"
		fi
	else
		result_pass "B45" "No symlinks in web root [${domain}]"
	fi

	local large_files
	large_files=$(find "$docroot" -type f -size +100M 2> /dev/null | wc -l)
	if [ "$large_files" -gt 0 ]; then
		result_warn "B46" "${large_files} file(s) larger than 100MB (potential data dump) [${domain}]"
	else
		result_pass "B46" "No unusually large files [${domain}]"
	fi

	local error_logs
	error_logs=$(find "$docroot" -maxdepth 3 \( -name "error_log" -o -name "error.log" -o -name "php_errors.log" \) -type f 2> /dev/null | wc -l)
	if [ "$error_logs" -gt 0 ]; then
		result_warn "B47" "${error_logs} PHP error log(s) in public_html (info disclosure) [${domain}]"
	else
		result_pass "B47" "No PHP error logs in public_html [${domain}]"
	fi

	local htpasswd_files
	htpasswd_files=$(find "$docroot" -maxdepth 3 -name ".htpasswd" -type f 2> /dev/null)
	if [ -n "$htpasswd_files" ]; then
		local htpasswd_perms_bad=0
		for hp in $htpasswd_files; do
			local hp_perms
			hp_perms=$(stat -c '%a' "$hp" 2> /dev/null)
			if is_more_permissive_than "$hp_perms" "640"; then
				htpasswd_perms_bad=$((htpasswd_perms_bad + 1))
			fi
		done
		if [ "$htpasswd_perms_bad" -gt 0 ]; then
			result_fail "B48" "${htpasswd_perms_bad} .htpasswd file(s) with excessive permissions [${domain}]"
		else
			result_pass "B48" ".htpasswd permissions OK [${domain}]"
		fi
	else
		result_pass "B48" "No .htpasswd files [${domain}]"
	fi

	local include_pattern
	include_pattern=$(grep -rlE '@include\s+["\x27]\\' "$docroot" --include="*.php" 2> /dev/null | wc -l)
	if [ "$include_pattern" -gt 0 ]; then
		result_critical "B49" "${include_pattern} file(s) with @include obfuscation (common CMS malware) [${domain}]"
	else
		result_pass "B49" "No @include obfuscation patterns [${domain}]"
	fi

	local hex_functions
	hex_functions=$(grep -rlE '(\\x[0-9a-fA-F]{2}){8,}' "$docroot" --include="*.php" 2> /dev/null | wc -l)
	if [ "$hex_functions" -gt 0 ]; then
		result_fail "B50" "${hex_functions} file(s) with heavy hex encoding (likely obfuscation) [${domain}]"
	else
		result_pass "B50" "No hex-encoded PHP obfuscation [${domain}]"
	fi
}
