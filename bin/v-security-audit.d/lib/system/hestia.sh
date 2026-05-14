#!/bin/bash

check_hestia_panel() {
	section "SYSTEM" "HestiaCP Panel Security"

	local hestia_conf="/usr/local/hestia/conf/hestia.conf"
	if [ ! -f "$hestia_conf" ]; then
		result_skip "S19" "HestiaCP not found"
		return
	fi

	local backend_port
	backend_port=$(grep "^BACKEND_PORT=" "$hestia_conf" 2> /dev/null | cut -d"'" -f2)
	if [ "$backend_port" = "8083" ] || [ -z "$backend_port" ]; then
		result_info "S19" "Panel is on default port 8083"
	else
		result_pass "S19" "Panel is on non-standard port ${backend_port}"
	fi

	local panel_cert="/usr/local/hestia/ssl/certificate.crt"
	if [ -f "$panel_cert" ]; then
		local expiry
		expiry=$(openssl x509 -enddate -noout -in "$panel_cert" 2> /dev/null | cut -d= -f2)
		if [ -n "$expiry" ]; then
			local exp_epoch
			exp_epoch=$(date -d "$expiry" +%s 2> /dev/null)
			local now_epoch
			now_epoch=$(date +%s)
			local days_left=$(((exp_epoch - now_epoch) / 86400))
			if [ $days_left -lt 0 ]; then
				result_fail "S20" "Panel SSL certificate is EXPIRED"
			elif [ $days_left -lt 14 ]; then
				result_warn "S21" "Panel SSL expires in ${days_left} days"
			else
				result_pass "S20" "Panel SSL certificate is valid (${days_left} days remaining)"
			fi
		else
			result_fail "S20" "Cannot parse panel SSL certificate"
		fi
	else
		result_fail "S20" "Panel SSL certificate not found"
	fi

	local admin_conf="/usr/local/hestia/data/users/admin/user.conf"
	if [ -f "$admin_conf" ]; then
		local twofa
		twofa=$(grep "^TWOFA=" "$admin_conf" 2> /dev/null | cut -d"'" -f2)
		if [ -n "$twofa" ] && [ "$twofa" != "" ]; then
			result_pass "S22" "Admin account has 2FA enabled"
		else
			result_critical "S22" "Admin account does NOT have 2FA enabled"
		fi

		local admin_domains=0
		if [ -f /usr/local/hestia/data/users/admin/web.conf ]; then
			admin_domains=$(grep -c "^DOMAIN=" /usr/local/hestia/data/users/admin/web.conf 2> /dev/null || echo 0)
		fi
		if [ "$admin_domains" -gt 0 ]; then
			result_warn "S23" "${admin_domains} website(s) hosted under admin user (use separate accounts)"
		else
			result_pass "S23" "No websites hosted under admin user"
		fi
	fi

	local hv
	hv=$(grep "^VERSION=" "$hestia_conf" 2> /dev/null | cut -d"'" -f2)
	if [ -n "$hv" ]; then
		result_info "S24" "HestiaCP version: ${hv}"
	fi

	local session_timeout
	session_timeout=$(grep "^SESSION_TIMEOUT=" "$hestia_conf" 2> /dev/null | cut -d"'" -f2)
	if [ -n "$session_timeout" ] && [ "$session_timeout" != "0" ]; then
		result_pass "S25" "Panel session timeout: ${session_timeout} minutes"
	else
		result_warn "S25" "Panel session timeout not configured"
	fi
}
