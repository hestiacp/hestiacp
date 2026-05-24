#!/bin/bash

check_info_disclosure() {
	local url="$1" domain="$2"

	local env_status
	env_status=$(curl -sI -m 5 "${url}/.env" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$env_status" = "200" ]; then
		result_critical "F50" "/.env is accessible (HTTP 200) [${domain}]"
	else
		result_pass "F50" "/.env properly blocked (HTTP ${env_status:-timeout}) [${domain}]"
	fi

	local git_status
	git_status=$(curl -sI -m 5 "${url}/.git/config" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$git_status" = "200" ]; then
		result_critical "F51" "/.git/config is accessible (HTTP 200) [${domain}]"
	else
		result_pass "F51" "/.git/config properly blocked (HTTP ${git_status:-timeout}) [${domain}]"
	fi

	local wpconfig_status
	wpconfig_status=$(curl -s -m 5 "${url}/wp-config.php" 2> /dev/null)
	if echo "$wpconfig_status" | grep -q '<?php'; then
		result_critical "F20" "wp-config.php source code is accessible [${domain}]"
	else
		result_pass "F20" "wp-config.php not leaking source [${domain}]"
	fi

	local server_status
	server_status=$(curl -sI -m 5 "${url}/server-status" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$server_status" = "200" ]; then
		result_fail "F21" "/server-status is exposed [${domain}]"
	else
		result_pass "F21" "/server-status is blocked [${domain}]"
	fi

	local server_info_sc
	server_info_sc=$(curl -sI -m 5 "${url}/server-info" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$server_info_sc" = "200" ]; then
		result_fail "F22" "/server-info is exposed [${domain}]"
	else
		result_pass "F22" "/server-info is blocked [${domain}]"
	fi

	local traversal
	traversal=$(curl -s -m 5 "${url}/../../../../etc/passwd" 2> /dev/null)
	if echo "$traversal" | grep -q "root:x:0:0"; then
		result_critical "F23" "Path traversal vulnerability detected [${domain}]"
	else
		result_pass "F23" "Path traversal blocked [${domain}]"
	fi

	local body
	body=$(curl -s -m 10 -L "${url}/" 2> /dev/null | head -200)
	if echo "$body" | grep -qE "Warning:.*on line|Fatal error:|Parse error:|Notice:.*on line|Stack trace:"; then
		result_fail "F24" "PHP errors visible in page output [${domain}]"
	else
		result_pass "F24" "No PHP errors visible [${domain}]"
	fi

	local dirlist
	dirlist=$(curl -s -m 5 "${url}/wp-content/" 2> /dev/null)
	if echo "$dirlist" | grep -qi "Index of /"; then
		result_fail "F25" "Directory listing is enabled [${domain}]"
	else
		result_pass "F25" "Directory listing is disabled [${domain}]"
	fi

	local xmlrpc_status
	xmlrpc_status=$(curl -sI -m 5 "${url}/xmlrpc.php" 2> /dev/null | head -1 | awk '{print $2}')
	if [ "$xmlrpc_status" = "200" ] || [ "$xmlrpc_status" = "405" ]; then
		local xmlrpc_body
		xmlrpc_body=$(curl -s -m 5 -d '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>' "${url}/xmlrpc.php" 2> /dev/null)
		if echo "$xmlrpc_body" | grep -q "methodResponse"; then
			result_warn "F26" "xmlrpc.php is active and responding [${domain}]"
		else
			result_pass "F26" "xmlrpc.php exists but methods are restricted [${domain}]"
		fi
	else
		result_pass "F26" "xmlrpc.php is blocked [${domain}]"
	fi

	local users_api
	users_api=$(curl -s -m 5 "${url}/wp-json/wp/v2/users" 2> /dev/null)
	if echo "$users_api" | grep -q '"slug"'; then
		result_warn "F27" "WordPress user enumeration is possible via REST API [${domain}]"
	else
		result_pass "F27" "WordPress user enumeration is blocked [${domain}]"
	fi
}
