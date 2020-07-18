#!/bin/sh

hestia_module_api_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled api)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "API module is not installed. See 'hestia module info api'."
        exit 1
    fi

    echo "Removing API module..."

    osal_kv_write $HESTIA/conf/hestia.conf 'API' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'version' '0'

    exit 0
}
