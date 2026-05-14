#!/bin/bash

check_ssh_hardening() {
	section "SYSTEM" "SSH Hardening"

	local root_login
	root_login=$(parse_sshd_config "PermitRootLogin")
	case "$root_login" in
		no | forced-commands-only)
			result_pass "S06" "Root SSH login is disabled (${root_login})"
			;;
		prohibit-password | without-password)
			result_warn "S06" "Root SSH login allows key-based only (${root_login})"
			;;
		__unset__ | yes)
			result_fail "S06" "Root SSH login is permitted"
			;;
		*)
			result_warn "S06" "Root SSH login setting: ${root_login}"
			;;
	esac

	local pass_auth
	pass_auth=$(parse_sshd_config "PasswordAuthentication")
	if [ "$pass_auth" = "no" ]; then
		result_pass "S07" "Password authentication is disabled (key-only)"
	else
		result_warn "S07" "Password authentication is enabled (prefer key-only)"
	fi

	local ssh_port
	ssh_port=$(parse_sshd_config "Port")
	if [ "$ssh_port" = "__unset__" ] || [ "$ssh_port" = "22" ]; then
		result_info "S08" "SSH is running on default port 22"
	else
		result_pass "S08" "SSH is on non-standard port ${ssh_port}"
	fi

	local max_tries
	max_tries=$(parse_sshd_config "MaxAuthTries")
	if [ "$max_tries" = "__unset__" ]; then
		result_warn "S10" "MaxAuthTries not set (default: 6)"
	elif [ "$max_tries" -le 3 ] 2> /dev/null; then
		result_pass "S10" "MaxAuthTries is ${max_tries}"
	else
		result_warn "S10" "MaxAuthTries is ${max_tries} (recommend ≤ 3)"
	fi

	local alive_interval
	alive_interval=$(parse_sshd_config "ClientAliveInterval")
	if [ "$alive_interval" = "__unset__" ] || [ "$alive_interval" = "0" ]; then
		result_warn "S11" "SSH idle timeout not configured"
	else
		result_pass "S11" "SSH idle timeout configured (${alive_interval}s)"
	fi

	local key_count=0
	if [ -f /root/.ssh/authorized_keys ]; then
		key_count=$(grep -cvE '^\s*$|^\s*#' /root/.ssh/authorized_keys 2> /dev/null || echo 0)
	fi
	result_info "S12" "${key_count} authorized SSH key(s) for root"
}
