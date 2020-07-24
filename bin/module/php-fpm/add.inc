#!/bin/bash

hestia_module_php-fpm_add() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php-fpm/func.inc

    if ! hestia_module_isinstalled 'php-fpm' && [ ! "$param_force" ]; then
        echo "PHP (FPM) module is not installed. See 'hestia module info php-fpm'."
        return 1
    fi

    # PHP version is first parameter
    param_ver="$1"

    if [ ! "$param_ver" ]; then
        echo "You must specify PHP version"
        return 1
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
        echo "PHP (FPM) version $param_ver is not supported."
        return 1
    fi

    local php_withoutdot=${php_version//.}
    local php_version_present=$(osal_kv_read_bool $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" && echo 1)

    if [ "$php_version_present" ] && [ ! "$param_force" ]; then
        echo "PHP version ${php_version} is already present. See 'hestia module php-fpm list'."
        return 1
    fi

    # Check if php version is supported
    # TODO:
    if [ ! -f "$HESTIA_INSTALL_DIR/multiphp/$WEB_SYSTEM/PHP-${php_withoutdot}.sh" ]; then
        echo "$HESTIA_INSTALL_DIR/multiphp/$WEB_SYSTEM/PHP-${php_withoutdot}.sh"
        echo "PHP (FPM) version ${php_version} is currently not supported or does not exist."
        return 1
    fi

    # Perform verification if read-only mode is enabled
    check_hestia_demo_mode

    echo "Adding PHP (FPM) version ${php_version}..."

    local php_prefix=$(osal_multiphp_php_package_prefix $php_version)

    mph="$php_prefix-mbstring $php_prefix-bcmath $php_prefix-cli $php_prefix-curl
        $php_prefix-fpm $php_prefix-gd $php_prefix-intl $php_prefix-mysql
        $php_prefix-soap $php_prefix-xml $php_prefix-zip $php_prefix-mbstring
        $php_prefix-json $php_prefix-bz2 $php_prefix-pspell $php_prefix-imagick $php_prefix-pgsql
        $php_prefix-imap $php_prefix-ldap"

    # Check is version is 7.1 or below to add mcrypt
    if [[ `echo "$php_version 7.2" | awk '{print ($1 < $2)}'` == 1 ]]; then
        mph="$mph $php_prefix-mcrypt"
    fi

    # Install php packages
    osal_package_preinstall
    osal_execute_with_spinner osal_package_install $mph

    # Check if installation was sucessfully
    if [ ! $(osal_multiphp_fpm_isinstalled $php_version) ]; then
        echo "Installation failed, please run the following command manualy for debuging:"
        echo "$OSAL_CMD_PACKAGE_MANAGER install ${mph//\\n/ \\}"
    fi

    # Check if required modules for apache2 are enabled
    if [ "$WEB_SYSTEM" = "apache2" ] || [ "$WEB_SYSTEM" = "httpd" ]; then
        osal_apache_module_enable 'proxy_fcgi'
        osal_apache_module_enable 'setenvif'
    fi

    # Configure FPM
    osal_service_enable $php_prefix-fpm > /dev/null 2>&1
    pool_d=$(osal_multiphp_fpm_pool_d $php_version)
    if [ "$pool_d" ]; then
        mkdir -p $pool_d
        hestia_safe_rm $pool_d/*
        cp -f $HESTIA_INSTALL_DIR/php-fpm/dummy.conf $pool_d/
        sed -i "s/9999/99$php_withoutdot/g" $pool_d/dummy.conf       # FIXME: this'll break with PHP 10.0
    fi

    # Install backend template
    cp -f $HESTIA_INSTALL_DIR/php-fpm/multiphp.tpl \
        $HESTIA/data/templates/web/php-fpm/PHP-${php_version/\./_}.tpl

    #----------------------------------------------------------#
    #                       Hestia                             #
    #----------------------------------------------------------#

    osal_kv_write $HESTIA_CONF_MODULES/php-fpm.conf "php${php_withoutdot}_present" '1'

    log_history "installed php $php_version" '' 'admin'

    $BIN/v-restart-web
}
