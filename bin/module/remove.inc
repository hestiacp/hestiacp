#!/bin/bash

hestia_module_remove() {
    source $HESTIA/bin/module/func.inc

    if [ "$1" ]; then
        local mod_name=$1
        shift

        local mod_providers=$(hestia module what-provides $mod_name --all)
        #echo "$mod_providers" equals "$mod_name"
        if [ "$mod_providers" == "$mod_name" ]; then
            # It's a final module name, remove immediately
            local hmd="$HESTIA_INSTALL_DIR/../modules/${mod_provider}.hmd"
            local requiredby=$(hestia_module_remove_check_requiredby $mod_name)
            if [ "$requiredby" ]; then
                if [ "$param_force" ]; then
                    for mod_required in $requiredby; do
                        hestia module remove $mod_required --force

                        local mod_provides=$(osal_kv_read $hmd 'provides')
                        if [ "$mod_provides" ]; then
                            # Write what this module provides
                            for mod in $mod_provides; do
                                local current_variant=$(hestia_module_variant_installed $mod)
                                current_variant=$(echo "$current_variant" | sed "s/$mod_provider//")

                                if [ ! "$current_variant" ]; then
                                    osal_kv_write $HESTIA_CONF_MODULES/$mod.conf 'installed' 0
                                fi
                                osal_kv_write $HESTIA_CONF_MODULES/$mod.conf $mod_provider 'no'
                                osal_kv_write $HESTIA_CONF_MODULES/$mod.conf 'variant' $current_variant
                            done
                        fi
                    done
                else
                    echo "Module '${mod_name}' is required by '${requiredby}'. Use --force to remove recursively."
                    return 1
                fi
            else
                hestia module $mod_name remove "$@"
                osal_enqueue_integrate $mod_name
            fi
        else
            # It's provided by something else
            for mod in $mod_providers; do
                if hestia_module_isinstalled $mod; then
                    echo "Ok, 'remove ${mod_name}' means 'remove ${mod}'"
                    if ! hestia module remove $mod; then
                        echo "Unable to remove '${mod_name}' because '${mod}' refuses to uninstall."
                    fi
                fi
            done

            osal_enqueue_integrate $mod_name
            for mod in $mod_providers; do
                osal_enqueue_integrate $mod
            done
        fi
    else
        echo "Usage: module remove module_name"
        return 0
    fi
}

# Check whether a module is required by other installed modules
hestia_module_remove_check_requiredby() {
    local mod_name=$1
    shift

    local requiredby=''
    for mod_conf in $HESTIA_INSTALL_DIR/../modules/*.conf; do
        local mod_hmd=$(osal_kv_read $mod_conf 'hmd')
        if [ "$mod_hmd" ]; then
            # This module is a provider. Check it isn't needed
            local mod_requires=$(osal_kv_read $mod_hmd 'requires')
            local mod_requires_expanded=$(hestia_module_remove_expand_requires $mod_requires)
            #echo >&2 "uuu $mod_requires expands to $mod_requires_expanded uuu"
            if osal_value_in_list $mod_name $mod_requires_expanded; then
                local mod_requires_name=$(osal_kv_read $mod_hmd 'name')
                #echo >&2 "$mod_name is in the required list of $mod_requires_name (which is $mod_requires_expanded)"
                requiredby="$mod_requires_name $requiredby"
            #else
                #echo >&2 "$mod_name is NOT in the required list (which is $mod_requires_expanded)"
            fi
        fi
    done

    if [ "$requiredby" ]; then
        echo $requiredby
        return 1
    else
        return 0
    fi
}

# Expands 'web' to 'web apache nginx'
hestia_module_remove_expand_requires() {
    local expanded=$@
    for mod in $@; do
        local extra_requires=$(hestia module what-provides $mod --all)
        for er in $extra_requires; do
            if ! osal_value_in_list $er $expanded; then
                # FIXME: allow recursive expansion?
                expanded="$expanded $er"
            fi
        done
    done

    echo $expanded
}