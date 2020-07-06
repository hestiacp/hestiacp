#!/bin/sh

hestia_module_info() {
    source $HESTIA/bin/module/func.inc

    modulename=$1
    echo "Module name     : $modulename"
    
    module_installed=$(hestia_module_isinstalled $modulename)
    if [ "$module_installed" ]; then
        module_enabled=$(hestia_module_isenabled $modulename)
        module_variant=$(hestia_module_getvariant $modulename)
        module_version=$(hestia_module_getversion $modulename)

        echo "Installed       : Yes"
        echo "Enabled         : $module_enabled"
        echo "Variant         : $module_variant"
        echo "Version         : $module_version"
    else
        echo "Installed       : No"
    fi
}

# If this files is _not_ being sourced, act immediately
# (otherise, wait for hestia cli to call the main function)
[[ $_ == $0 ]] && hestia_module_info $@
