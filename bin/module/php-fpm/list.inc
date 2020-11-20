#!/bin/bash

hestia_module_php-fpm_list() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    if ! hestia_module_isinstalled 'php-fpm' && [ ! "$param_force" ]; then
        echo "PHP (FPM) module is not installed. See 'hestia module info php-fpm'."
        return 1
    fi

    echo "Present PHP (FPM) versions"
    for php_version in $PHP_SUPPORTED_VERSIONS; do
        php_withoutdot=${php_version//.}
        php_version_present=$(osal_kv_read $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" 0)
        echo "PHP ${php_version}: $(osal_bool_tostring $php_version_present 'present' 'not present')"
    done

    return 0
}
