#!/bin/bash

hestia_module_php-fpm_install() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    if hestia_module_isinstalled 'php-fpm' && [ ! "$param_force" ]; then
        echo "PHP (FPM) module is already installed. See 'hestia module info php-fpm'."
        return 0
    fi

    echo "Installing PHP (FPM) module..."

    # Install mod_php for systems default PHP
    osal_package_preinstall
    osal_execute_with_spinner osal_package_install $PHP_PKG_MOD_PHP

    local pool_d=$(osal_php_fpm_pool_d $PHP_DEFAULT_VERSION)
    mkdir -p $pool_d
    mkdir -p $OSAL_PATH_RUN/php

    cp -f $HESTIA_INSTALL_DIR/php-fpm/www.conf $pool_d/www.conf

    osal_kv_write $HESTIA_CONF_MODULES/php-fpm.conf 'installed' 1
    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_BACKEND' 'php-fpm'

    # Add FPM support for each CLI version installed
    for php_version in $PHP_SUPPORTED_VERSIONS; do
        php_withoutdot=${php_version//.}
        php_version_present=$(osal_kv_read $HESTIA_CONF_MODULES/php.conf "php${php_withoutdot}_present" 0)
        if [ "php_version_present" ]; then
            hestia module php-fpm add $php_version
        fi
    done

    return 0
}
