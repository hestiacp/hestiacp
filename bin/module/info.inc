#!/bin/bash

hestia_module_info() {
    source $HESTIA/bin/module/func.inc

    local modulename=$1
    if [ -z "$modulename" ]; then
        usage
        return 1
    fi
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

usage() {
    echo "Usage:"
    echo "  hestia module info MODULE_NAME"
    echo ""
    echo "See 'hestia module list' for a list of installed and available modules."
}