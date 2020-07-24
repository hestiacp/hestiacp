#!/bin/bash

hestia_module_nginx_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'nginx' && [ ! "$param_force" ]; then
        echo "Nginx module is not installed. See 'hestia module info nginx'."
        return 1
    fi

    echo "Removing Nginx module..."

    osal_service_stop $OSAL_SERVICE_NGINX > /dev/null 2>&1
    osal_service_disable $OSAL_SERVICE_NGINX > /dev/null 2>&1

    hestia_config_backup 'nginx-remove' $OSAL_PATH_NGINX_CONF

    osal_package_remove $OSAL_PKG_NGINX

    osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'version' '0'
}
