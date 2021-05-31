#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Optimize loading firewall rules
if [ "$FIREWALL_SYSTEM" = "iptables" ]; then
    echo "[ * ] Fix the issue of loading firewall rules..."
    # Add rule to ensure the rule will be added when we update the firewall / /etc/iptables.rules
    iptables -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT
    rm -f /usr/lib/networkd-dispatcher/routable.d/50-ifup-hooks /etc/network/if-pre-up.d/iptables
    $BIN/v-update-firewall
fi
