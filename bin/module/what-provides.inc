#!/bin/bash

# Rerurns the preferred module that provides $1, or nothing
# if nothing provides $1
# i.e. `hestia_module_what-provides 'web'` = 'apache'
# i.e. `hestia_module_what-provides 'exim'` = 'exim'
hestia_module_what-provides() {
    if [ -e $HESTIA_INSTALL_DIR/../modules/$1.hmd ]; then
        # There's a module with that name
        echo $1
        return 0
    fi

    # Search for a module that provides $1
    current_pref=0
    for hmd in $HESTIA_INSTALL_DIR/../modules/*.hmd; do
        local mod_provides=$(osal_kv_read $hmd 'provides')
        if [ "$mod_provides" = "$1" ]; then
            if [ "$param_all" ]; then
                # Return all
                mod_name=$(basename -s '.hmd' "$hmd")
                echo $mod_name
            else
                # Return only the highest preference
                local mod_preference=$(osal_kv_read $hmd 'preference')
                if [ ! "$mod_preference" ]; then mod_preference=0; fi
                if [ $current_pref -eq 0 ] || [ $mod_preference -ge $current_pref ]; then
                    current_pref=$mod_preference
                    mod_name=$(basename -s '.hmd' "$hmd")
                fi
            fi
        fi
    done
    if [ "$mod_name" ]; then
        [ "$param_all" ] || echo $mod_name      # if --all, it was already echoed
        return 0
    else
        return 1
    fi
}