#!/bin/bash

hestia_module_list() {
    source $HESTIA/bin/module/func.inc

    printf "%-16s %-12s %-4s %s\n" "Module" "Provides" "Inst" "Description"
    for hmd in $HESTIA_INSTALL_DIR/../modules/*.hmd; do
        local mod_name=$(osal_kv_read $hmd 'name')
        local mod_provides=$(osal_kv_read $hmd 'provides')
        local mod_descr=$(osal_kv_read $hmd 'description')

        local mod_varinstalled=$(hestia_module_variant_installed $mod_provides)
        if [ "$mod_varinstalled" == "$mod_name" ]; then
            local mod_installed='Yes'
        else
            local mod_installed='No'
        fi

        printf "%-16s %-12s %-4s %s\n" "$mod_name" "$mod_provides" "$mod_installed" "$mod_descr"
    done
}