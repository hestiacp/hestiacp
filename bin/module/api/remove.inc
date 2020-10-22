#!/bin/bash

hestia_module_api_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'api' && [ ! "$param_force" ]; then
        echo "API module is not installed. See 'hestia module info api'."
        return 0
    fi

    echo "Removing API module..."

    osal_kv_write $HESTIA/conf/hestia.conf 'API' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'version' '0'

    return 0
}
