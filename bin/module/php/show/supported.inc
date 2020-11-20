#!/bin/bash

hestia_module_php_show_default() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    echo "Supported PHP versions: $PHP_SUPPORTED_VERSIONS"
    return 0
}
