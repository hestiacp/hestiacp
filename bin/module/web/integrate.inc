#!/bin/bash

hestia_module_web_integrate() {
    source $HESTIA/bin/module/func.inc

    osal_service_stop $OSAL_SERVICE_NGINX > /dev/null 2>&1
    osal_service_stop $OSAL_SERVICE_APACHE > /dev/null 2>&1

    local apache_installed=$(hestia_module_isinstalled apache && echo 1)
    local nginx_installed=$(hestia_module_isinstalled nginx && echo 1)

    if [ "$apache_installed" ] && [ ! "$nginx_installed" ] ; then
        echo "Setup Web module (Apache only)..."
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SYSTEM' $OSAL_PKG_APACHE
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_RGROUPS' $OSAL_USER_APACHE_DATA
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_PORT' '80'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL_PORT' '443'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL' 'mod_ssl'
        osal_kv_write $HESTIA/conf/hestia.conf 'PROXY_SYSTEM' ''
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_PORT'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SSL_PORT'

        osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'service_name' $OSAL_SERVICE_APACHE
    elif [ "$apache_installed" ] && [ "$nginx_installed" ] ; then
        echo "Setup Web module (Apache + Nginx)..."
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SYSTEM' $OSAL_PKG_APACHE
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_RGROUPS' $OSAL_USER_APACHE_DATA
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_PORT' '8080'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL_PORT' '8443'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL' 'mod_ssl'
        osal_kv_write $HESTIA/conf/hestia.conf  'PROXY_SYSTEM' 'nginx'
        osal_kv_write $HESTIA/conf/hestia.conf  'PROXY_PORT' '80'
        osal_kv_write $HESTIA/conf/hestia.conf  'PROXY_SSL_PORT' '443'

        osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'service_name' $OSAL_SERVICE_APACHE
        osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'service_name' $OSAL_SERVICE_NGINX
    elif [ ! "$apache_installed" ] && [ "$nginx_installed" ]; then
        echo "Setup Web module (Nginx only)..."
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SYSTEM' 'nginx'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_RGROUPS'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_PORT' '80'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL_PORT' '443'
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SSL' 'openssl'
        osal_kv_write $HESTIA/conf/hestia.conf 'PROXY_SYSTEM' ''
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_PORT'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SSL_PORT'

        osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'service_name' $OSAL_SERVICE_NGINX
    else
        echo "Remove Web module setup..."
        osal_kv_write $HESTIA/conf/hestia.conf  'WEB_SYSTEM' ''
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_RGROUPS'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_PORT' '80'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_SSL_PORT' '443'
        osal_kv_delete $HESTIA/conf/hestia.conf 'WEB_SSL' 'openssl'
        osal_kv_write $HESTIA/conf/hestia.conf 'PROXY_SYSTEM' ''
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_PORT'
        osal_kv_delete $HESTIA/conf/hestia.conf 'PROXY_SSL_PORT'
    fi

    if [ "$apache_installed" ] || [ "$nginx_installed" ] ; then
        hestia module web setup-ips
        # Rebuild domains
        echo "Rebuilding web domains"
        for user in $($HESTIA/bin/v-list-sys-users plain); do
            $BIN/v-rebuild-web-domains $user 'no' > /dev/null 2>&1
        done

        if [ "$apache_installed" ]; then
            osal_service_enable $OSAL_SERVICE_APACHE
            osal_service_start $OSAL_SERVICE_APACHE
            check_result $? "Apache start failed"
        fi

        if [ "$nginx_installed" ]; then
            osal_service_enable $OSAL_SERVICE_NGINX
            osal_service_start $OSAL_SERVICE_NGINX
            check_result $? "Nginx start failed"
        fi
    fi
}
