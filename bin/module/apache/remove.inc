#!/bin/bash

hestia_module_apache_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled apache)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "Apache module is not installed. See 'hestia module info apache'."
        exit 1
    fi

    echo "Removing Apache module..."

    osal_service_stop $OSAL_SERVICE_APACHE > /dev/null 2>&1
    osal_service_disable $OSAL_SERVICE_APACHE > /dev/null 2>&1

    hestia_config_backup 'apache-remove' $OSAL_PATH_APACHE_CONF $OSAL_PATH_APACHE_CONF_D

    osal_package_remove $OSAL_PKG_APACHE

    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'version' '0'

    # Setup web module (depending on Nginx and/or Apache config)
    $BIN/hestia module web setup
}
