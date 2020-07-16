#!/bin/bash

hestia_module_install() {
    module_name=$1
    shift
    $BIN/hestia module $module_name install "$@"
}
