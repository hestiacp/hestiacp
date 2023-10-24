#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - Firewall Function Library                          #
#                                                                           #
#===========================================================================#

heal_iptables_links() {
	packages="iptables iptables-save iptables-restore ip6tables ip6tables-save ip6tables-restore"
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
get_iptables_bin() {
	#	get iptables binary
	iptables_par="$1"                                                                                                       # input parameter
	fw_lockingopt="-w"                                                                                                      # Option was introduced to iptables since version 1.4.20 to prevent multiple instances from running concurrently and causing irratic behavior
	[ -z "$iptables_par" ] && iptables_par="iptables"                                                                       #	IPV4 version, if empty or not defined
	[ "$iptables_par" = "iptables" -o "$iptables_par" = "ip6tables" ] && iptables_found_binary="$(which "$iptables_par")"   # find iptables binary in system using which
	[ -n "$iptables_found_binary" -a -n "$fw_lockingopt" ] && iptables_found_binary="$iptables_found_binary $fw_lockingopt" # add locking option as first call parameter to iptables, if not empty
	echo "$iptables_found_binary"                                                                                           # output binary call string to stdout
}
