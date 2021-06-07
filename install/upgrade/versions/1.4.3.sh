#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.3

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Improve generate and loading firewall rules
if [ "$FIREWALL_SYSTEM" = "iptables" ]; then
    echo "[ * ] Fix the issue of generate firewall rules..."
    $BIN/v-update-firewall
fi
