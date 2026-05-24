#!/bin/bash

check_services() {
	section "SYSTEM" "Services"

	if is_service_active nginx; then
		result_pass "S26" "Nginx is running"
	else
		result_fail "S26" "Nginx is NOT running"
	fi

	local php_versions
	php_versions=$(systemctl list-units --type=service --state=active 2> /dev/null | grep "php.*fpm" | awk '{print $1}')
	if [ -n "$php_versions" ]; then
		local count
		count=$(echo "$php_versions" | wc -l)
		result_pass "S27" "${count} PHP-FPM service(s) running"
	else
		result_fail "S27" "No PHP-FPM services running"
	fi

	if is_service_active mariadb; then
		result_pass "S28" "MariaDB is running"
	elif is_service_active mysql; then
		result_pass "S28" "MySQL is running"
	else
		result_fail "S28" "Database service is NOT running"
	fi

	local root_php
	root_php=$(ps aux 2> /dev/null | grep -E "^root.*php" | grep -v grep | wc -l)
	local root_node
	root_node=$(ps aux 2> /dev/null | grep -E "^root.*node" | grep -v grep | wc -l)
	if [ "$root_php" -gt 0 ] || [ "$root_node" -gt 0 ]; then
		result_warn "S30" "Found processes running as root: PHP(${root_php}) Node(${root_node})"
	else
		result_pass "S30" "No PHP/Node processes running as root"
	fi
}
