#!/bin/bash

hestia_module_install() {
    if [ "$1" ] && [ "$1" != 'install' ] && [ "$1" != 'remove' ]; then
        module_name=$1
        shift
        $BIN/hestia module $module_name install "$@"
    else
        echo "Usage: module install module_name"
        return 0
    fi
}
