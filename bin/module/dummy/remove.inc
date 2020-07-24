#!/bin/bash

hestia_module_dummy_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'dummy'; then
        echo "Dummy module is not installed. See 'hestia module info dummy'."
        return 1
    fi

    echo "Removing Dummy module..."

    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'version' '0'

    return 0
}

# If this files is _not_ being sourced, act immediately
# (otherise, wait for hestia cli to call the main function)
[[ $_ == $0 ]] && hestia_module_dummy_remove $@
