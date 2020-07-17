#!/bin/bash

hestia_module_web_setup-ip() {
    source $HESTIA/bin/module/func.inc

    ip=$1

    echo "Configuring web for IP address $ip"

    # Web support
    if [ ! -z "$WEB_SYSTEM" ]; then
        web_conf="/etc/$WEB_SYSTEM/conf.d/$ip.conf"
        rm -f $web_conf

        if [ "$WEB_SYSTEM" = 'httpd' ] || [ "$WEB_SYSTEM" = 'apache2' ]; then
            if [ -z "$(/usr/sbin/apachectl -v | grep Apache/2.4)" ]; then
                # FIXME: this will break with Apache > 2.4
                echo "NameVirtualHost $ip:$WEB_PORT" >  $web_conf
            fi
            echo "Listen $ip:$WEB_PORT" >> $web_conf
            cat $HESTIA_INSTALL_DIR/$WEB_SYSTEM/unassigned.conf >> $web_conf
            sed -i 's/directIP/'$ip'/g' $web_conf
            sed -i 's/directPORT/'$WEB_PORT'/g' $web_conf
        elif [ "$WEB_SYSTEM" = 'nginx' ]; then
            cp -f $HESTIA_INSTALL_DIR/nginx/unassigned.inc $web_conf
            sed -i 's/directIP/'$ip'/g' $web_conf
        fi

        if [ "$WEB_SSL" = 'mod_ssl' ]; then
            if [ -z "$(/usr/sbin/apachectl -v | grep Apache/2.4)" ]; then
                # FIXME: this will break with Apache > 2.4
                sed -i "1s/^/NameVirtualHost $ip:$WEB_SSL_PORT\n/" $web_conf
            fi
            sed -i "1s/^/Listen $ip:$WEB_SSL_PORT\n/" $web_conf
            sed -i 's/directSSLPORT/'$WEB_SSL_PORT'/g' $web_conf
        fi
    fi

    # Proxy support
    if [ ! -z "$PROXY_SYSTEM" ]; then
        cat $WEBTPL/$PROXY_SYSTEM/proxy_ip.tpl |\
            sed -e "s/%ip%/$ip/g" \
                -e "s/%web_port%/$WEB_PORT/g" \
                -e "s/%proxy_port%/$PROXY_PORT/g" \
            > /etc/$PROXY_SYSTEM/conf.d/$ip.conf

        # mod_extract_forwarded
        fw_conf="$OSAL_PATH_APACHE_CONF_D/mod_extract_forwarded.conf"
        if [ -e "$fw_conf" ]; then
            ips=$(grep 'MEFaccept ' $fw_conf | grep -v '#' | head -n1)
            sed -i "s/$ips/$ips $ip/g" $fw_conf
        fi

        # mod_rpaf
        rpaf_conf="$OSAL_PATH_APACHE_MODS_ENABLED/rpaf.conf"
        if [ -e "$rpaf_conf" ]; then
            rpaf_str=$(grep RPAFproxy_ips $rpaf_conf)
            [ -z "$rpaf_str" ] && sed -i 's|</IfModule>|RPAFproxy_ips\n</IfModule>|' $rpaf_conf && rpaf_str='RPAFproxy_ips'
            rpaf_str="$rpaf_str $ip"
            sed -i "s/.*RPAFproxy_ips.*/$rpaf_str/" $rpaf_conf
        fi

        #mod_remoteip
        remoteip_conf="$OSAL_PATH_APACHE_MODS_ENABLED/remoteip.conf"
        if [ -e "$remoteip_conf" ]; then
            if [ $( grep -ic "$ip" $remoteip_conf ) -eq 0 ]; then
                sed -i "s/<\/IfModule>/RemoteIPInternalProxy $ip\n<\/IfModule>/g" $remoteip_conf
            fi
        fi
    fi
}
