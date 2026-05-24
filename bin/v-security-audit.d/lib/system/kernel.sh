#!/bin/bash

check_kernel_hardening() {
	section "SYSTEM" "Kernel Hardening (sysctl)"

	local aslr
	aslr=$(sysctl -n kernel.randomize_va_space 2> /dev/null)
	if [ "$aslr" = "2" ]; then
		result_pass "S39" "ASLR is fully enabled (kernel.randomize_va_space=2)"
	elif [ "$aslr" = "1" ]; then
		result_warn "S39" "ASLR is partially enabled (recommend =2)"
	else
		result_fail "S39" "ASLR is DISABLED (kernel.randomize_va_space=${aslr})"
	fi

	local syn_cookies
	syn_cookies=$(sysctl -n net.ipv4.tcp_syncookies 2> /dev/null)
	if [ "$syn_cookies" = "1" ]; then
		result_pass "S40" "SYN flood protection enabled (tcp_syncookies=1)"
	else
		result_fail "S40" "SYN flood protection DISABLED"
	fi

	local ip_forward
	ip_forward=$(sysctl -n net.ipv4.ip_forward 2> /dev/null)
	if [ "$ip_forward" = "0" ]; then
		result_pass "S41" "IP forwarding is disabled"
	else
		result_warn "S41" "IP forwarding is enabled (net.ipv4.ip_forward=1)"
	fi

	local icmp_redirect
	icmp_redirect=$(sysctl -n net.ipv4.conf.all.accept_redirects 2> /dev/null)
	if [ "$icmp_redirect" = "0" ]; then
		result_pass "S42" "ICMP redirect acceptance disabled"
	else
		result_warn "S42" "ICMP redirects accepted (net.ipv4.conf.all.accept_redirects=1)"
	fi

	local source_route
	source_route=$(sysctl -n net.ipv4.conf.all.accept_source_route 2> /dev/null)
	if [ "$source_route" = "0" ]; then
		result_pass "S43" "Source routing disabled"
	else
		result_fail "S43" "Source routing ACCEPTED (potential MITM vector)"
	fi

	local log_martians
	log_martians=$(sysctl -n net.ipv4.conf.all.log_martians 2> /dev/null)
	if [ "$log_martians" = "1" ]; then
		result_pass "S44" "Martian packet logging enabled"
	else
		result_info "S44" "Martian packet logging disabled"
	fi

	local core_dump
	core_dump=$(sysctl -n fs.suid_dumpable 2> /dev/null)
	if [ "$core_dump" = "0" ]; then
		result_pass "S45" "SUID core dumps disabled (fs.suid_dumpable=0)"
	else
		result_warn "S45" "SUID core dumps enabled (credential leak risk)"
	fi

	# Kernel CVE Profiling
	local kver
	kver=$(uname -r)
	local kmain
	kmain=$(echo "$kver" | awk -F. '{print $1"."$2}')
	local kpatch
	kpatch=$(echo "$kver" | awk -F. '{print $3}' | sed 's/-.*//')

	local dirty_cow=false
	local dirty_pipe=false

	# Dirty COW (CVE-2016-5195) affects < 4.8.3, or 3.x, 2.x
	if echo "$kmain" | grep -qE "^[23]\." || ([ "$kmain" = "4.8" ] && [ "$kpatch" -lt 3 ]); then dirty_cow=true; fi
	# Dirty Pipe (CVE-2022-0847) affects 5.8 to 5.16.11, 5.15.25, 5.10.102
	if echo "$kmain" | grep -qE "^5\.(8|9|1[0-6])$"; then
		if [ "$kmain" = "5.16" ] && [ "$kpatch" -lt 11 ] 2> /dev/null; then dirty_pipe=true; fi
		if [ "$kmain" = "5.15" ] && [ "$kpatch" -lt 25 ] 2> /dev/null; then dirty_pipe=true; fi
		if [ "$kmain" = "5.10" ] && [ "$kpatch" -lt 102 ] 2> /dev/null; then dirty_pipe=true; fi
	fi

	if $dirty_cow; then
		result_critical "S60" "Kernel ${kver} is potentially vulnerable to Dirty COW (CVE-2016-5195) LPE"
	fi
	if $dirty_pipe; then
		result_critical "S61" "Kernel ${kver} is potentially vulnerable to Dirty Pipe (CVE-2022-0847) LPE"
	fi
	if ! $dirty_cow && ! $dirty_pipe; then
		result_pass "S60" "Kernel ${kver} is not vulnerable to common Dirty COW/Pipe LPEs"
	fi
}

check_filesystem_security() {
	section "SYSTEM" "Filesystem Security"

	local shadow_perms
	shadow_perms=$(stat -c '%a' /etc/shadow 2> /dev/null)
	if [ -n "$shadow_perms" ]; then
		if is_more_permissive_than "$shadow_perms" "640"; then
			result_critical "S46" "/etc/shadow is ${shadow_perms} (should be <=640)"
		else
			result_pass "S46" "/etc/shadow permissions OK (${shadow_perms})"
		fi
	fi

	local passwd_perms
	passwd_perms=$(stat -c '%a' /etc/passwd 2> /dev/null)
	if is_more_permissive_than "$passwd_perms" "644"; then
		result_fail "S47" "/etc/passwd is ${passwd_perms} (should be <=644)"
	else
		result_pass "S47" "/etc/passwd permissions OK (${passwd_perms})"
	fi

	local tmp_mount
	tmp_mount=$(mount 2> /dev/null | grep " /tmp ")
	if [ -n "$tmp_mount" ]; then
		if echo "$tmp_mount" | grep -q "noexec"; then
			result_pass "S48" "/tmp mounted with noexec"
		else
			result_warn "S48" "/tmp is not mounted with noexec (malware execution risk)"
		fi
		if echo "$tmp_mount" | grep -q "nosuid"; then
			result_pass "S49" "/tmp mounted with nosuid"
		else
			result_warn "S49" "/tmp is not mounted with nosuid"
		fi
	else
		result_warn "S48" "/tmp is not a separate mount (recommend separate partition with noexec,nosuid)"
	fi

	local tmp_sticky
	tmp_sticky=$(stat -c '%a' /tmp 2> /dev/null)
	if [ "${tmp_sticky:0:1}" = "1" ] || [ "$tmp_sticky" = "1777" ]; then
		result_pass "S50" "/tmp has sticky bit set"
	else
		result_fail "S50" "/tmp missing sticky bit (${tmp_sticky})"
	fi

	local writable_scripts
	writable_scripts=$(find /usr/local/hestia/bin/ -perm -o+w -type f 2> /dev/null | wc -l)
	if [ "$writable_scripts" -gt 0 ]; then
		result_critical "S51" "${writable_scripts} world-writable script(s) in Hestia bin/"
	else
		result_pass "S51" "No world-writable scripts in Hestia bin/"
	fi

	local cron_perms
	cron_perms=$(stat -c '%a' /etc/crontab 2> /dev/null)
	if is_more_permissive_than "$cron_perms" "600"; then
		result_warn "S52" "/etc/crontab is ${cron_perms} (recommend 600)"
	else
		result_pass "S52" "/etc/crontab permissions OK (${cron_perms})"
	fi
}
