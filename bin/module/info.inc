#!/bin/bash

hestia_module_info() {
    source $HESTIA/bin/module/func.inc

    local modulename=$1
    echo "Module name     : $modulename"
    
    if hestia_module_isinstalled $modulename; then
        local module_variant=$(hestia_module_getvariant $modulename)
        local module_version=$(hestia_module_getversion $modulename)

        echo "Installed       : yes"
        echo "Description     : $(osal_kv_read $HESTIA_CONF_MODULES/${1}.conf 'description')"
        echo "Variant         : $module_variant"
        echo "Version         : $module_version"
    else
        echo "Installed       : no"
    fi
}
