#!/bin/bash

hestia_module_php-fpm_install() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php-fpm/func.inc

    if hestia_module_isinstalled 'php-fpm' && [ ! "$param_force" ]; then
        echo "PHP (FPM) module is already installed. See 'hestia module info php-fpm'."
        return 1
    fi

    echo "Installing PHP (FPM) module..."

    local pool_d=$(osal_php_fpm_pool_d $PHP_DEFAULT_VERSION)
    mkdir -p $pool_d
    cp -f $HESTIA_INSTALL_DIR/php-fpm/www.conf $pool_d/www.conf

    if [ "$OS_BASE" = 'debian' ]; then    
        update-alternatives --set php /usr/bin/php$PHP_DEFAULT_VERSION > /dev/null 2>&1
    fi

    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_BACKEND' 'php-fpm'
    
    # Add default PHP version
    # Use --force, otherwise it will fail because technically php-fpm is still not installed
    echo "Adding default PHP version..."
    hestia module php-fpm add $PHP_DEFAULT_VERSION --force

    return 0
}
