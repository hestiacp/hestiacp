#!/bin/sh

hestia_module_php_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled php)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "PHP module is not installed. See 'hestia module info php'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant php)
    if [ "$module_variant" != 'php-fpm' ] && [ ! "$param_force" ]; then
        echo "The installed PHP module is not FPM. See 'hestia module info php'."
        exit 1
    fi

    echo "Removing PHP (FPM) module..."

    # TODO: Remove all added versions

    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_BACKEND' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'version' '0'

    exit 0
}
