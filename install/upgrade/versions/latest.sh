#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.1.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ -e "/etc/apache2/mods-enabled/status.conf" ]; then
    echo "(*) Disable Apache2 Server Status Module..."
    a2dismod status > /dev/null 2>&1
fi