#!/bin/bash

hestia_module_php_show_default() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    echo "Default PHP version: $PHP_DEFAULT_VERSION"
    return 0
}
