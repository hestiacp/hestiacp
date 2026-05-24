#!/bin/bash

check_firewall() {
	section "SYSTEM" "Firewall & Fail2Ban"

	if is_service_active fail2ban; then
		result_pass "S13" "Fail2Ban is running"
	else
		result_fail "S13" "Fail2Ban is NOT running"
	fi

	local ssh_jail
	ssh_jail=$(fail2ban-client status sshd 2> /dev/null || fail2ban-client status ssh-iptables 2> /dev/null)
	if [ $? -eq 0 ]; then
		local banned
		banned=$(echo "$ssh_jail" | grep "Currently banned" | awk '{print $NF}')
		result_pass "S14" "SSH jail is active (${banned} currently banned)"
	else
		result_fail "S14" "SSH Fail2Ban jail is NOT active"
	fi

	local recidive
	recidive=$(fail2ban-client status recidive 2> /dev/null)
	if [ $? -eq 0 ]; then
		result_pass "S15" "Recidive jail is active (repeat offender protection)"
	else
		result_warn "S15" "Recidive jail is not active (recommend enabling)"
	fi

	# S15b: WordPress Fail2Ban jail — must cover both xmlrpc.php AND wp-login.php
	local wp_jail
	wp_jail=$(fail2ban-client status wordpress-xmlrpc 2> /dev/null)
	if [ $? -eq 0 ]; then
		local wp_banned
		wp_banned=$(echo "$wp_jail" | grep "Currently banned" | awk '{print $NF}')
		result_pass "S15b" "WordPress jail is active (${wp_banned} currently banned)"

		# Check that the filter covers wp-login.php, not just xmlrpc.php
		local wp_filter="/etc/fail2ban/filter.d/wordpress-xmlrpc.conf"
		if [ -f "$wp_filter" ]; then
			if grep -q 'wp-login' "$wp_filter" 2> /dev/null; then
				result_pass "S15c" "WordPress filter covers wp-login.php brute-force"
			else
				result_fail "S15c" "WordPress filter does NOT cover wp-login.php (only xmlrpc.php) — brute-force attacks on wp-login will go unblocked"
			fi
		else
			result_warn "S15c" "WordPress filter file not found at ${wp_filter}"
		fi
	else
		# Check if any WordPress sites exist (look for wp-login in Apache logs)
		if find /var/log/apache2/domains/ -name '*.log' -exec grep -ql 'wp-login' {} + 2> /dev/null; then
			result_fail "S15b" "WordPress jail is NOT active but WordPress sites are being attacked"
		else
			result_pass "S15b" "WordPress jail is not active (no WordPress sites detected)"
		fi
	fi

	if [ -f /usr/local/hestia/conf/hestia.conf ]; then
		local fw
		fw=$(grep "^FIREWALL=" /usr/local/hestia/conf/hestia.conf 2> /dev/null | cut -d"'" -f2)
		if [ "$fw" = "yes" ]; then
			result_pass "S16" "HestiaCP firewall is enabled"
		else
			result_fail "S16" "HestiaCP firewall is DISABLED"
		fi
	fi

	local listen_ports
	listen_ports=$(ss -tlnp 2> /dev/null | grep LISTEN | awk '{print $4}' | sed 's/.*://' | sort -un)
	if [ -z "$listen_ports" ]; then
		listen_ports=$(netstat -tlnp 2> /dev/null | grep LISTEN | awk '{print $4}' | sed 's/.*://' | sort -un)
	fi
	local known_ports="22 25 53 80 110 143 443 465 587 993 995 3306 8083"
	local unknown_ports=""
	for p in $listen_ports; do
		local is_known=false
		for kp in $known_ports; do
			if [ "$p" = "$kp" ]; then
				is_known=true
				break
			fi
		done
		local ssh_port_val
		ssh_port_val=$(parse_sshd_config "Port")
		if [ "$p" = "$ssh_port_val" ]; then is_known=true; fi
		if ! $is_known && [ "$p" -gt 1024 ] 2> /dev/null; then
			unknown_ports="${unknown_ports} ${p}"
		fi
	done
	if [ -n "$unknown_ports" ]; then
		result_warn "S17" "Unexpected ports listening:${unknown_ports}"
	else
		result_pass "S17" "Only expected ports are listening"
	fi
}
