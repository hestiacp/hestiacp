#!/usr/bin/env bats

# IPv6 / dual-stack tests
#
# Complements the IPv6 tests in test.bats. This file is self contained: it
# creates its own users, addresses and domains and removes them again.
#
# Addresses come from the documentation prefix 2001:db8::/32 (RFC 3849), block
# 2001:db8:6::/64 is reserved for this file:
#   ::10  shared address of the admin user (added in non canonical notation)
#   ::11  dedicated to $user
#   ::12  configured on the interface outside of Hestia
#   ::13  dedicated to $user2
#   ::14  added without prefix length

if [ "${PATH#*/usr/local/hestia/bin*}" = "$PATH" ]; then
	. /etc/profile.d/hestia.sh
fi

load 'test_helper/bats-support/load'
load 'test_helper/bats-assert/load'
load 'test_helper/bats-file/load'

function setup() {
	if [ $BATS_TEST_NUMBER = 1 ]; then
		echo 'user=test-5291' > /tmp/hestia-ipv6-test-env.sh
		echo 'user2=test-5292' >> /tmp/hestia-ipv6-test-env.sh
		echo 'HESTIA=/usr/local/hestia' >> /tmp/hestia-ipv6-test-env.sh
		echo 'domain=ipv6test-5291.hestiacp.com' >> /tmp/hestia-ipv6-test-env.sh
		echo "interface=$(/usr/local/hestia/bin/v-list-sys-interfaces plain | head -n 1)" >> /tmp/hestia-ipv6-test-env.sh
	fi

	source /tmp/hestia-ipv6-test-env.sh
	source $HESTIA/func/main.sh
	source $HESTIA/conf/hestia.conf
	source $HESTIA/func/ip.sh
	source $HESTIA/func/firewall.sh

	admin="${ROOT_USER:-admin}"
	ip6_shared='2001:db8:6::10'
	ip6_user='2001:db8:6::11'
	ip6_system='2001:db8:6::12'
	ip6_user2='2001:db8:6::13'
	ip6_default='2001:db8:6::14'
}

function require_ipv6() {
	if [ "$(cat /proc/sys/net/ipv6/conf/all/disable_ipv6 2> /dev/null)" != "0" ]; then
		skip "IPv6 is disabled on this system"
	fi
}

function require_ipv6_firewall() {
	require_ipv6
	[ -z "$FIREWALL_SYSTEM" ] && skip "Firewall is disabled"
	[ ! -f "$HESTIA/data/firewall/rules6.conf" ] && skip "IPv6 firewall is disabled"
	[ ! -x /sbin/ip6tables ] && skip "ip6tables is not available"
	return 0
}

function web_conf() {
	local owner=$1 domain=$2
	cat "$HOMEDIR/$owner/conf/web/$domain/"*.conf
}

#----------------------------------------------------------#
#                     Helpers (unit)                       #
#----------------------------------------------------------#

@test "Helper: is_ipv6_address" {
	run is_ipv6_address '2001:db8::1'
	assert_success

	run is_ipv6_address '::1'
	assert_success

	run is_ipv6_address '198.18.0.1'
	assert_failure
}

@test "Helper: normalize_ipv6 returns the canonical notation" {
	run normalize_ipv6 '2001:0DB8:0000:0000:0000:0000:0000:0001'
	assert_success
	assert_output '2001:db8::1'

	run normalize_ipv6 '2001:db8:0:0:1:0:0:1'
	assert_output '2001:db8::1:0:0:1'

	# Invalid input is returned unchanged, validation happens elsewhere
	run normalize_ipv6 'not-an-ip'
	assert_output 'not-an-ip'
}

@test "Helper: hst_detect_ip_version handles compressed and prefixed IPv6" {
	run hst_detect_ip_version '::'
	assert_success
	assert_output '6'

	run hst_detect_ip_version '2001:db8::/0'
	assert_success
	assert_output '6'

	run hst_detect_ip_version '198.18.0.1/33'
	assert_failure
	refute_output

	run hst_detect_ip_version '2001:db8:::1'
	assert_failure
	refute_output
}

