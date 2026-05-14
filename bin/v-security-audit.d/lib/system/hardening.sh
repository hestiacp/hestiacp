#!/bin/bash

check_php_hardening() {
	section "SYSTEM" "PHP-FPM Security"

	local pool_dirs=$(find /etc/php/ -type d -name "pool.d" 2> /dev/null)
	if [ -z "$pool_dirs" ]; then
		result_skip "S70" "No PHP-FPM pool directories found"
		return
	fi

	local total_pools=0
	local openbasedir_missing=0
	local disable_functions_weak=0
	local dangerous_funcs="exec,passthru,shell_exec,system,proc_open,popen"

	for pool_dir in $pool_dirs; do
		for pool_file in "${pool_dir}"/*.conf; do
			[ -f "$pool_file" ] || continue
			total_pools=$((total_pools + 1))

			local pool_name
			pool_name=$(basename "$pool_file" .conf)

			if ! grep -qE "^[^;]*open_basedir" "$pool_file" 2> /dev/null; then
				openbasedir_missing=$((openbasedir_missing + 1))
			fi

			local df_line
			df_line=$(grep -E "^[^;]*disable_functions" "$pool_file" 2> /dev/null | head -1)
			if [ -z "$df_line" ]; then
				disable_functions_weak=$((disable_functions_weak + 1))
			else
				local has_all=true
				for func in exec passthru shell_exec system proc_open popen; do
					if ! echo "$df_line" | grep -q "$func"; then
						has_all=false
						break
					fi
				done
				if ! $has_all; then
					disable_functions_weak=$((disable_functions_weak + 1))
				fi
			fi
		done
	done

	if [ $total_pools -eq 0 ]; then
		result_skip "S70" "No PHP-FPM pool configs found"
		return
	fi

	if [ $openbasedir_missing -gt 0 ]; then
		result_fail "S70" "${openbasedir_missing}/${total_pools} PHP-FPM pool(s) missing open_basedir (cross-site read risk)"
	else
		result_pass "S70" "All ${total_pools} PHP-FPM pool(s) have open_basedir configured"
	fi

	if [ $disable_functions_weak -gt 0 ]; then
		result_warn "S71" "${disable_functions_weak}/${total_pools} PHP-FPM pool(s) have weak/missing disable_functions"
	else
		result_pass "S71" "All ${total_pools} PHP-FPM pool(s) have dangerous functions disabled"
	fi
}

check_nginx_hardening() {
	section "SYSTEM" "Nginx Security"

	local nginx_conf="/etc/nginx/nginx.conf"
	if [ ! -f "$nginx_conf" ]; then
		result_skip "S72" "Nginx config not found"
		return
	fi

	if grep -qE "^\s*server_tokens\s+off" "$nginx_conf" 2> /dev/null; then
		result_pass "S72" "Nginx server_tokens off (version hidden)"
	else
		result_warn "S72" "Nginx server_tokens not set to off (version exposed)"
	fi

	local max_body
	max_body=$(grep -E "^\s*client_max_body_size" "$nginx_conf" 2> /dev/null | head -1 | awk '{print $2}' | tr -d ';')
	if [ -n "$max_body" ]; then
		result_info "S73" "Nginx client_max_body_size: ${max_body}"
	else
		result_warn "S73" "Nginx client_max_body_size not set (default 1MB or unlimited)"
	fi

	local ssl_protocols
	ssl_protocols=$(grep -E "^\s*ssl_protocols" "$nginx_conf" 2> /dev/null | head -1)
	if [ -n "$ssl_protocols" ]; then
		if echo "$ssl_protocols" | grep -qE "TLSv1[^.]|TLSv1\.0"; then
			result_fail "S74" "Nginx allows TLS 1.0 in ssl_protocols"
		elif echo "$ssl_protocols" | grep -q "TLSv1.1"; then
			result_fail "S74" "Nginx allows TLS 1.1 in ssl_protocols"
		else
			result_pass "S74" "Nginx ssl_protocols properly configured"
		fi
	else
		result_info "S74" "Nginx ssl_protocols not set in main config (may use defaults)"
	fi

	local limit_req
	limit_req=$(grep -rE "limit_req_zone" /etc/nginx/ 2> /dev/null | head -1)
	if [ -n "$limit_req" ]; then
		result_pass "S75" "Nginx rate limiting configured (limit_req_zone)"
	else
		result_info "S75" "No Nginx rate limiting (limit_req_zone) configured"
	fi
}

check_hestia_update() {
	section "SYSTEM" "HestiaCP Update Status"

	local current_version
	current_version=$(grep "^VERSION=" /usr/local/hestia/conf/hestia.conf 2> /dev/null | cut -d"'" -f2)
	if [ -z "$current_version" ]; then
		result_skip "S76" "Cannot determine HestiaCP version"
		return
	fi

	local latest_version
	latest_version=$(curl -s -m 10 "https://api.github.com/repos/hestiacp/hestiacp/releases/latest" 2> /dev/null | sed -n 's/.*"tag_name":"\([^"]*\)".*/\1/p' | sed 's/^v//')

	if [ -z "$latest_version" ]; then
		result_info "S76" "HestiaCP ${current_version} installed (cannot check latest — no internet or rate limit)"
		return
	fi

	if [ "$current_version" = "$latest_version" ]; then
		result_pass "S76" "HestiaCP ${current_version} is the latest version"
	else
		result_warn "S76" "HestiaCP ${current_version} installed, latest is ${latest_version}"
	fi
}

check_hestia_firewall_restrictions() {
	section "SYSTEM" "HestiaCP Firewall IP Restrictions"

	local fw_dir="/usr/local/hestia/data/firewall"
	if [ ! -d "$fw_dir" ]; then
		result_info "S77" "HestiaCP firewall rules directory not found"
		return
	fi

	local rule_count
	rule_count=$(ls -1 "$fw_dir" 2> /dev/null | wc -l)
	if [ "$rule_count" -eq 0 ]; then
		result_warn "S77" "No firewall rules configured"
	else
		result_info "S77" "${rule_count} firewall rule(s) configured"
	fi

	local panel_port
	panel_port=$(grep "^BACKEND_PORT=" /usr/local/hestia/conf/hestia.conf 2> /dev/null | cut -d"'" -f2)
	panel_port="${panel_port:-8083}"

	local panel_restricted=false
	if grep -rlq "$panel_port" "$fw_dir" 2> /dev/null; then
		panel_restricted=true
	fi

	if $panel_restricted; then
		result_pass "S78" "Firewall rules reference panel port ${panel_port}"
	else
		result_info "S78" "No specific firewall rule for panel port ${panel_port} (consider IP restriction)"
	fi
}
