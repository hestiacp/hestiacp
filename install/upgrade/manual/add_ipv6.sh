#!/bin/bash
source /etc/profile.d/hestia.sh
source /usr/local/hestia/func/main.sh

#Download firewallv6 templates
if [ ! -e "$HESTIA/data/firewallv6" ]; then
	mkdir -p $HESTIA/data/firewallv6
	chmod 770 $HESTIA/data/firewallv6

	cp $HESTIA/install/rhel/6/firewallv6/* \
		$HESTIA/data/firewallv6/
	chmod 660 $HESTIA/data/firewallv6/*

fi

#download new templates
if [ -z $0 ]; then
	$BIN/v-update-web-templates
	$BIN/v-update-dns-templates
fi
#testing
#rm -rf /usr/local/hestia/data/templates/*
# cp -rf /usr/local/hestia/install/rhel/7/templates/* /usr/local/hestia/data/templates/es/

#set IPv4 version
iplist=$(ls --sort=time $HESTIA/data/ips/)
for ip in $iplist; do
	echo "VERSION='4'" >> $HESTIA/data/ips/$ip
done

#Add IP6 field
ipv6=$(ip addr show | sed -e's/^.*inet6 \([^ ]*\)\/.*$/\1/;t;d' | grep -ve "^fe80" | tail -1)
ipv6use=""
if [ ! -z "$ipv6" ] && [ "::1" != "$ipv6" ]; then
	netmask="ip addr show | grep '$ipv6' | awk -F '/' '{print \$2}' | awk '{print \$1}'"
	netmask=$(eval $netmask)
	$HESTIA/bin/v-add-sys-ip $ipv6 $netmask
	$BIN/v-update-firewall-ipv6
	ipv6use=$ipv6
fi

#set IPv6
userlist=$(ls --sort=time $HESTIA/data/users/)
for user in $userlist; do
	USER_DATA="$HESTIA/data/users/$user"

	#UPDATE WEB
	conf="$USER_DATA/web.conf"
	while read line; do
		eval $line
		update_object_value 'web' 'DOMAIN' "$DOMAIN" '$IP6' "$ipv6use"
	done < $conf

	#UPDATE DNS
	conf="$USER_DATA/dns.conf"
	while read line; do
		eval $line
		if [ "$(echo $line | grep 'IP6=')" == "" ]; then
			sed -i "s/DOMAIN='$DOMAIN' IP='$IP'/DOMAIN='$DOMAIN' IP='$IP' IP6='$ipv6use'/g" "$conf"
		else
			update_object_value 'dns' 'DOMAIN' "$DOMAIN" '$IP6' "$ipv6use"
		fi
	done < $conf
	$BIN/v-rebuild-user $user
done

$BIN/v-update-sys-ip-counters

$BIN/v-add-user-notification admin "IPv6 support" "Your hestia installation supports IPv6!"
