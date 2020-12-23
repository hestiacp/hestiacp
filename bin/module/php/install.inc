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
        dnf -y -q module disable php
        dnf -y -q install http://rpms.remirepo.net/enterprise/remi-release-$OS_VERSION.rpm
        dnf config-manager --set-enabled remi remi-modular
        dnf -y -q module reset php
        dnf -y -q module enable php:remi-$PHP_DEFAULT_VERSION
    fi

    # Install mod_php for system default PHP
    osal_package_preinstall
    osal_execute_with_spinner osal_package_install $PHP_PKG_MOD_PHP

    mkdir -p $OSAL_PATH_RUN/php

    osal_kv_write $HESTIA_CONF_MODULES/php.conf 'installed' 1
    
    # Add default PHP version
    echo "Adding default PHP (CLI) version..."
    hestia module php add default

    return 0
}
