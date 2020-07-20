#!/bin/bash

hestia_module_phpmyadmin_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'phpmyadmin' && [ ! "$param_force" ]; then
        echo "phpMyAdmin module is already installed. See 'hestia module info phpmyadmin'."
        return 1
    fi

    if [ -z "$WEB_SYSTEM" ] && [ ! "$param_force" ]; then
        echo "phpMyAdmin needs a web server."
        return 1
    fi

    local pma_version='5.0.2'
    echo "Installing phpMyAdmin module..."
    
    hestia_config_backup 'phpmyadmin-remove' $OSAL_PATH_NGINX_CONF_D/phpmyadmin.inc \
        $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf /usr/share/phpmyadmin /etc/phpmyadmin

    local tmp_dir=$(mktemp -d -t hestia-XXXXXXXXXX)
    cd $tmp_dir

    # Download and unpack
    [[ "$HESTIA_DEBUG" ]] && echo $tmp_dir
    [[ "$HESTIA_DEBUG" ]] && echo https://files.phpmyadmin.net/phpMyAdmin/$pma_version/phpMyAdmin-$pma_version-all-languages.tar.gz
    wget --quiet https://files.phpmyadmin.net/phpMyAdmin/$pma_version/phpMyAdmin-$pma_version-all-languages.tar.gz
    tar xzf phpMyAdmin-$pma_version-all-languages.tar.gz

    # Delete file to prevent error. FIXME: prevent what error?
    mkdir -p /usr/share/phpmyadmin
    cd - > /dev/null
    hestia_safe_rm /usr/share/phpmyadmin/doc/html

    cp -rf phpMyAdmin-$pma_version-all-languages/* /usr/share/phpmyadmin
    [[ "$HESTIA_DEBUG" ]] || rm -rf $tmp_dir

    # Set config and log directory
    sed -i "s|define('CONFIG_DIR', ROOT_PATH);|define('CONFIG_DIR', '/etc/phpmyadmin/');|" /usr/share/phpmyadmin/libraries/vendor_config.php
    sed -i "s|define('TEMP_DIR', ROOT_PATH . 'tmp/');|define('TEMP_DIR', '/var/lib/phpmyadmin/tmp/');|" /usr/share/phpmyadmin/libraries/vendor_config.php

    # Create folders and change permissions
    mkdir -p /etc/phpmyadmin
    mkdir -p /var/lib/phpmyadmin/tmp
    chmod 777 /var/lib/phpmyadmin/tmp

    if [ -e /var/lib/phpmyadmin/blowfish_secret.inc.php ]; then
        chmod 0644 /var/lib/phpmyadmin/blowfish_secret.inc.php
    fi

    cp -f $HESTIA_INSTALL_DIR/pma/config.inc.php /etc/phpmyadmin/

    osal_kv_write $HESTIA/conf/hestia.conf 'DB_PMA_ALIAS' 'phpmyadmin'
    $HESTIA/bin/v-change-sys-db-alias 'pma' 'phpmyadmin'

    if [ "$WEB_SYSTEM" = 'nginx' ]; then
        cp -f $HESTIA_INSTALL_DIR/nginx/phpmyadmin.inc $OSAL_PATH_NGINX_CONF_D/
    else
        cp -f $HESTIA_INSTALL_DIR/pma/apache.conf /etc/phpmyadmin/
        ln -s /etc/phpmyadmin/apache.conf $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf
    fi

    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'description' 'Hestia phpMyAdmin module'
    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'variant' 'phpmyadmin'
    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'version' '1'
}