@test "Helper: get_ipv6_ban_networks bans the /64 of an address" {
	unset FIREWALL_IPV6_BAN_PREFIX
	run get_ipv6_ban_networks <<< 'SSH 2001:db8:7:1:2:3:4:5'
	assert_success
	assert_output 'SSH 2001:db8:7:1::/64'
}

@test "Helper: get_ipv6_ban_networks honours FIREWALL_IPV6_BAN_PREFIX" {
	FIREWALL_IPV6_BAN_PREFIX=48
	run get_ipv6_ban_networks <<< 'SSH 2001:db8:7:1:2:3:4:5'
	assert_output 'SSH 2001:db8:7::/48'

	FIREWALL_IPV6_BAN_PREFIX=128
	run get_ipv6_ban_networks <<< 'SSH 2001:db8:7:1:2:3:4:5'
	assert_output 'SSH 2001:db8:7:1:2:3:4:5'

	# Invalid values fall back to /64
	FIREWALL_IPV6_BAN_PREFIX=129
	run get_ipv6_ban_networks <<< 'SSH 2001:db8:7:1:2:3:4:5'
	assert_output 'SSH 2001:db8:7:1::/64'
}

@test "Helper: get_ipv6_ban_networks never bans the network of a server address" {
	require_ipv6
	unset FIREWALL_IPV6_BAN_PREFIX
	echo "OWNER='$admin'" > "$HESTIA/data/ips/2001:db8:7:1::1"

	run get_ipv6_ban_networks <<< 'SSH 2001:db8:7:1:2:3:4:5'
	rm -f "$HESTIA/data/ips/2001:db8:7:1::1"

	assert_success
	assert_output 'SSH 2001:db8:7:1:2:3:4:5'
}

@test "Helper: is_ipv6_ban_address_valid" {
	run is_ipv6_ban_address_valid '2001:db8::bad'
	assert_success
	refute_output

	for address in '::1' 'fe80::1' 'ff02::1' '::ffff:192.0.2.1' 'not-an-ip'; do
		run is_ipv6_ban_address_valid "$address"
		assert_failure $E_INVALID
	done
}

#----------------------------------------------------------#
#                         SETUP                            #
#----------------------------------------------------------#

@test "Setup: Add test users" {
	run v-add-user $user $user $user@hestiacp.com default "IPv6 Test"
	assert_success
	refute_output

	run v-add-user $user2 $user2 $user2@hestiacp.com default "IPv6 Test 2"
	assert_success
	refute_output
}

#----------------------------------------------------------#
#                       SYSTEM IP                          #
#----------------------------------------------------------#

@test "Ip6: Add IPv6 in non canonical notation with prefix" {
	require_ipv6
	run v-add-sys-ip '2001:0DB8:0006:0000:0000:0000:0000:0010/64' '' $interface
	assert_success
	refute_output

	# Stored under the canonical notation
	assert_file_exist $HESTIA/data/ips/$ip6_shared
	assert_file_not_exist $HESTIA/data/ips/2001:0DB8:0006:0000:0000:0000:0000:0010
	assert_file_contains $HESTIA/data/ips/$ip6_shared "OWNER='$admin'"
	assert_file_contains $HESTIA/data/ips/$ip6_shared "STATUS='shared'"
	assert_file_contains $HESTIA/data/ips/$ip6_shared "NETMASK='/64'"
	assert_file_contains $HESTIA/data/ips/$ip6_shared "VERSION='6'"

	run ip -6 addr show dev $interface
	assert_output --partial "$ip6_shared/64"

	if [ -n "$WEB_SYSTEM" ]; then
		assert_file_contains /etc/$WEB_SYSTEM/conf.d/$ip6_shared.conf "[$ip6_shared]"
	fi
	if [ -n "$PROXY_SYSTEM" ]; then
		assert_file_contains /etc/$PROXY_SYSTEM/conf.d/$ip6_shared.conf "[$ip6_shared]"
	fi

	# Web servers may bind to addresses still in duplicate address detection
	assert_file_contains /etc/sysctl.d/60-hestia-ipv6.conf "net.ipv6.ip_nonlocal_bind = 1"
}

