#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Update nginx configuration to block connections for unsigned (no SSL certificate) domains
for ipaddr in $(ls /usr/local/hestia/data/ips/ 2>/dev/null); do
    web_conf="/etc/$PROXY_SYSTEM/conf.d/$ipaddr.conf"

    if [ "$PROXY_SYSTEM" = "nginx" ]; then
        echo "[ * ] Hardening nginx SSL SNI configuration..."
        cp -f $HESTIA_INSTALL_DIR/nginx/unassigned.inc $web_conf
        sed -i 's/directIP/'$ipaddr'/g' $web_conf
    fi
done