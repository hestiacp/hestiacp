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

    $BIN/hestia module php add $PHP_DEFAULT_VERSION

    pool_d=$(osal_multiphp_fpm_pool_d $PHP_DEFAULT_VERSION)
    mkdir -p $pool_d
    cp -f $HESTIA_INSTALL_DIR/php-fpm/www.conf $pool_d/www.conf

    if [ "$OS_BASE" = 'debian' ]; then    
        update-alternatives --set php /usr/bin/php$PHP_DEFAULT_VERSION > /dev/null 2>&1
    fi

    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_BACKEND' 'php-fpm'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'description' 'Hestia PHP (FPM) module'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'variant' 'php-fpm'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'service_name' ''
    
    # Add default version
    echo "Adding default PHP version..."
    $BIN/hestia module php add $PHP_DEFAULT_VERSION

    exit 0
}