@test "Ip6: Added IPv6 is persisted in the network config" {
	require_ipv6
	if [ -f /etc/netplan/60-hestia.yaml ]; then
		run grep -F -- "- $ip6_shared/64" /etc/netplan/60-hestia.yaml
		assert_success
	else
		assert_file_contains /etc/network/interfaces "iface $interface inet6 static"
		assert_file_contains /etc/network/interfaces "address $ip6_shared"
	fi
}

@test "Ip6: Add IPv6 (duplicate in other notation)" {
	require_ipv6
	run v-add-sys-ip '2001:db8:6:0::10' 64 $interface
	assert_failure $E_EXISTS
}

@test "Ip6: Add IPv6 without prefix length defaults to /64" {
	require_ipv6
	run v-add-sys-ip $ip6_default '' $interface
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/ips/$ip6_default "NETMASK='/64'"

	run v-delete-sys-ip $ip6_default
	assert_success
	refute_output
	assert_file_not_exist $HESTIA/data/ips/$ip6_default

	run ip -6 addr show dev $interface
	refute_output --partial "$ip6_default/"
}

@test "Ip6: Add IPv6 with invalid prefix length" {
	require_ipv6
	for prefix in 0 129 abc; do
		run v-add-sys-ip $ip6_default $prefix $interface
		assert_failure $E_INVALID
	done
	assert_file_not_exist $HESTIA/data/ips/$ip6_default
}

@test "Ip6: Add IPv6 with prefix length defined twice" {
	require_ipv6
	run v-add-sys-ip $ip6_default/64 64 $interface
	assert_failure $E_INVALID
	assert_file_not_exist $HESTIA/data/ips/$ip6_default
}

@test "Ip6: Add IPv6 with NAT address" {
	require_ipv6
	run v-add-sys-ip $ip6_default 64 $interface $admin shared '' 198.18.0.1
	assert_failure $E_INVALID
	assert_file_not_exist $HESTIA/data/ips/$ip6_default
}

@test "Ip6: Add IPv6 for a user makes it dedicated" {
	require_ipv6
	run v-add-sys-ip $ip6_user 64 $interface $user
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/ips/$ip6_user "OWNER='$user'"
	assert_file_contains $HESTIA/data/ips/$ip6_user "STATUS='dedicated'"

	run v-add-sys-ip $ip6_user2 64 $interface $user2
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/ips/$ip6_user2 "OWNER='$user2'"
}

@test "Ip6: v-list-sys-ip" {
	require_ipv6
	run v-list-sys-ip $ip6_shared json
	assert_success
	run jq -e --arg ip "$ip6_shared" '.[$ip].OWNER' <<< "$output"
	assert_success

	run v-list-sys-ip 2001:db8:zzzz::1
	assert_failure $E_INVALID
}

@test "Ip6: v-list-sys-ips lists IPv4 and IPv6 addresses" {
	require_ipv6
	run v-list-sys-ips json
	assert_success
	run jq -e --arg ip "$ip6_shared" 'has($ip)' <<< "$output"
	assert_success

	run v-list-sys-ips plain
	assert_success
	assert_line --regexp "^$ip6_shared"$'\t'"$admin"$'\t'"shared"
	assert_line --regexp "^$ip6_user"$'\t'"$user"$'\t'"dedicated"
}

@test "Ip6: v-list-user-ips lists dedicated and shared IPv6" {
	require_ipv6
	run v-list-user-ips $user plain
	assert_success
	assert_line --regexp "^$ip6_user"$'\t'"$user"$'\t'"dedicated"
	assert_line --regexp "^$ip6_shared"$'\t'"$admin"$'\t'"shared"
	refute_output --partial "$ip6_user2"

	run v-list-user-ips $user json
	assert_success
	run jq -e --arg ip "$ip6_user" 'has($ip)' <<< "$output"
	assert_success
}

