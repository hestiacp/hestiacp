#!/bin/sh

hestia_module_list() {
    source $HESTIA/bin/module/func.inc

    for conf_file in $HESTIA_CONF_MODULES/*.conf; do
        mod_name=$(basename $conf_file .conf)
        mod_descr=$(osal_kv_read ${conf_file} 'description')

        module_installed=$(hestia_module_isinstalled $mod_name)
        module_enabled=$(hestia_module_isenabled $mod_name)

        if [ "$module_installed" ]; then
            if [ "$module_enabled" ] || [ "$param_all" ]; then
                echo "$mod_name: $mod_descr"
            fi
        fi
    done
}