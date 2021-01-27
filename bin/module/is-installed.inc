#!/bin/bash

hestia_module_is-installed() {
    source $HESTIA/bin/module/func.inc
    
    if [ "$1" ]; then
        local mod_name=$1
        shift

        if hestia_module_isinstalled $mod_name; then
            echo true
            return 0
        else
            echo false
            return 1
        fi
    else
        echo "Usage: module is-installed MODULE_NAME"
        return 1
    fi
}
