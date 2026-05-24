#!/bin/bash

BACKEND_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "${BACKEND_DIR}/exposure.sh"
source "${BACKEND_DIR}/malware.sh"
source "${BACKEND_DIR}/cms.sh"
source "${BACKEND_DIR}/misc.sh"
source "${BACKEND_DIR}/permissions.sh"

run_backend_checks() {
	local target_user="$1"
	local target_domain="$2"

	if [ -n "$target_user" ] && [ -n "$target_domain" ]; then
		scan_domain "$target_user" "$target_domain"
	elif [ -n "$target_user" ]; then
		for domain in $(hestia_domain_list "$target_user"); do
			scan_domain "$target_user" "$domain"
		done
	else
		for user in $(hestia_user_list); do
			for domain in $(hestia_domain_list "$user"); do
				scan_domain "$user" "$domain"
			done
		done
	fi
}

scan_domain() {
	local user="$1"
	local domain="$2"
	local docroot="/home/${user}/web/${domain}/public_html"

	if [ ! -d "$docroot" ]; then
		result_skip "BACKEND" "Document root not found: ${docroot}"
		return
	fi

	local cms="static"
	if [ -f "${docroot}/wp-config.php" ]; then
		cms="wordpress"
	elif [ -f "${docroot}/../artisan" ] || [ -f "${docroot}/../.env" ]; then
		cms="laravel"
	elif [ -f "${docroot}/sites/default/settings.php" ]; then
		cms="drupal"
	elif [ -f "${docroot}/configuration.php" ]; then
		cms="joomla"
	elif [ -f "${docroot}/../app/etc/env.php" ] || [ -f "${docroot}/app/etc/env.php" ]; then
		cms="magento"
	elif [ -f "${docroot}/config/settings.inc.php" ] || [ -f "${docroot}/app/config/parameters.php" ]; then
		cms="prestashop"
	elif [ -f "${docroot}/config.php" ] && grep -q "opencart" "${docroot}/config.php" 2> /dev/null; then
		cms="opencart"
	elif [ -f "${docroot}/config.php" ] && [ -f "${docroot}/version.php" ] && grep -q "MOODLE" "${docroot}/version.php" 2> /dev/null; then
		cms="moodle"
	elif find "$docroot" -maxdepth 1 -name "*.php" 2> /dev/null | grep -q .; then
		cms="generic-php"
	fi

	section "BACKEND" "${user} → ${domain} (${cms})"

	check_file_exposure "$docroot" "$user" "$domain"
	check_malware "$docroot" "$user" "$domain"
	check_cms_hardening "$docroot" "$user" "$domain" "$cms"
	check_permissions "$docroot" "$user" "$domain"
}
