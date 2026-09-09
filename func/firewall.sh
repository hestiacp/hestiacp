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
#   names. Called by v-add-firewall-chain-ipv6 and v-update-firewall-ipv6.  #
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
