#!/bin/bash

hestia_module_apache_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'apache' && [ ! "$param_force" ]; then
        echo "Apache module is not installed. See 'hestia module info apache'."
        return 0
    fi

    echo "Removing Apache module..."

    osal_service_stop $OSAL_SERVICE_APACHE > /dev/null 2>&1
    osal_service_disable $OSAL_SERVICE_APACHE > /dev/null 2>&1

    hestia_config_backup 'apache-remove' $OSAL_PATH_APACHE_CONF $OSAL_PATH_APACHE_CONF_D

    osal_package_remove $OSAL_PKG_APACHE

    if [ "$OS_BASE" = 'debian' ]; then  
        hestia_safe_rm $OSAL_PATH_APACHE_CONF/mods-available/hestia-status.conf
        hestia_safe_rm $OSAL_PATH_APACHE_CONF/mods-available/hestia-status.load
    elif [ "$OS_BASE" = 'rhel' ]; then
        hestia_safe_rm  /etc/httpd/mods-enabled
    fi

    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'version' '0'
}
