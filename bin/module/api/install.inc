#!/bin/sh

hestia_module_api_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled api)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "API module is already installed. See 'hestia module info api'."
        exit 1
    fi

    echo "Installing API module..."

    osal_kv_write $HESTIA/conf/hestia.conf 'API' 'yes'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'description' 'Hestia API module'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'variant' 'api'
    osal_kv_write $HESTIA_CONF_MODULES/api.conf 'version' '1'

    exit 0
}
