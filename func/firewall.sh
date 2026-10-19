#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - Firewall Function Library                          #
#                                                                           #
# IPv6 additions (non-invasive):                                            #
#   heal_ip6tables_links() is an exact mirror of heal_iptables_links() for  #
#   the ip6tables suite. It ensures /sbin/ip6tables, /sbin/ip6tables-save,  #
#   and /sbin/ip6tables-restore symlinks exist before any firewall script   #
#   attempts to call them — identical self-healing logic, different binary   #
#   names. Called by v-update-firewall-ipv6 and v-stop-firewall-ipv6.      #
#                                                                           #
#   The rest of this file (iptables chain management, ipset helpers, etc.)  #
#   is unchanged from upstream HestiaCP.                                    #
#                                                                           #
#===========================================================================#

heal_iptables_links() {
	packages="iptables iptables-save iptables-restore"
	for package in $packages; do
		if [ ! -e "/sbin/${package}" ]; then
			if which ${package}; then
				ln -s "$(which ${package})" /sbin/${package}
			elif [ -e "/usr/sbin/${package}" ]; then
				ln -s /usr/sbin/${package} /sbin/${package}
			elif whereis -B /bin /sbin /usr/bin /usr/sbin -f -b ${package}; then
				autoiptables=$(whereis -B /bin /sbin /usr/bin /usr/sbin -f -b ${package} | cut -d '' -f 2)
				if [ -x "$autoiptables" ]; then
					ln -s "$autoiptables" /sbin/${package}
				fi
			fi
		fi
	done
}

# IPv6 mirror of heal_iptables_links — ensures ip6tables suite symlinks exist.
# Must be called before any script uses /sbin/ip6tables directly.
heal_ip6tables_links() {
	packages="ip6tables ip6tables-save ip6tables-restore"
	for package in $packages; do
		if [ ! -e "/sbin/${package}" ]; then
			if which ${package}; then
				ln -s "$(which ${package})" /sbin/${package}
			elif [ -e "/usr/sbin/${package}" ]; then
				ln -s /usr/sbin/${package} /sbin/${package}
			elif whereis -B /bin /sbin /usr/bin /usr/sbin -f -b ${package}; then
				autoiptables=$(whereis -B /bin /sbin /usr/bin /usr/sbin -f -b ${package} | cut -d '' -f 2)
				if [ -x "$autoiptables" ]; then
					ln -s "$autoiptables" /sbin/${package}
				fi
			fi
		fi
	done
}

# Networks blocked for banned IPv6 addresses: reads "CHAIN ADDRESS" lines and
# prints "CHAIN NETWORK". A single host usually owns a complete /64, so a ban
# covers the whole network (FIREWALL_IPV6_BAN_PREFIX, default 64) unless the
# network contains an address of this server or an excluded address.
get_ipv6_ban_networks() {
	local prefix="${FIREWALL_IPV6_BAN_PREFIX:-64}"
	local protected
	if ! [[ "$prefix" =~ ^[0-9]+$ ]] || [ "$prefix" -lt 1 ] || [ "$prefix" -gt 128 ]; then
		prefix=64
	fi
	protected="$(
		ls $HESTIA/data/ips/ | grep ':'
		grep ':' $HESTIA/data/firewall/excludes.conf 2> /dev/null
	)"
	# shellcheck disable=SC2086
	$HESTIA_PHP -r '
		$prefix = (int) $argv[1];
		$mask = str_repeat("\xff", intdiv($prefix, 8));
		if ($prefix % 8) {
			$mask .= chr((0xff << (8 - ($prefix % 8))) & 0xff);
		}
		$mask = str_pad($mask, 16, "\x00");
		$protected = [];
		foreach (array_slice($argv, 2) as $address) {
			$binary = @inet_pton(explode("/", $address)[0]);
			if ($binary !== false && strlen($binary) == 16) {
				$protected[] = $binary & $mask;
			}
		}
		while (($line = fgets(STDIN)) !== false) {
			[$chain, $address] = array_pad(preg_split("/\s+/", trim($line)), 2, "");
			$binary = @inet_pton($address);
			if ($prefix == 128 || $binary === false || strlen($binary) != 16 || in_array($binary & $mask, $protected, true)) {
				echo "$chain $address\n";
			} else {
				echo $chain . " " . inet_ntop($binary & $mask) . "/" . $prefix . "\n";
			}
		}
	' "$prefix" $protected
}

# Delete the ip6tables rule of an IPv6 ban (after the ban was removed from the
# banlist) unless another banned address of the chain is in the same network
delete_ipv6_ban_rule() {
	local chain="$1" address="$2" network remaining
	network=$(echo "$chain $address" | get_ipv6_ban_networks | cut -d ' ' -f 2)
	remaining=$(sed -n "s/^IP='\([^']*:[^']*\)' CHAIN='$chain'.*/$chain \1/p" "$HESTIA/data/firewall/banlist.conf" 2> /dev/null \
		| get_ipv6_ban_networks | grep -cxF "$chain $network")
	if [ "$remaining" = "0" ] && [ -n "$network" ]; then
		/sbin/ip6tables -D "fail2ban-$chain" -s "$network" -j DROP 2> /dev/null
	fi
}
