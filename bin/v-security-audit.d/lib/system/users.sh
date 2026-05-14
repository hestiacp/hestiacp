#!/bin/bash

check_user_accounts() {
	section "SYSTEM" "User Accounts & Authentication"

	local uid0_count
	uid0_count=$(awk -F: '$3 == 0 {print $1}' /etc/passwd 2> /dev/null | wc -l)
	if [ "$uid0_count" -gt 1 ]; then
		local uid0_users
		uid0_users=$(awk -F: '$3 == 0 {print $1}' /etc/passwd | tr '\n' ', ' | sed 's/,$//')
		result_critical "S53" "Multiple UID 0 accounts found: ${uid0_users}"
	else
		result_pass "S53" "Only root has UID 0"
	fi

	local empty_pass
	empty_pass=$(awk -F: '($2 == "") {print $1}' /etc/shadow 2> /dev/null | head -5)
	if [ -n "$empty_pass" ]; then
		result_critical "S54" "Accounts with empty passwords: $(echo $empty_pass | tr '\n' ', ')"
	else
		result_pass "S54" "No accounts with empty passwords"
	fi

	local login_shells
	login_shells=$(grep -cvE '(/nologin|/false|/sync|/shutdown|/halt)$' /etc/passwd 2> /dev/null)
	result_info "S55" "${login_shells} accounts have valid login shells"

	if [ -f /etc/login.defs ]; then
		local pass_max_days
		pass_max_days=$(grep "^PASS_MAX_DAYS" /etc/login.defs 2> /dev/null | awk '{print $2}')
		if [ -n "$pass_max_days" ] && [ "$pass_max_days" -le 90 ] 2> /dev/null; then
			result_pass "S56" "Password max age is ${pass_max_days} days"
		elif [ -n "$pass_max_days" ] && [ "$pass_max_days" -gt 365 ] 2> /dev/null; then
			result_warn "S56" "Password max age is ${pass_max_days} days (recommend <=90)"
		else
			result_info "S56" "Password max age: ${pass_max_days:-unset} days"
		fi
	fi

	local ntp_active=false
	if is_service_active systemd-timesyncd; then
		ntp_active=true
	elif is_service_active ntp; then
		ntp_active=true
	elif is_service_active chrony; then
		ntp_active=true
	fi
	if $ntp_active; then
		result_pass "S57" "Time synchronization service is running"
	else
		result_warn "S57" "No NTP/time sync service detected (clock drift risk)"
	fi

	if command_exists aa-status; then
		local aa_profiles
		aa_profiles=$(aa-status --profiled 2> /dev/null || echo 0)
		if [ "${aa_profiles:-0}" -gt 0 ] 2> /dev/null; then
			result_pass "S58" "AppArmor active with ${aa_profiles} profile(s)"
		else
			result_info "S58" "AppArmor installed but no profiles loaded"
		fi
	else
		result_info "S58" "AppArmor not installed (optional hardening)"
	fi

	# Cron Job Permission Hijacks
	local cron_vulnerable=false
	local cron_files="/etc/crontab /etc/cron.d/* /etc/cron.hourly/* /etc/cron.daily/* /etc/cron.weekly/* /etc/cron.monthly/* /var/spool/cron/crontabs/root"
	for cfile in $cron_files; do
		if [ -f "$cfile" ]; then
			local risky_jobs
			risky_jobs=$(grep -v '^#' "$cfile" 2> /dev/null | grep -E "(/home/|/tmp/|/var/www/|/var/tmp/)" | awk '{for(i=6;i<=NF;i++) print $i}' | sed 's/;//' | grep -v 'root' 2> /dev/null || true)
			if [ -n "$risky_jobs" ]; then
				for task in $risky_jobs; do
					if [ -f "$task" ]; then
						local file_owner
						file_owner=$(stat -c '%U' "$task" 2> /dev/null)
						local file_perms
						file_perms=$(stat -c '%a' "$task" 2> /dev/null)
						if [ "$file_owner" != "root" ] || echo "$file_perms" | grep -qE "(.[2367].)|(..[2367])"; then
							result_critical "S64" "Root cron job executes user-writable script: ${task} [LPE risk in ${cfile}]"
							cron_vulnerable=true
						fi
					fi
				done
			fi
		fi
	done
	if ! $cron_vulnerable; then
		result_pass "S64" "No root cron jobs execute user-writable scripts"
	fi
}
