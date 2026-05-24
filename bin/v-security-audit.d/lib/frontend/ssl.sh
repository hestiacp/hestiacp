#!/bin/bash

check_ssl() {
	local url="$1" domain="$2"

	if $SKIP_SSL; then
		result_skip "F01" "SSL checks skipped (--skip-ssl) [${domain}]"
		return
	fi

	local ssl_output
	ssl_output=$(echo | openssl s_client -servername "$domain" -connect "${domain}:443" 2> /dev/null)

	if [ -z "$ssl_output" ]; then
		result_fail "F01" "Cannot establish SSL connection [${domain}]"
		return
	fi

	local verify
	verify=$(echo "$ssl_output" | grep "Verify return code:" | awk -F: '{print $2}' | tr -d ' ')
	if [[ "$verify" == "0"* ]]; then
		result_pass "F01" "SSL certificate is valid [${domain}]"
	else
		result_fail "F01" "SSL certificate validation failed: ${verify} [${domain}]"
	fi

	local cert_text
	cert_text=$(echo "$ssl_output" | openssl x509 -noout -enddate 2> /dev/null)
	if [ -n "$cert_text" ]; then
		local expiry
		expiry=$(echo "$cert_text" | cut -d= -f2)
		local exp_epoch
		exp_epoch=$(date -d "$expiry" +%s 2> /dev/null)
		local now_epoch
		now_epoch=$(date +%s)
		if [ -n "$exp_epoch" ]; then
			local days_left=$(((exp_epoch - now_epoch) / 86400))
			if [ $days_left -lt 0 ]; then
				result_fail "F02" "SSL certificate is EXPIRED [${domain}]"
			elif [ $days_left -lt 14 ]; then
				result_warn "F02" "SSL certificate expires in ${days_left} days [${domain}]"
			else
				result_pass "F02" "SSL certificate valid for ${days_left} days [${domain}]"
			fi
		fi
	fi

	local chain_depth
	chain_depth=$(echo "$ssl_output" | grep -c "^ [0-9]")
	if [ "$chain_depth" -ge 2 ]; then
		result_pass "F03" "Certificate chain is complete (depth: ${chain_depth}) [${domain}]"
	else
		result_fail "F03" "Certificate chain may be incomplete [${domain}]"
	fi

	local tls10
	tls10=$(echo | openssl s_client -tls1 -connect "${domain}:443" 2>&1)
	if echo "$tls10" | grep -q "handshake failure\|wrong version\|no protocols\|alert protocol"; then
		result_pass "F04" "TLS 1.0 is disabled [${domain}]"
	else
		result_fail "F04" "TLS 1.0 is still enabled [${domain}]"
	fi

	local tls11
	tls11=$(echo | openssl s_client -tls1_1 -connect "${domain}:443" 2>&1)
	if echo "$tls11" | grep -q "handshake failure\|wrong version\|no protocols\|alert protocol"; then
		result_pass "F05" "TLS 1.1 is disabled [${domain}]"
	else
		result_fail "F05" "TLS 1.1 is still enabled [${domain}]"
	fi

	local tls12
	tls12=$(echo | openssl s_client -tls1_2 -connect "${domain}:443" 2>&1)
	if echo "$tls12" | grep -q "CONNECTED"; then
		result_pass "F06" "TLS 1.2 is supported [${domain}]"
	else
		result_fail "F06" "TLS 1.2 is NOT supported [${domain}]"
	fi

	local http_response
	http_response=$(curl -sI -m 5 "http://${domain}/" 2> /dev/null | head -1)
	local http_location
	http_location=$(curl -sI -m 5 "http://${domain}/" 2> /dev/null | grep -i "^location:" | tr -d '\r')
	if echo "$http_response" | grep -qE "301|302|307|308"; then
		if echo "$http_location" | grep -qi "https://"; then
			result_pass "F09" "HTTP redirects to HTTPS [${domain}]"
		else
			result_warn "F09" "HTTP redirects but not to HTTPS [${domain}]"
		fi
	else
		result_fail "F09" "HTTP does NOT redirect to HTTPS [${domain}]"
	fi
}
