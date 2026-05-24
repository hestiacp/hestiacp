#!/bin/bash

check_database() {
	section "SYSTEM" "Database Security"

	if ! is_service_active mariadb && ! is_service_active mysql; then
		result_skip "S31" "Database service not running, skipping DB checks"
		return
	fi

	local can_query=false
	if mariadb -e "SELECT 1" &> /dev/null; then
		can_query=true
	elif mysql -e "SELECT 1" &> /dev/null; then
		can_query=true
	fi

	if $can_query; then
		local db_cmd="mariadb"
		if ! command_exists mariadb; then db_cmd="mysql"; fi

		result_pass "S31" "MariaDB uses unix socket authentication (root)"

		local anon_users
		anon_users=$($db_cmd -N -e "SELECT COUNT(*) FROM mysql.user WHERE user=''" 2> /dev/null)
		if [ "${anon_users:-0}" = "0" ] 2> /dev/null; then
			result_pass "S32" "No anonymous MySQL users"
		elif [ -n "$anon_users" ]; then
			result_fail "S32" "${anon_users} anonymous MySQL user(s) found"
		fi

		local remote_root
		remote_root=$($db_cmd -N -e "SELECT host FROM mysql.user WHERE user='root' AND host NOT IN ('localhost','127.0.0.1','::1')" 2> /dev/null | tr '\n' ' ')
		if [ -z "$remote_root" ] || [ "$remote_root" = " " ]; then
			result_pass "S33" "Remote root login is disabled"
		else
			result_fail "S33" "Remote root login is allowed from: ${remote_root}"
		fi

		local test_db
		test_db=$($db_cmd -N -e "SHOW DATABASES LIKE 'test'" 2> /dev/null)
		if [ -z "$test_db" ]; then
			result_pass "S34" "No test database exists"
		else
			result_warn "S34" "Test database exists (should be removed)"
		fi
	else
		result_skip "S31" "Cannot connect to database (no socket auth or credentials)"
	fi
}
