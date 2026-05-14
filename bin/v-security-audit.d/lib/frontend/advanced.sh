#!/bin/bash

check_advanced_frontend() {
	local url="$1" domain="$2"

	local http2_test
	http2_test=$(curl -sI --http2 -m 5 "${url}/" 2> /dev/null | head -1)
	if echo "$http2_test" | grep -q "HTTP/2"; then
		result_pass "F35" "HTTP/2 is supported [${domain}]"
	else
		result_info "F35" "HTTP/2 not detected (HTTP/1.1 only) [${domain}]"
	fi

	local ocsp_test
	ocsp_test=$(echo | openssl s_client -servername "$domain" -connect "${domain}:443" -status 2> /dev/null | grep -i "OCSP Response Status")
	if echo "$ocsp_test" | grep -qi "successful"; then
		result_pass "F36" "OCSP stapling is enabled [${domain}]"
	else
		result_info "F36" "OCSP stapling not detected [${domain}]"
	fi

	if command_exists dig; then
		local caa
		caa=$(dig +short CAA "$domain" 2> /dev/null)
		if [ -n "$caa" ]; then
			result_pass "F37" "CAA DNS record is configured [${domain}]"
		else
			result_warn "F37" "No CAA DNS record (any CA can issue certificates) [${domain}]"
		fi
	fi

	local author_enum
	author_enum=$(curl -sI -m 5 "${url}/?author=1" 2> /dev/null | grep -i "^location:" | tr -d '\r')
	if echo "$author_enum" | grep -qi "/author/"; then
		result_warn "F38" "WordPress author enumeration possible via ?author=1 [${domain}]"
	else
		result_pass "F38" "Author enumeration blocked [${domain}]"
	fi

	local login_url="${url}/wp-login.php"
	local login_status
	login_status=$(curl -sI -m 5 "$login_url" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$login_status" = "200" ]; then
		local login_body
		login_body=$(curl -s -m 5 "$login_url" 2> /dev/null)
		if echo "$login_body" | grep -q "wp-login"; then
			result_warn "F39" "WordPress login page publicly accessible (consider IP restriction) [${domain}]"
		fi
	else
		result_pass "F39" "WordPress login page not directly exposed [${domain}]"
	fi

	local debug_headers
	debug_headers=$(curl -sI -m 5 "${url}/" 2> /dev/null | grep -iE "^(x-debug|x-aspnet|x-runtime|x-request-id|x-amzn|x-cache-key|x-varnish):" | tr -d '\r')
	if [ -n "$debug_headers" ]; then
		result_warn "F40" "Debug/internal headers exposed [${domain}]"
	else
		result_pass "F40" "No debug headers exposed [${domain}]"
	fi

	for bpath in /backup /backups /db /database /old /temp /test /staging /dev; do
		local bstatus
		bstatus=$(curl -sI -m 3 "${url}${bpath}/" 2> /dev/null | head -1 | awk '{print $2}')
		if [ "$bstatus" = "200" ] || [ "$bstatus" = "301" ]; then
			result_warn "F41" "Sensitive path accessible: ${bpath}/ (HTTP ${bstatus}) [${domain}]"
			break
		fi
	done

	local wpcron_status
	wpcron_status=$(curl -sI -m 5 "${url}/wp-cron.php" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$wpcron_status" = "200" ]; then
		result_warn "F42" "wp-cron.php is publicly accessible (DDoS amplification risk) [${domain}]"
	else
		result_pass "F42" "wp-cron.php is not publicly exposed [${domain}]"
	fi

	local generator
	generator=$(curl -s -m 5 -L "${url}/" 2> /dev/null | sed -n 's/.*<meta name="generator" content="\([^"]*\)".*/\1/Ip' | head -1)
	if [ -n "$generator" ]; then
		result_warn "F43" "Technology version exposed via generator meta: ${generator} [${domain}]"
	else
		result_pass "F43" "No technology version in generator meta tag [${domain}]"
	fi

	local mixed_content
	mixed_content=$(curl -s -m 10 -L "${url}/" 2> /dev/null | grep -oE 'src="http://[^"]+"|href="http://[^"]+' | grep -v "http://${domain}" | head -3)
	if [ -n "$mixed_content" ]; then
		result_warn "F44" "Mixed content detected (HTTP resources on HTTPS page) [${domain}]"
	else
		result_pass "F44" "No mixed content [${domain}]"
	fi

	# Benchmark TTFB
	local ttfb
	ttfb=$(curl -o /dev/null -s -m 10 -w "%{time_starttransfer}" "${url}/" 2> /dev/null)
	if [ -n "$ttfb" ]; then
		local ttfb_ms=$(awk -v t="$ttfb" 'BEGIN { printf "%.0f", t * 1000 }' 2> /dev/null)
		if [ -n "$ttfb_ms" ]; then
			if [ "$ttfb_ms" -gt 1500 ]; then
				result_warn "F45" "Poor TTFB Benchmark: ${ttfb_ms}ms (Cache setup recommended) [${domain}]"
			elif [ "$ttfb_ms" -gt 600 ]; then
				result_info "F45" "Acceptable TTFB Benchmark: ${ttfb_ms}ms [${domain}]"
			elif [ "$ttfb_ms" -gt 0 ]; then
				result_pass "F45" "Excellent TTFB Benchmark: ${ttfb_ms}ms [${domain}]"
			fi
		fi
	fi

	local security_txt_status
	security_txt_status=$(curl -sI -m 5 "${url}/.well-known/security.txt" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$security_txt_status" = "200" ]; then
		result_pass "F46" "security.txt published at /.well-known/security.txt [${domain}]"
	else
		local alt_security_txt
		alt_security_txt=$(curl -sI -m 5 "${url}/security.txt" 2> /dev/null | head -1 | awk '{print $2}')
		if [ "$alt_security_txt" = "200" ]; then
			result_info "F46" "security.txt found at /security.txt (recommended: /.well-known/) [${domain}]"
		else
			result_info "F46" "No security.txt (RFC 9116 — recommended for responsible disclosure) [${domain}]"
		fi
	fi

	local page_body
	page_body=$(curl -s -m 10 -L "${url}/" 2> /dev/null)
	local external_scripts
	external_scripts=$(echo "$page_body" | grep -oE '<script[^>]+src="https?://[^"]+' | grep -v "integrity" | wc -l)
	local sri_scripts
	sri_scripts=$(echo "$page_body" | grep -oE '<script[^>]+integrity="[^"]+"' | wc -l)

	if [ "$external_scripts" -gt 0 ] && [ "$sri_scripts" -eq 0 ]; then
		result_warn "F47" "${external_scripts} external script(s) without Subresource Integrity (SRI) [${domain}]"
	elif [ "$external_scripts" -gt 0 ]; then
		result_info "F47" "${sri_scripts}/${external_scripts} external scripts use SRI [${domain}]"
	else
		result_pass "F47" "No external scripts or all have SRI [${domain}]"
	fi
}
