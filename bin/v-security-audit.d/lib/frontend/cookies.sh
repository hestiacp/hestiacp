#!/bin/bash

check_cookies() {
	local url="$1" domain="$2"

	local cookie_headers
	cookie_headers=$(curl -sI -m 10 -L "${url}/" 2> /dev/null | grep -i "^set-cookie:" | tr -d '\r')

	if [ -z "$cookie_headers" ]; then
		result_info "F28" "No cookies set on initial page load [${domain}]"
		return
	fi

	local session_cookies
	session_cookies=$(echo "$cookie_headers" | grep -iE "PHPSESSID|wordpress_logged_in|session|SESS")
	if [ -n "$session_cookies" ]; then
		if echo "$session_cookies" | grep -qi "httponly"; then
			result_pass "F28" "Session cookies have HttpOnly flag [${domain}]"
		else
			result_fail "F28" "Session cookies missing HttpOnly flag [${domain}]"
		fi

		if echo "$session_cookies" | grep -qi "secure"; then
			result_pass "F29" "Session cookies have Secure flag [${domain}]"
		else
			result_fail "F29" "Session cookies missing Secure flag [${domain}]"
		fi

		if echo "$session_cookies" | grep -qi "samesite"; then
			result_pass "F30" "Session cookies have SameSite attribute [${domain}]"
		else
			result_warn "F30" "Session cookies missing SameSite attribute [${domain}]"
		fi
	else
		result_info "F28" "No session cookies detected [${domain}]"
	fi
}
