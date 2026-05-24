#!/bin/bash

FRONTEND_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${FRONTEND_DIR}/ssl.sh"
source "${FRONTEND_DIR}/headers.sh"
source "${FRONTEND_DIR}/info.sh"
source "${FRONTEND_DIR}/cookies.sh"
source "${FRONTEND_DIR}/redirects.sh"
source "${FRONTEND_DIR}/advanced.sh"

run_frontend_checks() {
	local target="$1"

	if [[ "$target" == https://* ]] || [[ "$target" == http://* ]]; then
		scan_url "$target"
	else
		local user="$target"
		if [ -n "$user" ]; then
			for domain in $(hestia_domain_list "$user"); do
				scan_url "https://${domain}"
			done
		else
			for u in $(hestia_user_list); do
				for domain in $(hestia_domain_list "$u"); do
					scan_url "https://${domain}"
				done
			done
		fi
	fi
}

scan_url() {
	local url="$1"
	local domain
	domain=$(echo "$url" | sed -E 's|https?://||' | sed 's|/.*||')

	section "FRONTEND" "${domain}"

	check_ssl "$url" "$domain"
	check_headers "$url" "$domain"
	check_info_disclosure "$url" "$domain"
	check_cookies "$url" "$domain"
	check_redirects "$url" "$domain"
	check_advanced_frontend "$url" "$domain"
}