@test "Ip6: v-list-network filters by IP version" {
	require_ipv6
	run v-list-network 6 plain
	assert_success
	assert_line "$ip6_shared /64 $interface"
	refute_line --regexp '^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+ '

	run v-list-network 4 list
	assert_success
	refute_output --partial ':'

	run v-list-network json
	assert_success
	run jq -e --arg ip "$ip6_shared" '.[$ip].MASK == "/64"' <<< "$output"
	assert_success
}

@test "Ip6: Change IPv6 name (non canonical notation)" {
	require_ipv6
	run v-change-sys-ip-name '2001:DB8:6::10' ipv6.hestiacp.com
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/ips/$ip6_shared "NAME='ipv6.hestiacp.com'"
}

@test "Ip6: Change IPv6 status" {
	require_ipv6
	run v-change-sys-ip-status $ip6_shared dedicated
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/ips/$ip6_shared "STATUS='dedicated'"

	run v-change-sys-ip-status '2001:DB8:6::10' shared
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/ips/$ip6_shared "STATUS='shared'"
}

@test "Ip6: Change IPv6 owner" {
	require_ipv6
	run v-change-sys-ip-owner $ip6_shared $user
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/ips/$ip6_shared "OWNER='$user'"
	assert_file_contains $HESTIA/data/ips/$ip6_shared "STATUS='dedicated'"

	run v-change-sys-ip-owner $ip6_shared $admin
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/ips/$ip6_shared "OWNER='$admin'"

	run v-change-sys-ip-status $ip6_shared shared
	assert_success
	refute_output
}

@test "Ip6: Change IPv6 NAT is not supported" {
	require_ipv6
	run v-change-sys-ip-nat $ip6_shared 198.18.0.1
	assert_failure $E_INVALID
	assert_file_contains $HESTIA/data/ips/$ip6_shared "NAT=''"
}

@test "Ip6: Delete keeps an IPv6 address not configured by Hestia" {
	require_ipv6
	run ip -6 addr add $ip6_system/64 dev $interface nodad
	assert_success

	run v-add-sys-ip $ip6_system 64 $interface
	assert_success
	refute_output
	if [ -f /etc/netplan/60-hestia.yaml ]; then
		run grep -F "$ip6_system" /etc/netplan/60-hestia.yaml
		assert_failure
	fi

	run v-delete-sys-ip $ip6_system
	assert_success
	refute_output
	assert_file_not_exist $HESTIA/data/ips/$ip6_system

	# Still configured on the interface
	run ip -6 addr show dev $interface
	assert_output --partial "$ip6_system/64"

	run ip -6 addr del $ip6_system/64 dev $interface
	assert_success
}

#----------------------------------------------------------#
#                          WEB                             #
#----------------------------------------------------------#

@test "Web6: New web domain gets the dedicated IPv6 of the user" {
	require_ipv6
	run v-add-web-domain $user dual.$domain
	assert_success
	refute_output

	USER_DATA=$HESTIA/data/users/$user
	run get_object_value 'web' 'DOMAIN' "dual.$domain" '$IP6'
	assert_output "$ip6_user"
	# Dual-stack, the IPv4 address is assigned as well
	run get_object_value 'web' 'DOMAIN' "dual.$domain" '$IP'
	assert [ -n "$output" ]

	assert_file_contains $HESTIA/data/ips/$ip6_user "U_WEB_DOMAINS='1'"
	assert_file_contains $HESTIA/data/ips/$ip6_user "U_SYS_USERS='$user'"

	run web_conf $user dual.$domain
	assert_output --partial "[$ip6_user]:"
}

@test "Web6: Web domain is reachable over IPv6" {
	require_ipv6
	echo "ipv6-dual-stack-proof" > $HOMEDIR/$user/web/dual.$domain/public_html/ipv6.html
	chown $user:$user $HOMEDIR/$user/web/dual.$domain/public_html/ipv6.html

	run curl --silent --show-error --max-time 10 --resolve "dual.$domain:80:[$ip6_user]" "http://dual.$domain/ipv6.html"
	assert_success
	assert_output --partial "ipv6-dual-stack-proof"
}

