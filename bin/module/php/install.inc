#!/bin/bash

hestia_module_php_install() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    if hestia_module_isinstalled 'php' && [ ! "$param_force" ]; then
        echo "PHP module is already installed. See 'hestia module info php'."
        return 0
    fi

    echo "Installing PHP module..."

    if [ "$OS_BASE" = 'debian' ]; then
        cat > /etc/apt/sources.list.d/php.list <<EOL
# This file was added by Hestia Control Panel.
deb https://packages.sury.org/php/ $OS_CODENAME main
EOL
        apt-key adv --fetch-keys 'https://packages.sury.org/php/apt.gpg' > /dev/null 2>&1
    elif [ "$OS_BASE" = 'ubuntu' ]; then
        LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1
    elif [ "$OS_BASE" = 'rhel' ]; then
        # Disable CentOS PHP, enable Remi PHP
        dnf -y module disable php
        dnf -y install http://rpms.remirepo.net/enterprise/remi-release-$OS_VERSION.rpm
        dnf config-manager --set-enabled remi remi-modular
        dnf -y module enable php:remi-$PHP_DEFAULT_VERSION
    fi

    # Install mod_php for system default PHP
    osal_package_preinstall
    osal_execute_with_spinner osal_package_install $PHP_PKG_MOD_PHP

    local pool_d=$(osal_php_fpm_pool_d $PHP_DEFAULT_VERSION)
    mkdir -p $pool_d

    mkdir -p $OSAL_PATH_RUN/php

    cp -f $HESTIA_INSTALL_DIR/php-fpm/www.conf $pool_d/www.conf

    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_BACKEND' 'php-fpm'
    
    # Add default PHP version
    # Use --force, otherwise it will fail because technically PHP is still not installed
    echo "Adding default PHP version..."
    hestia module php add default --force

    return 0
}
