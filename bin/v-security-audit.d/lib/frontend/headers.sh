#!/bin/bash

check_headers() {
	local url="$1" domain="$2"

	local headers
	headers=$(curl -sI -m 10 -L "${url}/" 2> /dev/null)

	if [ -z "$headers" ]; then
		result_fail "F10" "Cannot fetch headers from ${url}"
		return
	fi

	local xfo
	xfo=$(echo "$headers" | grep -i "^x-frame-options:" | tr -d '\r')
	local csp_fa
	csp_fa=$(echo "$headers" | grep -i "^content-security-policy:" | grep -i "frame-ancestors" | tr -d '\r')
	if [ -n "$xfo" ] || [ -n "$csp_fa" ]; then
		result_pass "F10" "Clickjacking protection present [${domain}]"
	else
		result_fail "F10" "Missing X-Frame-Options and CSP frame-ancestors [${domain}]"
	fi

	local xcto
	xcto=$(echo "$headers" | grep -i "^x-content-type-options:" | tr -d '\r')
	if echo "$xcto" | grep -qi "nosniff"; then
		result_pass "F11" "X-Content-Type-Options: nosniff [${domain}]"
	else
		result_fail "F11" "Missing X-Content-Type-Options: nosniff [${domain}]"
	fi

	local rp
	rp=$(echo "$headers" | grep -i "^referrer-policy:" | tr -d '\r')
	if [ -n "$rp" ]; then
		result_pass "F12" "Referrer-Policy is set [${domain}]"
	else
		result_warn "F12" "Missing Referrer-Policy header [${domain}]"
	fi

	local pp
	pp=$(echo "$headers" | grep -i "^permissions-policy:" | tr -d '\r')
	if [ -n "$pp" ]; then
		result_pass "F13" "Permissions-Policy is set [${domain}]"
	else
		result_warn "F13" "Missing Permissions-Policy header [${domain}]"
	fi

	local csp
	csp=$(echo "$headers" | grep -i "^content-security-policy:" | tr -d '\r')
	if [ -n "$csp" ]; then
		result_pass "F14" "Content-Security-Policy is set [${domain}]"
	else
		result_warn "F14" "Missing Content-Security-Policy header [${domain}]"
	fi

	local xpb
	xpb=$(echo "$headers" | grep -i "^x-powered-by:" | tr -d '\r')
	if [ -n "$xpb" ]; then
		result_warn "F15" "X-Powered-By header exposed: ${xpb} [${domain}]"
	else
		result_pass "F15" "X-Powered-By header not exposed [${domain}]"
	fi

	local server_header
	server_header=$(echo "$headers" | grep -i "^server:" | tr -d '\r')
	if echo "$server_header" | grep -qE "[0-9]+\.[0-9]+"; then
		result_warn "F16" "Server version exposed: ${server_header} [${domain}]"
	else
		result_pass "F16" "Server version not exposed [${domain}]"
	fi

	local hsts
	hsts=$(echo "$headers" | grep -i "^strict-transport-security:" | tr -d '\r')
	if [ -n "$hsts" ]; then
		local max_age
		max_age=$(echo "$hsts" | tr -d '\r\n' | sed -n 's/.*max-age=\([0-9]*\).*/\1/Ip')
		if [ -n "$max_age" ] && [ "$max_age" -ge 31536000 ] 2> /dev/null; then
			result_pass "F17" "HSTS enabled (max-age=${max_age}) [${domain}]"
		else
			result_warn "F17" "HSTS max-age too low: ${max_age} (recommend ≥31536000) [${domain}]"
		fi
	else
		result_fail "F17" "Missing Strict-Transport-Security header [${domain}]"
	fi

	local cors_check
	cors_check=$(curl -sI -m 5 -H "Origin: https://evil.com" -L "${url}/" 2> /dev/null)
	local acao
	acao=$(echo "$cors_check" | grep -i "^access-control-allow-origin:" | tr -d '\r\n')
	if echo "$acao" | grep -qi "evil\.com"; then
		result_critical "F18" "CORS Misconfiguration: reflects arbitrary Origin (https://evil.com) [${domain}]"
	elif echo "$acao" | grep -q "\*"; then
		result_warn "F18" "CORS allows wildcard (*) Origin [${domain}]"
	else
		result_pass "F18" "CORS policy correctly restricts cross-origin access [${domain}]"
	fi
}
