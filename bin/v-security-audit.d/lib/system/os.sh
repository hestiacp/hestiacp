#!/bin/bash

check_os() {
	section "SYSTEM" "Kernel & OS"

	if [ -f /etc/os-release ]; then
		source /etc/os-release
		case "$ID-$VERSION_ID" in
			debian-11* | debian-12*)
				result_pass "S01" "Supported OS: ${PRETTY_NAME}"
				;;
			ubuntu-20* | ubuntu-22* | ubuntu-24*)
				result_pass "S01" "Supported OS: ${PRETTY_NAME}"
				;;
			*)
				result_warn "S01" "Untested OS: ${PRETTY_NAME} — results may be inaccurate"
				;;
		esac
	else
		result_warn "S01" "Cannot determine OS version (/etc/os-release missing)"
	fi
}

check_kernel() {
	local running
	running=$(uname -r)
	local upgradable
	upgradable=$(apt list --upgradable 2> /dev/null | grep -c "linux-image" || true)
	if [ "$upgradable" -gt 0 ]; then
		result_warn "S02" "Kernel update available (running: ${running})"
	else
		result_pass "S02" "Kernel is current (${running})"
	fi
}

check_reboot() {
	if [ -f /var/run/reboot-required ]; then
		result_warn "S03" "System reboot is pending"
	else
		result_pass "S03" "No pending reboot"
	fi
}

check_auto_updates() {
	if ! command_exists dpkg; then
		result_skip "S04" "dpkg not available, skipping auto-update check"
		return
	fi
	if dpkg -l unattended-upgrades 2> /dev/null | grep -q "^ii"; then
		if [ -f /etc/apt/apt.conf.d/20auto-upgrades ]; then
			local enabled
			enabled=$(grep -c 'APT::Periodic::Unattended-Upgrade "1"' /etc/apt/apt.conf.d/20auto-upgrades 2> /dev/null || true)
			if [ "$enabled" -gt 0 ]; then
				result_pass "S04" "Automatic security updates are enabled"
			else
				result_warn "S04" "unattended-upgrades installed but not enabled"
			fi
		else
			result_warn "S04" "unattended-upgrades installed but 20auto-upgrades config missing"
		fi
	else
		result_warn "S04" "Automatic security updates not configured (unattended-upgrades not installed)"
	fi
}

check_critical_updates() {
	if ! command_exists apt; then
		result_skip "S05" "apt not available, skipping critical update check"
		return
	fi
	local critical_pkgs="openssl|nginx|apache2|php|exim4|mariadb|dovecot|fail2ban"
	local pending
	pending=$(apt list --upgradable 2> /dev/null | grep -iE "$critical_pkgs" || true)
	if [ -n "$pending" ]; then
		local count
		count=$(echo "$pending" | wc -l)
		result_fail "S05" "${count} critical package(s) have updates pending"
	else
		result_pass "S05" "No critical package updates pending"
	fi
}

check_suid_capabilities() {
	section "SYSTEM" "Binary Capabilities & SUID Audit"

	local dangerous_suid="find|nmap|tar|awk|sed|bash|sh|php|python|perl|ruby|cp|mv|vim|nano"

	local suid_files
	suid_files=$(find /usr/bin /usr/sbin /bin /sbin /usr/local/bin /usr/local/sbin -type f -perm -4000 2> /dev/null | grep -iE "($dangerous_suid)$")

	if [ -n "$suid_files" ]; then
		local count
		count=$(echo "$suid_files" | wc -l)
		result_critical "S62" "Found ${count} highly dangerous SUID binaries (LPE risk)"
	else
		result_pass "S62" "No exceptionally dangerous SUID binaries found in standard paths"
	fi

	if command_exists getcap; then
		if getcap /usr/bin/* /usr/sbin/* /bin/* /sbin/* 2> /dev/null | grep -qiE "cap_setuid|cap_setgid|cap_dac_override"; then
			result_fail "S63" "Found binaries with powerful capabilities (cap_setuid/dac_override)"
		else
			result_pass "S63" "No excessively powerful capabilities assigned to binaries"
		fi
	else
		result_skip "S63" "getcap not installed, skipping capabilities check"
	fi
}
