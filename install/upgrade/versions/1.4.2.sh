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

# Fix potential issue of updating to Nginx 1.21.0
if [ "WEB_SYSTEM" = "nginx" ] || [ "PROXY_SYSTEM" = "nginx" ]; then
    default_conf="/etc/nginx/conf.d/default.conf"
    nginx_conf="/etc/nginx/nginx.conf"

    [ -f "${default_conf}" ]          && mv -f ${default_conf} ${default_conf}.dpkg-dist
    [ -f "${default_conf}.dpkg-new" ] && mv -f ${default_conf}.dpkg-new ${default_conf}.dpkg-dist
    [ -f "${nginx_conf}.dpkg-new" ]   && mv -f ${nginx_conf}.dpkg-new ${nginx_conf}.dpkg-dist
    [ -f "${nginx_conf}.dpkg-old" ]   && mv -f ${nginx_conf} ${nginx_conf}.dpkg-dist \
                                      && rm -f ${nginx_conf}.dpkg-old \
                                      && cp -f $HESTIA/install/deb/nginx/nginx.conf /etc/nginx/
fi
