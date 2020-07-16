#!/bin/sh

hestia_module_remove() {
    module_name=$1
    shift
    $BIN/hestia module $module_name remove "$@"
}