@test "Web6: Dedicated IPv6 of another user is not assigned" {
	require_ipv6
	run v-add-web-domain $user2 web2.$domain
	assert_success
	refute_output

	USER_DATA=$HESTIA/data/users/$user2
	run get_object_value 'web' 'DOMAIN' "web2.$domain" '$IP6'
	assert_output "$ip6_user2"
}

@test "Web6: Change web domain IPv6 to a dedicated IPv6 of another user" {
	require_ipv6
	run v-change-web-domain-ipv6 $user dual.$domain $ip6_user2 no
	assert_failure $E_FORBIDEN

	USER_DATA=$HESTIA/data/users/$user
	run get_object_value 'web' 'DOMAIN' "dual.$domain" '$IP6'
	assert_output "$ip6_user"
}

@test "Web6: Change web domain IPv6 (non canonical notation)" {
	require_ipv6
	run v-change-web-domain-ipv6 $user dual.$domain '2001:DB8:6:0::10' yes
	assert_success
	refute_output

	USER_DATA=$HESTIA/data/users/$user
	run get_object_value 'web' 'DOMAIN' "dual.$domain" '$IP6'
	assert_output "$ip6_shared"

	assert_file_contains $HESTIA/data/ips/$ip6_shared "U_WEB_DOMAINS='1'"
	assert_file_contains $HESTIA/data/ips/$ip6_user "U_WEB_DOMAINS='0'"

	run web_conf $user dual.$domain
	assert_output --partial "[$ip6_shared]:"
	refute_output --partial "[$ip6_user]:"

	run curl --silent --show-error --max-time 10 --resolve "dual.$domain:80:[$ip6_shared]" "http://dual.$domain/ipv6.html"
	assert_success
	assert_output --partial "ipv6-dual-stack-proof"
}

@test "Web6: v-update-sys-ip-counters recounts IPv6 usage" {
	require_ipv6
	# Break the counter on purpose
	sed -i "s/U_WEB_DOMAINS='[0-9]*'/U_WEB_DOMAINS='7'/" $HESTIA/data/ips/$ip6_shared

	run v-update-sys-ip-counters $ip6_shared
	assert_success
	assert_file_contains $HESTIA/data/ips/$ip6_shared "U_WEB_DOMAINS='1'"
}

@test "Web6: Rebuild web domains keeps IPv6" {
	require_ipv6
	run v-rebuild-web-domains $user
	assert_success
	refute_output

	run web_conf $user dual.$domain
	assert_output --partial "[$ip6_shared]:"
}

@test "Web6: Delete IPv6 in use" {
	require_ipv6
	run v-delete-sys-ip $ip6_shared
	assert_failure $E_EXISTS
	assert_file_exist $HESTIA/data/ips/$ip6_shared
}

#----------------------------------------------------------#
#                      DOMAIN OWNER                        #
#----------------------------------------------------------#

@test "Owner6: v-add-domain publishes AAAA for the web domain IPv6" {
	require_ipv6
	run v-add-domain $user owner.$domain
	assert_success
	refute_output

	USER_DATA=$HESTIA/data/users/$user
	run get_object_value 'web' 'DOMAIN' "owner.$domain" '$IP6'
	assert_output "$ip6_user"

	if [ -n "$DNS_SYSTEM" ]; then
		assert_file_contains $HESTIA/data/users/$user/dns/owner.$domain.conf "RECORD='@' TYPE='AAAA' PRIORITY='' VALUE='$ip6_user'"
	fi
}

