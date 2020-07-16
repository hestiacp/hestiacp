#!/bin/bash

hestia_module_web_setup() {
    source $HESTIA/bin/module/func.inc

    apache_installed=$(hestia_module_isinstalled apache)
    nginx_installed=$(hestia_module_isinstalled nginx)

    if [ "$apache_installed" ] && [ ! "$nginx_installed" ] ; then
        echo "Setup Web module (Apache only)..."
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SYSTEM' 'httpd'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_RGROUPS' $OSAL_USER_APACHE_DATA
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_PORT' '80'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL_PORT' '443'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL' 'mod_ssl'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SYSTEM'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_PORT'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SSL_PORT'

        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'installed' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'enabled' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'variant' 'apache2'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'version' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'service_name' $OSAL_SERVICE_APACHE
    elif [ "$apache_installed" ] && [ "$nginx_installed" ] ; then
        echo "Setup Web module (Apache + Nginx)..."
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SYSTEM' 'httpd'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_RGROUPS' $OSAL_USER_APACHE_DATA
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_PORT' '8080'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL_PORT' '8443'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL' 'mod_ssl'
        osal_kv_write $HESTIA/conf/hestia.conf  'PROXY_SYSTEM' 'nginx'
        osal_kv_write $HESTIA/conf/hestia.conf  'PROXY_PORT' '80'
        osal_kv_write $HESTIA/conf/hestia.conf  'PROXY_SSL_PORT' '443'

        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'installed' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'enabled' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'variant' 'apache2+nginx'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'version' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'service_name' "$OSAL_SERVICE_APACHE $OSAL_SERVICE_NGINX"
    elif [ ! "$apache_installed" ] && [ "$nginx_installed" ]; then
        echo "Setup Web module (Nginx only)..."
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SYSTEM' 'nginx'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_RGROUPS'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_PORT' '80'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL_PORT' '443'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL' 'openssl'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SYSTEM'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_PORT'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SSL_PORT'

        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'installed' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'enabled' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'variant' 'nginx'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'version' '1'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'service_name' $OSAL_SERVICE_NGINX
    else
        echo "Remove Web module setup..."
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SYSTEM' 'no'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_RGROUPS'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_PORT' '80'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_SSL_PORT' '443'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_SSL' 'openssl'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SYSTEM'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_PORT'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SSL_PORT'

        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'installed' '0'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'enabled' '0'
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'variant' ''
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'version' ''
        osal_kv_write $HESTIA_CONF_MODULES/web.conf 'service_name' ''
    fi

    osal_kv_write $HESTIA_CONF_MODULES/web.conf 'description' 'Hestia Web module'

    if [ "$OS_BASE" = 'debian' ]; then
        # Configuring libapache2-mod-remoteip
        if [ "$apache_installed" = 'yes' ] && [ "$nginx_installed"  = 'yes' ] ; then
            # FIXME: RHEL version
            cd /etc/apache2/mods-available
            echo "<IfModule mod_remoteip.c>" > remoteip.conf
            echo "  RemoteIPHeader X-Real-IP" >> remoteip.conf
            if [ "$local_ip" != "127.0.0.1" ] && [ "$pub_ip" != "127.0.0.1" ]; then
                echo "  RemoteIPInternalProxy 127.0.0.1" >> remoteip.conf
            fi
            if [ ! -z "$local_ip" ] && [ "$local_ip" != "$pub_ip" ]; then
                echo "  RemoteIPInternalProxy $local_ip" >> remoteip.conf
            fi
            if [ ! -z "$pub_ip" ]; then
                echo "  RemoteIPInternalProxy $pub_ip" >> remoteip.conf
            fi
            echo "</IfModule>" >> remoteip.conf
            sed -i "s/LogFormat \"%h/LogFormat \"%a/g" /etc/apache2/apache2.conf
            a2enmod remoteip >> $LOG
            systemctl restart apache2
        fi
    fi

    # FIXME: setup PHP support
    php_variant=$(hestia_module_variant_installed php)
    if [ "$apache_installed" ]; then
        # Enable mod_ruid/mpm_itk or mpm_event
        if [ "$php_variant" = 'php-fpm' ]; then
            # Disable prefork and php, enable event
            osal_apache_module_disable php$fpm_v > /dev/null 2>&1
            osal_apache_module_disable mpm_prefork > /dev/null 2>&1
            osal_apache_module_enable mpm_event > /dev/null 2>&1
            cp -f $HESTIA_INSTALL_DIR/apache2/hestia-event.conf /etc/apache2/conf.d/
        else
            osal_apache_module_enable ruid2 > /dev/null 2>&1
        fi
    fi

    # FIXME: move the following to awstats install
    osal_kv_write $HESTIA/conf/hestia.conf 'STATS_SYSTEM' 'awstats'

    # Rebuild mail
    for user in $($HESTIA/bin/v-list-sys-users plain); do
        echo "Rebuilding web domains for user $user"
        $BIN/v-rebuild-web-domains $user 'no' > /dev/null 2>&1
    done

    osal_service_stop $OSAL_SERVICE_NGINX > /dev/null 2>&1
    osal_service_stop $OSAL_SERVICE_APACHE > /dev/null 2>&1

    if [ "$apache_installed" ]; then
        osal_service_enable $OSAL_SERVICE_APACHE
        osal_service_start $OSAL_SERVICE_APACHE
    fi

    if [ "$nginx_installed" ]; then
        osal_service_enable $OSAL_SERVICE_NGINX
        osal_service_start $OSAL_SERVICE_NGINX
    fi
    check_result $? "Nginx start failed"

}
