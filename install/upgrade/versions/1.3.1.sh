#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Update nginx configuration to block connections for unsigned (no SSL certificate) domains
if [ "$WEB_SYSTEM" = "nginx" ]; then
    echo "[ * ] Hardening nginx SSL SNI configuration..."
    for IP in $(ls $HESTIA/data/ips/ 2>/dev/null); do
        cp -f $HESTIA_INSTALL_DIR/nginx/unassigned.inc /etc/$WEB_SYSTEM/conf.d/$IP.conf
        sed -i "s/directIP/$IP/g" /etc/$WEB_SYSTEM/conf.d/$IP.conf
    done
fi

if [ "$PROXY_SYSTEM" = "nginx" ]; then
    echo "[ * ] Hardening nginx SSL SNI configuration..."
    for IP in $(ls $HESTIA/data/ips/ 2>/dev/null); do
        rm -f /etc/$PROXY_SYSTEM/conf.d/$IP.conf
        cat $WEBTPL/nginx/proxy_ip.tpl |\
        sed -e "s/%ip%/$IP/g" \
            -e "s/%web_port%/$WEB_PORT/g" \
            -e "s/%proxy_port%/$PROXY_PORT/g" \
            -e "s/%proxy_ssl_port%/$PROXY_SSL_PORT/g" \
        > /etc/$PROXY_SYSTEM/conf.d/$IP.conf
    done
fi
