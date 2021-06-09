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

# Reset PMA SSO
if [ "$PHPMYADMIN_KEY" != "" ]; then
    echo "[ * ] Refressh hestia-sso for PMA..."
    $BIN/v-delete-sys-pma-sso 
    $BIN/v-add-sys-pma-sso 
fi