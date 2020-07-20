#!/bin/sh

hestia_module_list() {
    source $HESTIA/bin/module/func.inc

    for conf_file in $HESTIA_CONF_MODULES/*.conf; do
        local mod_name=$(basename $conf_file .conf)
        local mod_descr=$(osal_kv_read ${conf_file} 'description')

        if hestia_module_isinstalled $mod_name; then
            echo "$mod_name: $mod_descr"
        fi
    done
}