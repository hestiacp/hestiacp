#!/bin/bash
# info: Add IPv6 support to existing Hestia installation
# This script migrates an existing Hestia installation to support IPv6.

source /etc/profile.d/hestia.sh
source /usr/local/hestia/func/main.sh

# Create firewallv6 data directory and copy default rules
if [ ! -e "$HESTIA/data/firewallv6" ]; then
	mkdir -p "$HESTIA/data/firewallv6"
	chmod 770 "$HESTIA/data/firewallv6"
	cp "$HESTIA/install/common/firewall/firewallv6/rules.conf" "$HESTIA/data/firewallv6/"
	chmod 660 "$HESTIA/data/firewallv6/"*
fi

# Mark existing IPv4 addresses with VERSION='4'
for ip_file in "$HESTIA/data/ips/"*; do
	[ -f "$ip_file" ] || continue
	if ! grep -q "^VERSION=" "$ip_file"; then
		echo "VERSION='4'" >> "$ip_file"
	fi
done

# Detect global IPv6 address and register it
ipv6=$(ip -6 addr show scope global | awk '/inet6/{split($2,a,"/"); print a[1]}' | head -1)
ipv6use=""
if [ -n "$ipv6" ] && [ "$ipv6" != "::1" ]; then
	netmask=$(ip -6 addr show scope global | grep "$ipv6" | awk -F'/' '{print $2}' | awk '{print $1}')
	"$BIN/v-add-sys-ip" "$ipv6" "$netmask"
	"$BIN/v-update-firewall-ipv6"
	ipv6use="$ipv6"
fi

# Update web and DNS configs for all users with IPv6
for user_dir in "$HESTIA/data/users/"*/; do
	user=$(basename "$user_dir")
	USER_DATA="$HESTIA/data/users/$user"

	# Update web domains
	if [ -f "$USER_DATA/web.conf" ]; then
		while IFS= read -r line; do
			eval "$line"
			update_object_value 'web' 'DOMAIN' "$DOMAIN" '$IP6' "$ipv6use"
		done < "$USER_DATA/web.conf"
	fi

	# Update DNS domains
	if [ -f "$USER_DATA/dns.conf" ]; then
		while IFS= read -r line; do
			eval "$line"
			if ! echo "$line" | grep -q 'IP6='; then
				sed -i "s/DOMAIN='$DOMAIN' IP='$IP'/DOMAIN='$DOMAIN' IP='$IP' IP6='$ipv6use'/g" "$USER_DATA/dns.conf"
			else
				update_object_value 'dns' 'DOMAIN' "$DOMAIN" '$IP6' "$ipv6use"
			fi
		done < "$USER_DATA/dns.conf"
	fi

	"$BIN/v-rebuild-user" "$user"
done

"$BIN/v-update-sys-ip-counters"
"$BIN/v-add-user-notification" admin "IPv6 support enabled" "Your Hestia installation now supports IPv6."
