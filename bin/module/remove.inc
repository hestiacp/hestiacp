#!/bin/sh

hestia_module_remove() {
    if [ "$1" ]; then
        local mod_name=$1
        shift

        $BIN/hestia module $mod_name remove "$@"
    else
        echo "Usage: module remove module_name"
        return 0
    fi
}
