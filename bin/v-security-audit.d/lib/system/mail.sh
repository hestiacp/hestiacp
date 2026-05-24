#!/bin/bash

check_mail() {
	section "SYSTEM" "Mail Security"

	if ! is_service_active exim4; then
		result_skip "S35" "Exim is not running, skipping mail checks"
		return
	fi

	if [ -f /etc/exim4/exim4.conf.template ]; then
		local relay_hosts
		relay_hosts=$(grep -i "hostlist relay_from_hosts" /etc/exim4/exim4.conf.template 2> /dev/null)
		if echo "$relay_hosts" | grep -qE "0\.0\.0\.0|::\s*$|\*"; then
			result_critical "S35" "Exim may be configured as open relay"
		else
			result_pass "S35" "Exim relay is restricted"
		fi
	else
		result_info "S35" "Exim config template not found (non-standard setup)"
	fi

	if is_service_active clamav-daemon; then
		local clamav_age
		clamav_age=$(file_age_days "/var/lib/clamav/daily.cld")
		local clamav_age2
		clamav_age2=$(file_age_days "/var/lib/clamav/daily.cvd")
		local min_age=$clamav_age
		if [ "$clamav_age2" -lt "$min_age" ]; then min_age=$clamav_age2; fi

		if [ "$min_age" -le 7 ]; then
			result_pass "S36" "ClamAV definitions are current (${min_age} days old)"
		elif [ "$min_age" -le 14 ]; then
			result_warn "S36" "ClamAV definitions are ${min_age} days old"
		else
			result_fail "S36" "ClamAV definitions are STALE (${min_age} days old)"
		fi
	else
		result_info "S36" "ClamAV is not running"
	fi

	local total_mail_domains=0
	local dkim_configured=0
	local spf_configured=0
	for user in $(hestia_user_list); do
		local mail_conf="/usr/local/hestia/data/users/${user}/mail.conf"
		if [ -f "$mail_conf" ]; then
			while IFS= read -r line; do
				local domain
				domain=$(echo "$line" | grep "^DOMAIN=" | cut -d"'" -f2)
				if [ -z "$domain" ]; then continue; fi
				total_mail_domains=$((total_mail_domains + 1))

				local dkim_key="/home/${user}/conf/mail/${domain}/dkim.pem"
				if [ -f "$dkim_key" ]; then
					dkim_configured=$((dkim_configured + 1))
				fi

				if command_exists dig; then
					local spf
					spf=$(dig +short TXT "$domain" 2> /dev/null | grep "v=spf1" || true)
					if [ -n "$spf" ]; then
						spf_configured=$((spf_configured + 1))
					fi
				fi
			done < "$mail_conf"
		fi
	done

	if [ $total_mail_domains -gt 0 ]; then
		if [ $dkim_configured -eq $total_mail_domains ]; then
			result_pass "S37" "DKIM configured for all ${total_mail_domains} mail domain(s)"
		else
			result_warn "S37" "DKIM configured for ${dkim_configured}/${total_mail_domains} mail domain(s)"
		fi

		if command_exists dig; then
			if [ $spf_configured -eq $total_mail_domains ]; then
				result_pass "S38" "SPF records found for all ${total_mail_domains} mail domain(s)"
			else
				result_warn "S38" "SPF records found for ${spf_configured}/${total_mail_domains} mail domain(s)"
			fi
		else
			result_skip "S38" "dig not available, skipping SPF check (install dnsutils)"
		fi
	else
		result_info "S37" "No mail domains configured"
	fi
}

check_dns_mail_advanced() {
	section "SYSTEM" "DNS & Mail Advanced"

	if ! command_exists dig; then
		result_skip "S59" "dig not available, skipping advanced DNS checks"
		return
	fi

	local checked_domains=0
	local dmarc_configured=0
	for user in $(hestia_user_list); do
		local mail_conf="/usr/local/hestia/data/users/${user}/mail.conf"
		if [ -f "$mail_conf" ]; then
			while IFS= read -r line; do
				local domain
				domain=$(echo "$line" | grep "^DOMAIN=" | cut -d"'" -f2)
				if [ -z "$domain" ]; then continue; fi
				checked_domains=$((checked_domains + 1))

				local dmarc
				dmarc=$(dig +short TXT "_dmarc.${domain}" 2> /dev/null | grep "v=DMARC1" || true)
				if [ -n "$dmarc" ]; then
					dmarc_configured=$((dmarc_configured + 1))
				fi
			done < "$mail_conf"
		fi
	done

	if [ $checked_domains -gt 0 ]; then
		if [ $dmarc_configured -eq $checked_domains ]; then
			result_pass "S59" "DMARC configured for all ${checked_domains} mail domain(s)"
		elif [ $dmarc_configured -gt 0 ]; then
			result_warn "S59" "DMARC configured for ${dmarc_configured}/${checked_domains} mail domain(s)"
		else
			result_fail "S59" "DMARC not configured for any mail domain"
		fi
	fi

	local hostname_resolves
	hostname_resolves=$(dig +short "$(hostname -f)" A 2> /dev/null | head -1)
	if [ -n "$hostname_resolves" ]; then
		result_pass "S65" "Server hostname resolves to ${hostname_resolves}"
	else
		result_warn "S65" "Server hostname does not resolve (mail delivery issues)"
	fi
}