@test "Owner6: Changing the owner moves the domain to an IPv6 of the new owner" {
	require_ipv6
	run v-change-domain-owner owner.$domain $user2
	assert_success

	USER_DATA=$HESTIA/data/users/$user2
	run get_object_value 'web' 'DOMAIN' "owner.$domain" '$IP6'
	assert_output "$ip6_user2"

	if [ -n "$DNS_SYSTEM" ]; then
		assert_file_contains $HESTIA/data/users/$user2/dns/owner.$domain.conf "RECORD='@' TYPE='AAAA' PRIORITY='' VALUE='$ip6_user2'"
		run grep -F "$ip6_user" $HESTIA/data/users/$user2/dns/owner.$domain.conf
		assert_failure
	fi

	assert_file_contains $HESTIA/data/ips/$ip6_user "U_WEB_DOMAINS='0'"
	assert_file_contains $HESTIA/data/ips/$ip6_user2 "U_WEB_DOMAINS='2'"

	run web_conf $user2 owner.$domain
	assert_output --partial "[$ip6_user2]:"
}

#----------------------------------------------------------#
#                       FIREWALL                           #
#----------------------------------------------------------#

@test "Firewall6: Registered IPv6 addresses are accepted" {
	require_ipv6_firewall
	run v-update-firewall-ipv6
	assert_success

	run ip6tables -S INPUT
	assert_success
	assert_line "-A INPUT -s $ip6_shared/128 -j ACCEPT"
	assert_line "-P INPUT DROP"
}

@test "Firewall6: ICMP rules apply to ICMPv6" {
	require_ipv6_firewall
	grep -q "PROTOCOL='ICMP'.*SUSPENDED='no'" $HESTIA/data/firewall/rules6.conf || skip "No active ICMP rule"

	run ip6tables -S INPUT
	assert_line "-A INPUT -p ipv6-icmp -j ACCEPT"
}

@test "Firewall6: Add IPv6 network rule" {
	require_ipv6_firewall
	run v-add-firewall-rule-ipv6 ACCEPT 2001:db8:6::/48 8443 TCP 'IPv6 network' 201
	assert_success
	refute_output

	assert_file_contains $HESTIA/data/firewall/rules6.conf "RULE='201' ACTION='ACCEPT' PROTOCOL='TCP' PORT='8443' IP6='2001:db8:6::/48'"

	run ip6tables -S INPUT
	assert_line --regexp '^-A INPUT -s 2001:db8:6::/48 -p tcp (-m tcp )?--dport 8443 -j ACCEPT$'
}

@test "Firewall6: Add IPv6 port range rule" {
	require_ipv6_firewall
	run v-add-firewall-rule-ipv6 DROP 2001:db8:7::1 8000-8010 UDP 'IPv6 range' 202
	assert_success
	refute_output

	run ip6tables -S INPUT
	assert_line --regexp '^-A INPUT -s 2001:db8:7::1/128 -p udp -m multiport --dports 8000:8010 -j DROP$'
}

@test "Firewall6: Add IPv6 rule (duplicate id)" {
	require_ipv6_firewall
	run v-add-firewall-rule-ipv6 ACCEPT 2001:db8:6::/48 8443 TCP '' 201
	assert_failure $E_EXISTS
}

@test "Firewall6: Add IPv6 rule with IPv4 source" {
	require_ipv6_firewall
	run v-add-firewall-rule-ipv6 ACCEPT 198.18.0.1 8443 TCP '' 203
	assert_failure $E_INVALID
	run grep "RULE='203'" $HESTIA/data/firewall/rules6.conf
	assert_failure
}

@test "Firewall6: List IPv6 rules" {
	require_ipv6_firewall
	run v-list-firewall-ipv6 json
	assert_success
	run jq -e '."201".IP6 == "2001:db8:6::/48" and ."202".PORT == "8000-8010"' <<< "$output"
	assert_success

	run v-list-firewall-ipv6 plain
	assert_success
	assert_line --regexp "^201"$'\t'"ACCEPT"$'\t'"TCP"$'\t'"8443"$'\t'"2001:db8:6::/48"
}

