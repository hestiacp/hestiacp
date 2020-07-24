#!/bin/bash

hestia_module_dummy_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'dummy'; then
        echo "Dummy module is already installed. See 'hestia module info dummy'."
        return 1
    fi

    echo "Installing Dummy module..."

    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'description' 'Hestia Dummy module'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'variant' 'crash-test-dummy'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'version' '4'

    return 0
}

# If this files is _not_ being sourced, act immediately
# (otherise, wait for hestia cli to call the main function)
[[ $_ == $0 ]] && hestia_module_dummy_install $@
