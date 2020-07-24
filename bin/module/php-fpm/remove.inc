#!/bin/bash

hestia_module_php-fpm_remove() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php-fpm/func.inc

    if ! hestia_module_isinstalled 'php-fpm' && [ ! "$param_force" ]; then
        echo "PHP (FPM) module is not installed. See 'hestia module info php-fpm'."
        return 1
    fi

    echo "Removing PHP (FPM) module..."

    # Remove all added versions
    # FIXME: this will not remove unsupported but installed PHP versions
    # (i.e. supported by an older version of Hestia)
    for php_version in $PHP_SUPPORTED_VERSIONS; do
        php_withoutdot=${php_version//.}
        php_version_present=$(osal_kv_read $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" '')
        if [ "$php_version_present" ]; then
            hestia module php-fpm del $php_version
        fi
    done

    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_BACKEND' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/php-fpm.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/php-fpm.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/php-fpm.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/php-fpm.conf 'version' '0'

    return 0
}
