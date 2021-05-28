#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Optimize loading firewall rules
if [ "$FIREWALL_SYSTEM" = "iptables" ]; then
    echo "[ * ] Fix the issue of loading firewall rules..."
    rm -f /usr/lib/networkd-dispatcher/routable.d/50-ifup-hooks /etc/network/if-pre-up.d/iptables
    $BIN/v-update-firewall
fi
