#!/bin/bash
# info: enable IPv6 support on an existing Hestia installation
# options: [--assign-domains]
#
# example: add_ipv6.sh
# example: add_ipv6.sh --assign-domains
#
# This script registers the primary IPv6 address of the server as shared
# address, enables the IPv6 firewall with the default rule set and lets
# vsftpd and Bind listen on IPv6. New web domains get the IPv6 address
# automatically. Existing web domains (and their DNS zones) only get the
# IPv6 address if --assign-domains is passed.
#
# Mail stays on IPv4, Exim does not listen on IPv6.

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
# shellcheck source=/etc/hestiacp/hestia.conf
source /etc/hestiacp/hestia.conf
# shellcheck source=/usr/local/hestia/func/main.sh
source $HESTIA/func/main.sh
# load config file
source_conf "$HESTIA/conf/hestia.conf"

assign_domains='no'
if [ "$1" = '--assign-domains' ]; then
	assign_domains='yes'
fi

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

# Detect the primary IPv6 address (no temporary or deprecated addresses).
# Unique local addresses (fc00::/7) are not reachable from the internet and
# are skipped, register one with v-add-sys-ip first to use it anyway.
nic="$(ip -6 -d -j route show | jq -r '.[] | if .dst == "default" then .dev else empty end' | head -n1)"
if [ -z "$nic" ]; then
	nic="$(ip -d -j route show | jq -r '.[] | if .dst == "default" then .dev else empty end' | head -n1)"
fi
ipv6="$(ip -6 -d -j addr show "$nic" | jq -r '.[] | select(length > 0) | .addr_info[] | select(.scope == "global" and .temporary != true and .deprecated != true and (.local | test("^f[cd]") | not)) | .local' | head -n1)"
prefix="$(ip -6 -d -j addr show "$nic" | jq -r --arg IP "$ipv6" '.[].addr_info[] | select(.local == $IP) | .prefixlen' | head -n1)"

# Fall back to an IPv6 address registered before
if [ -z "$ipv6" ]; then
	for ip_file in "$HESTIA"/data/ips/*:*; do
		if [ -f "$ip_file" ]; then
			ipv6="${ip_file##*/}"
			break
		fi
	done
fi

if [ -z "$ipv6" ]; then
	echo "Error: no global IPv6 address found on interface $nic"
	exit 1
fi

failed='no'

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

# Registering the IPv6 address
if [ ! -f "$HESTIA/data/ips/$ipv6" ]; then
	echo "[ * ] Registering IPv6 address $ipv6/${prefix:-64} ($nic)..."
	$BIN/v-add-sys-ip "$ipv6" "${prefix:-64}" "$nic" "$ROOT_USER" 'shared'
	check_result $? "Unable to register IPv6 address $ipv6"
fi

# Enabling the IPv6 firewall with the rules of the installed services
if [ -n "$FIREWALL_SYSTEM" ] && [ ! -f "$HESTIA/data/firewall/rules6.conf" ]; then
	echo "[ * ] Enabling IPv6 firewall..."
	cp "$HESTIA_COMMON_DIR/firewall/rules6.conf" "$HESTIA/data/firewall/rules6.conf"
	chmod 660 "$HESTIA/data/firewall/rules6.conf"
	for comment in FTP SMTP IMAP POP3 DNS; do
		if ! grep -q "COMMENT='$comment'" "$HESTIA/data/firewall/rules.conf"; then
			sed -i "/COMMENT='$comment'/d" "$HESTIA/data/firewall/rules6.conf"
		fi
	done
	$BIN/v-update-firewall
fi

# Listening on IPv6 with vsftpd (dual-stack)
if [ "$FTP_SYSTEM" = 'vsftpd' ] && grep -q '^listen=YES$' /etc/vsftpd.conf 2> /dev/null; then
	echo "[ * ] Enabling IPv6 for vsftpd..."
	sed -i 's/^listen=YES$/listen=NO\nlisten_ipv6=YES/' /etc/vsftpd.conf
	$BIN/v-restart-ftp
	check_result $? "Unable to restart vsftpd"
fi

# Listening on IPv6 with Bind
if [[ "$DNS_SYSTEM" =~ named|bind ]] && [ -f /etc/bind/named.conf.options ] && ! grep -q "listen-on-v6" /etc/bind/named.conf.options; then
	echo "[ * ] Enabling IPv6 for Bind..."
	sed -i 's/^\(\s*\)allow-recursion/\1listen-on-v6 { any; };\n\1allow-recursion/' /etc/bind/named.conf.options
	$BIN/v-restart-dns
	check_result $? "Unable to restart Bind"
fi

# Assigning the IPv6 address to existing web domains and DNS zones
if [ "$assign_domains" = 'yes' ]; then
	for user in $($BIN/v-list-users list); do
		for domain in $($BIN/v-list-web-domains "$user" plain | cut -f 1); do
			domain_ipv6="$($BIN/v-list-web-domain "$user" "$domain" json | jq -r '.[].IP6')"
			if [ -n "$domain_ipv6" ]; then
				continue
			fi
			echo "[ * ] Assigning $ipv6 to $domain ($user)..."
			# The AAAA records are only added if the web domain listens on IPv6
			if ! $BIN/v-change-web-domain-ipv6 "$user" "$domain" "$ipv6" 'no'; then
				echo "[ ! ] Unable to assign $ipv6 to $domain ($user)"
				failed='yes'
				continue
			fi
			if $BIN/v-list-dns-domain "$user" "$domain" plain > /dev/null 2>&1; then
				if ! $BIN/v-change-dns-domain-ipv6 "$user" "$domain" "$ipv6" 'no'; then
					echo "[ ! ] Unable to add the AAAA records of $domain ($user)"
					failed='yes'
				fi
			fi
		done
	done
	$BIN/v-restart-web
	check_result $? "Unable to restart the web server"
	$BIN/v-restart-proxy
	check_result $? "Unable to restart the proxy server"
	if [ -n "$DNS_SYSTEM" ]; then
		$BIN/v-restart-dns
		check_result $? "Unable to restart the DNS server"
	fi
fi

echo "[ * ] IPv6 support enabled ($ipv6)"
if [ "$assign_domains" = 'no' ]; then
	echo "      Existing domains were not changed, run with --assign-domains to add IPv6 to them"
fi
if [ "$failed" = 'yes' ]; then
	echo "[ ! ] Some domains could not be changed, see the errors above"
	exit 1
fi
