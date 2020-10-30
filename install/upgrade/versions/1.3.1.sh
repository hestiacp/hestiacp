#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Update nginx configuration to block connections for unsigned (no SSL certificate) domains
for ipaddr in $(ls /usr/local/hestia/data/ips/ 2>/dev/null); do
    web_conf="/etc/$WEB_SYSTEM/conf.d/$ipaddr.conf"

    if [ "$WEB_SYSTEM" = "nginx" ]; then
        cp -f $HESTIA_INSTALL_DIR/nginx/unassigned.inc $web_conf
        sed -i 's/directIP/'$ipaddr'/g' $web_conf
    fi

    if [ "$PROXY_SYSTEM" = "nginx" ]; then
        echo "[ * ] Adding unassigned hosts configuration to Nginx..."
        cat $WEBTPL/$PROXY_SYSTEM/proxy_ip.tpl |\
        sed -e "s/%ip%/$ipaddr/g" \
            -e "s/%web_port%/$WEB_PORT/g" \
            -e "s/%proxy_port%/$PROXY_PORT/g" \
        > /etc/$PROXY_SYSTEM/conf.d/$ipaddr.conf
    fi
done