@test "Firewall6: Change IPv6 rule" {
	require_ipv6_firewall
	run v-change-firewall-ipv6-rule 201 DROP 2001:db8:6::1 8444 TCP 'IPv6 changed'
	assert_success
	refute_output

	assert_file_contains $HESTIA/data/firewall/rules6.conf "RULE='201' ACTION='DROP' PROTOCOL='TCP' PORT='8444' IP6='2001:db8:6::1' COMMENT='IPv6 changed'"
	run grep -c "RULE='201'" $HESTIA/data/firewall/rules6.conf
	assert_output '1'

	run ip6tables -S INPUT
	assert_line --regexp '^-A INPUT -s 2001:db8:6::1/128 -p tcp (-m tcp )?--dport 8444 -j DROP$'
	refute_output --partial '2001:db8:6::/48'
}

@test "Firewall6: Change IPv6 rule (invalid)" {
	require_ipv6_firewall
	run v-change-firewall-ipv6-rule 299 DROP 2001:db8:6::1 8444
	assert_failure $E_NOTEXIST

	run v-change-firewall-ipv6-rule 201 DROP 2001:db8:6::/129 8444
	assert_failure $E_INVALID

	run v-change-firewall-ipv6-rule 201 DROP 'ipset:missing-ipv6-list' 8444
	assert_failure $E_NOTEXIST

	# The rule is unchanged
	assert_file_contains $HESTIA/data/firewall/rules6.conf "RULE='201' ACTION='DROP' PROTOCOL='TCP' PORT='8444' IP6='2001:db8:6::1'"
}

@test "Firewall6: Suspend and unsuspend IPv6 rule" {
	require_ipv6_firewall
	run v-suspend-firewall-rule-ipv6 201
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/firewall/rules6.conf "RULE='201' .*SUSPENDED='yes'"

	run ip6tables -S INPUT
	refute_output --partial '--dport 8444'

	run v-unsuspend-firewall-rule-ipv6 201
	assert_success
	refute_output
	assert_file_contains $HESTIA/data/firewall/rules6.conf "RULE='201' .*SUSPENDED='no'"

	run ip6tables -S INPUT
	assert_output --partial '--dport 8444'
}

@test "Firewall6: Suspend IPv6 rule (not existing)" {
	require_ipv6_firewall
	run v-suspend-firewall-rule-ipv6 299
	assert_failure $E_NOTEXIST

	run v-unsuspend-firewall-rule-ipv6 299
	assert_failure $E_NOTEXIST
}

@test "Firewall6: Move IPv6 rule" {
	require_ipv6_firewall
	run v-move-firewall-rule-ipv6 202 up
	assert_success
	refute_output

	assert_file_contains $HESTIA/data/firewall/rules6.conf "RULE='201' ACTION='DROP' PROTOCOL='UDP' PORT='8000-8010' IP6='2001:db8:7::1'"
	assert_file_contains $HESTIA/data/firewall/rules6.conf "RULE='202' ACTION='DROP' PROTOCOL='TCP' PORT='8444' IP6='2001:db8:6::1'"
	run grep -c "RULE='20[12]'" $HESTIA/data/firewall/rules6.conf
	assert_output '2'

	run v-move-firewall-rule-ipv6 202 down
	assert_failure
	assert_output --partial "already the last rule"

	run v-move-firewall-rule-ipv6 202 sideways
	assert_failure

	run v-move-firewall-rule-ipv6 299 up
	assert_failure $E_NOTEXIST
}

@test "Firewall6: Add IPv6 rule with ipset" {
	require_ipv6_firewall
	printf '%s\n' 2001:db8:8:1::/64 2001:db8:8:2::/64 2001:db8:8:3::/64 2001:db8:8:4::/64 2001:db8:8:5::/64 > /tmp/hestia-ipv6-ipset.txt

	run v-add-firewall-ipset ipv6-test-list 'file:/tmp/hestia-ipv6-ipset.txt' v6 no
	assert_success
	refute_output

	run v-list-firewall-ipset-ipv6 plain
	assert_success
	assert_line --regexp "^ipv6-test-list"$'\t'"v6"

	run v-add-firewall-rule-ipv6 DROP 'ipset:ipv6-test-list' 8445 TCP 'IPv6 ipset' 203
	assert_success
	refute_output

	run ip6tables -S INPUT
	assert_line --regexp '^-A INPUT -p tcp (-m tcp )?--dport 8445 -m set --match-set ipv6-test-list src -j DROP$'
}

