#!/bin/sh

hestia_module_dummy_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled dummy)
    if [ ! "$module_installed" ]; then
        echo "Dummy module is not installed. See 'hestia module info dummy'."
        exit 1
    fi

    echo "Removing Dummy module..."

    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'version' '0'

    exit 0
}

# If this files is _not_ being sourced, act immediately
# (otherise, wait for hestia cli to call the main function)
[[ $_ == $0 ]] && hestia_module_dummy_remove $@
