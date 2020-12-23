#!/bin/bash

hestia_module_php-fpm_add() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    if ! hestia_module_isinstalled 'php-fpm' && [ ! "$param_force" ]; then
        echo "PHP (FPM) module is not installed. See 'hestia module info php-php'."
        return 1
    fi

    # PHP version is first parameter
    param_ver="$1"

    if [ ! "$param_ver" ]; then
        echo "You must specify PHP version"
        return 1
    fi

    if [ "$param_ver" == "default" ]; then
        param_ver="$PHP_DEFAULT_VERSION"
    fi

    # Verify php version format
    if [[ ! "$param_ver" =~ ^[0-9]\.[0-9]+ ]]; then
        echo "The PHP version format is invalid, it should look like [0-9].[0-9]."
        exit
    fi

    # Check version is supported
    php_version=''
    for ver in $PHP_SUPPORTED_VERSIONS; do
        if [ "$param_ver" == "$ver" ]; then
            php_version="$param_ver"
            break;
        fi
    done
    if [ ! "$php_version" ]; then
        echo "PHP (FPM( version $param_ver is not supported."
        return 1
    fi

    local php_withoutdot=${php_version//.}
    local php_version_present=$(osal_kv_read_bool $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" && echo 1)

    if [ "$php_version_present" ] && [ ! "$param_force" ]; then
        echo "PHP (FPM) version ${php_version} is already present. See 'hestia module php list'."
        return 0
    fi


    # Perform verification if read-only mode is enabled
    check_hestia_demo_mode

    echo "Adding PHP (FPM) version ${php_version}..."

    local php_prefix=$(osal_php_package_prefix $php_version)

    # Install php packages
    osal_package_preinstall
    osal_execute_with_spinner osal_package_install $php_prefix-fpm

    # Check if required modules for apache2 are enabled
    if [ "$WEB_SYSTEM" = "apache2" ] || [ "$WEB_SYSTEM" = "httpd" ]; then
        osal_apache_module_enable 'proxy_fcgi'
        osal_apache_module_enable 'setenvif'
    fi

    # Configure FPM
    osal_service_enable $php_prefix-fpm
    pool_d=$(osal_php_fpm_pool_d $php_version)
    if [ "$pool_d" ]; then
        mkdir -p $pool_d
        hestia_safe_rm $pool_d/*
        cp -f $HESTIA_INSTALL_DIR/php-fpm/dummy.conf $pool_d/
        sed -i "s/9999/99$php_withoutdot/g" $pool_d/dummy.conf       # FIXME: this'll break with PHP 10.0
    fi

    # Install backend template
    cp -f $HESTIA_INSTALL_DIR/php-fpm/multiphp.tpl \
        $HESTIA/data/templates/web/php-fpm/PHP-${php_version/\./_}.tpl

    osal_kv_write $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" '1'

    if hestia_module_isinstalled 'php'; then
        hestia module php add $php_version
    fi

    # Integrate everything
    osal_enqueue_integrate php-fpm

    log_history "installed php $php_version" '' 'admin'
}