@test "Firewall6: Add IPv6 rule with IPv4 only ipset" {
	require_ipv6_firewall
	run v-add-firewall-rule-ipv6 DROP 'ipset:missing-ipv6-list' 8446 TCP '' 204
	assert_failure $E_NOTEXIST
}

@test "Firewall6: Add firewall chain creates the IPv6 chain" {
	require_ipv6_firewall
	run v-add-firewall-chain IPVSIXTEST 8447 TCP
	assert_success
	refute_output

	run ip6tables -S fail2ban-IPVSIXTEST
	assert_success
	run ip6tables -S INPUT
	assert_line --regexp '^-A INPUT -p tcp (-m tcp )?--dport 8447 -j fail2ban-IPVSIXTEST$'

	run v-delete-firewall-chain IPVSIXTEST
	assert_success
	refute_output

	run ip6tables -S fail2ban-IPVSIXTEST
	assert_failure
	run ip6tables -S INPUT
	refute_output --partial 'fail2ban-IPVSIXTEST'
}

@test "Firewall6: Stop and start the firewall" {
	require_ipv6_firewall
	run v-stop-firewall
	assert_success

	run ip6tables -S INPUT
	assert_line "-P INPUT ACCEPT"
	refute_output --partial '--dport 8444'

	run v-update-firewall
	assert_success

	run ip6tables -S INPUT
	assert_line "-P INPUT DROP"
	assert_output --partial '--dport 8444'
}

@test "Firewall6: Cleanup rules" {
	require_ipv6_firewall
	# Deleting a rule renumbers the remaining rules
	for comment in 'IPv6 changed' 'IPv6 range' 'IPv6 ipset'; do
		rule=$(grep "COMMENT='$comment'" $HESTIA/data/firewall/rules6.conf | cut -f 2 -d \')
		run v-delete-firewall-rule-ipv6 $rule
		assert_success
		refute_output
	done

	run v-delete-firewall-ipset ipv6-test-list
	assert_success
	refute_output
	rm -f /tmp/hestia-ipv6-ipset.txt

	run ip6tables -S INPUT
	refute_output --partial '2001:db8:6::1/128'
	refute_output --partial '2001:db8:7::1/128'
	refute_output --partial 'ipv6-test-list'
}

#----------------------------------------------------------#
#                        CLEANUP                           #
#----------------------------------------------------------#

@test "Cleanup: Delete users returns dedicated IPv6 to admin" {
	run v-delete-user $user
	assert_success
	refute_output

	run v-delete-user $user2
	assert_success
	refute_output

	if [ -f "$HESTIA/data/ips/$ip6_user" ]; then
		assert_file_contains $HESTIA/data/ips/$ip6_user "OWNER='$admin'"
		assert_file_contains $HESTIA/data/ips/$ip6_user2 "OWNER='$admin'"
		assert_file_contains $HESTIA/data/ips/$ip6_shared "U_WEB_DOMAINS='0'"
	fi
}

@test "Cleanup: Delete IPv6 addresses" {
	require_ipv6
	for address in $ip6_shared $ip6_user $ip6_user2; do
		run v-delete-sys-ip $address
		assert_success
		refute_output

		assert_file_not_exist $HESTIA/data/ips/$address
		if [ -n "$WEB_SYSTEM" ]; then
			assert_file_not_exist /etc/$WEB_SYSTEM/conf.d/$address.conf
		fi
		if [ -n "$PROXY_SYSTEM" ]; then
			assert_file_not_exist /etc/$PROXY_SYSTEM/conf.d/$address.conf
		fi
	done

	run ip -6 addr show dev $interface
	refute_output --partial '2001:db8:6::'

	if [ -f /etc/netplan/60-hestia.yaml ]; then
		run grep -F '2001:db8:6::' /etc/netplan/60-hestia.yaml
		assert_failure
	fi
	run grep -F '2001:db8:6::' /etc/network/interfaces
	assert_failure
}
