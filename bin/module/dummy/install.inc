#!/bin/sh

hestia_module_dummy_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled dummy)
    if [ "$module_installed" ]; then
        echo "Dummy module is already installed. See 'hestia module info dummy'."
        exit 1
    fi

    echo "Installing Dummy module..."

    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'description' 'Hestia Dummy module'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'variant' 'crash-test-dummy'
    osal_kv_write $HESTIA_CONF_MODULES/dummy.conf 'version' '4'

    exit 0
}

# If this files is _not_ being sourced, act immediately
# (otherise, wait for hestia cli to call the main function)
[[ $_ == $0 ]] && hestia_module_dummy_install $@
