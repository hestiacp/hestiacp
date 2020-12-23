#!/bin/bash

hestia_module_install() {
    source $HESTIA/bin/module/func.inc
    
    if [ "$1" ]; then
        local mod_name=$1
        shift

        local mod_provider=$(hestia module what-provides $mod_name)
        if [ "$mod_provider" != "$mod_name" ]; then
            echo "Module '${mod_provider}' selected as provider of '${mod_name}'"
        fi

        local hmd="$HESTIA_INSTALL_DIR/../modules/${mod_provider}.hmd"

        local mod_conflicts=$(osal_kv_read $hmd 'conflicts')
        if [ "$mod_conflicts" ] && ! hestia_module_install_check_conflicts "$mod_provider" "$mod_conflicts"; then
            return 1
        fi

        local mod_requires=$(osal_kv_read $hmd 'requires')
        if [ "$mod_requires" ] && ! hestia_module_install_check_requires "$mod_provider" "$mod_requires"; then
            return 1
        fi

        if ! hestia module "$mod_provider" install "$@"; then
            echo "$mod_provider module installation failed"
            if [ ! "$param_force" ]; then
                exit 1
            fi
        fi

        # Write installed module info
        osal_kv_write $HESTIA_CONF_MODULES/$mod_provider.conf 'installed' '1'
        osal_kv_write $HESTIA_CONF_MODULES/$mod_provider.conf 'hmd' "$hmd"
        osal_kv_write $HESTIA_CONF_MODULES/$mod_provider.conf 'isprovider' "$hmd"

        local mod_provides=$(osal_kv_read $hmd 'provides')
        if [ "$mod_provides" ]; then
            # Write what this module provides
            for mod in $mod_provides; do
                local current_variant=$(hestia_module_variant_installed $mod)
                current_variant="$mod_provider $current_variant"

                osal_kv_write $HESTIA_CONF_MODULES/$mod.conf 'installed' 1
                osal_kv_write $HESTIA_CONF_MODULES/$mod.conf $mod_provider 'yes'
                osal_kv_write $HESTIA_CONF_MODULES/$mod.conf 'variant' $current_variant
            done
        fi

        osal_enqueue_integrate $mod
    else
        echo "Usage: module install module_name"
        return 1
    fi
}

# Check whether conflicting modules are installed,
# returns 1 if a conflict is found.
hestia_module_install_check_conflicts() {
    local mod_name=$1
    shift

    for mod in "$@"; do
        if hestia_module_isinstalled $mod; then
            echo "'$mod_name' conflicts with an installed module. Please remove '$mod' first."
            return 1
        fi
    done
    return 0
}

# Check whether required modules are installed and installs
# them if necessary, returns 1 if a requirements can't be installed.
hestia_module_install_check_requires() {
    local mod_name=$1
    shift

    for mod in $@; do
        [ "$HESTIA_DEBUG" ] && echo "Check dependency: $mod"
        if ! hestia_module_isinstalled $mod; then
            if ! hestia module install $mod; then
                echo "Module '${mod}' is required for module '${mod_name}' but can not be installed"
                return 1
            fi
        fi
    done

    return 0
}
