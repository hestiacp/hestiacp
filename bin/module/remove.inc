#!/bin/sh

hestia_module_remove() {
    if [ "$1" ] && [ "$1" != 'install' ] && [ "$1" != 'remove' ]; then
        module_name=$1
        shift
        $BIN/hestia module $module_name remove "$@"
    else
        echo "Usage: module install module_name"
        return 0
    fi
}
