#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Update nginx configuration to block connections for unsigned (no SSL certificate) domains
nginx_ver_info="$(sudo nginx -v 2>&1 | awk '{print $3}' | awk -F '/' '{print $2}' | awk -F '.' '{print $2, $3}')"
nginx_ver_b=$(echo $nginx_ver_info | awk '{print $1}') && nginx_ver_c=$(echo $nginx_ver_info | awk '{print $2}')

update_nginx_conf() {
    if [ "$WEB_SYSTEM" = "nginx" ]; then
        echo "[ * ] Hardening nginx SSL SNI configuration..."
        for IP in $(ls $HESTIA/data/ips/ 2>/dev/null); do
            cp -f $HESTIA_INSTALL_DIR/nginx/unassigned.inc /etc/$WEB_SYSTEM/conf.d/$IP.conf
            sed -i "s/directIP/$IP/g" /etc/$WEB_SYSTEM/conf.d/$IP.conf
        done
    elif [ "$PROXY_SYSTEM" = "nginx" ]; then
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
}

if [ $nginx_ver_b -ge 19 ] && [ $nginx_ver_c -ge 4 ]; then
    update_nginx_conf
else
    echo "[ * ] Upgrading nginx to the latest..."
    sudo apt update -qq > /dev/null 2>&1
    sudo apt install -qq nginx -y > /dev/null 2>&1
    update_nginx_conf
fi
