#!/bin/sh

hestia_module_php_install() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    module_installed=$(hestia_module_isinstalled php)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "PHP module is already installed. See 'hestia module info php'."
        exit 1
    fi

    echo "Installing PHP (FPM) module..."

    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_BACKEND' 'php-fpm'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'description' 'Hestia PHP (FPM) module'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'variant' 'php-fpm'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'service_name' ''

    # Add default version
    echo "Adding default PHP version..."
    hestia module php add --ver $PHP_DEFAULT_VERSION

    exit 0
}
