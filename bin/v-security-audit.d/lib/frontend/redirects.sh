#!/bin/bash

check_redirects() {
	local url="$1" domain="$2"

	local redirect_params=("redirect" "url" "next" "dest" "return_to" "continue" "rurl" "return" "goto" "target")
	local open_redirect_found=false
	for rparam in "${redirect_params[@]}"; do
		local redirect_test
		redirect_test=$(curl -sI -m 5 "${url}/?${rparam}=https://evil.com" 2> /dev/null)
		local redirect_loc
		redirect_loc=$(echo "$redirect_test" | grep -i "^location:" | tr -d '\r')
		if echo "$redirect_loc" | grep -qi "evil.com"; then
			result_fail "F31" "Open redirect vulnerability via ?${rparam}= [${domain}]"
			open_redirect_found=true
			break
		fi
	done
	if ! $open_redirect_found; then
		result_pass "F31" "No open redirect found (tested ${#redirect_params[@]} params) [${domain}]"
	fi

	local robots
	robots=$(curl -s -m 5 "${url}/robots.txt" 2> /dev/null)
	if echo "$robots" | grep -qiE "Disallow:.*(admin|backup|config|database|install|private|secret)"; then
		result_warn "F32" "robots.txt reveals sensitive paths [${domain}]"
	else
		result_pass "F32" "robots.txt clean (no sensitive paths exposed) [${domain}]"
	fi

	local sitemap_status
	sitemap_status=$(curl -sI -m 5 "${url}/sitemap.xml" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$sitemap_status" = "200" ]; then
		result_pass "F33" "sitemap.xml is accessible [${domain}]"
	else
		result_info "F33" "No sitemap.xml found [${domain}]"
	fi
}
