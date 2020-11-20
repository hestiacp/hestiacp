#!/bin/bash

hestia_module_php-fpm_remove() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    if ! hestia_module_isinstalled 'php-fpm' && [ ! "$param_force" ]; then
        echo "PHP (FPM) module is not installed. See 'hestia module info php-fpm'."
        return 0
    fi

    for php_version in $PHP_SUPPORTED_VERSIONS; do
        php_withoutdot=${php_version//.}
        php_version_present=$(osal_kv_read $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" 0)
        if [ "php_version_present" ]; then
            hestia module php-fpm del $php_version
        fi
    done

    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_BACKEND' 'no'

    return 0
}
