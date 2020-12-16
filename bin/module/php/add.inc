#!/bin/bash

hestia_module_php_add() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    if ! hestia_module_isinstalled 'php' && [ ! "$param_force" ]; then
        echo "PHP (CLI) module is not installed. See 'hestia module info php'."
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
        echo "PHP (CLI) version $param_ver is not supported."
        return 1
    fi

    local php_withoutdot=${php_version//.}
    local php_version_present=$(osal_kv_read_bool $HESTIA_CONF_MODULES/php.conf "php${php_withoutdot}_present" && echo 1)

    if [ "$php_version_present" ] && [ ! "$param_force" ]; then
        echo "PHP (CLI) version ${php_version} is already present. See 'hestia module php list'."
        return 0
    fi

    # Perform verification if read-only mode is enabled
    check_hestia_demo_mode

    echo "Adding PHP version ${php_version}..."

    local php_prefix=$(osal_php_package_prefix $php_version)

    mph="$php_prefix-mbstring $php_prefix-bcmath $php_prefix-cli $php_prefix-curl
        $php_prefix-gd $php_prefix-intl $php_prefix-mysql
        $php_prefix-soap $php_prefix-xml $php_prefix-zip $php_prefix-mbstring
        $php_prefix-bz2 $php_prefix-pspell $php_prefix-imagick $php_prefix-pgsql
        $php_prefix-imap $php_prefix-ldap $php_prefix-opcache"

    if [ "$OS_BASE" = 'rhel' ]; then
        mph="$mph $php_prefix-apcu"
    else
        mph="$mph php-apcu"
    fi

    # Check is version is 7.1 or below to add mcrypt
    if [[ `echo "$php_version 7.2" | awk '{print ($1 < $2)}'` == 1 ]]; then
        mph="$mph $php_prefix-mcrypt"
    fi

    # Check is version is below 8.0 to add json
    if [[ `echo "$php_version 8.0" | awk '{print ($1 < $2)}'` == 1 ]]; then
        mph="$mph $php_prefix-json"
    fi

    # Install php packages
    osal_package_preinstall
    osal_execute_with_spinner osal_package_install $mph

    osal_kv_write $HESTIA_CONF_MODULES/php.conf "php${php_withoutdot}_present" '1'

    if hestia_module_isinstalled 'php-fpm'; then
        hestia module php-fpm add $php_version
    fi

    # Integrate everything
    if [ ! "$param_without_no_integrate" ]; then
        hestia module php integrate
    fi

    log_history "installed php $php_version" '' 'admin'
}
