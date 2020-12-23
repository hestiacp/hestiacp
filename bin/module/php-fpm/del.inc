#!/bin/bash

hestia_module_php-fpm_del() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    if ! hestia_module_isinstalled 'php' && [ ! "$param_force" ]; then
        echo "PHP module is not installed. See 'hestia module info php'."
        return 1
    fi

    # PHP version is first parameter
    local param_ver="$1"

    if [ ! $param_ver ]; then
        echo "You must specify PHP version"
        return 1
    fi

    # Verify php version format
    if [[ ! $param_ver =~ ^[0-9]\.[0-9]+ ]]; then
        echo "The PHP version format is invalid, it should look like [0-9].[0-9]..."
        exit
    fi

    # Check version is supported
    php_version=''
    for ver in $PHP_SUPPORTED_VERSIONS; do
        if [ "$param_ver" == "$ver" ]; then
            php_version=$param_ver
            break;
        fi
    done
    if [ ! "$php_version" ]; then
        echo "PHP version $param_ver is not supported."
        return 1
    fi

    php_withoutdot=${php_version//.}

    php_version_present=$(osal_kv_read_bool $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" && echo 1)
    if [ ! "$php_version_present" ] && [ ! "$param_force" ]; then
        echo "PHP (FPM) version ${php_version} is not present. See 'hestia module php list'."
        return 0
    fi

    # Perform verification if read-only mode is enabled
    check_hestia_demo_mode

    echo "Removing PHP (FPM) version ${php_version}..."

    php_etc_folder=$(osal_php_etc_folder $php_version)
    hestia_config_backup 'php-fpm-del' $php_etc_folder \
        $HESTIA/data/templates/web/php/PHP-${php_version/\./_}.tpl

    php_prefix=$(osal_php_package_prefix $php_version)

    # Purge PHP packages
    osal_service_stop $php_prefix-fpm > /dev/null 2>&1
    osal_service_disable $php_prefix-fpm > /dev/null 2>&1
    osal_execute_with_spinner osal_package_remove $php_prefix-fpm

    # Cleanup files and folders
    [ -f $HESTIA/data/templates/web/php-fpm/PHP-${php_version/\./_}.tpl ] && hestia_safe_rm $HESTIA/data/templates/web/php-fpm/PHP-${php_version/\./_}.tpl

    osal_kv_write $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" '0'

    osal_enqueue_integrate php-fpm

    log_history "removed php-fpm $phpversion" '' 'admin'

    return 0
}
