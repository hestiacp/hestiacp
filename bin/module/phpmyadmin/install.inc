#!/bin/bash

hestia_module_phpmyadmin_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'phpmyadmin' && [ ! "$param_force" ]; then
        echo "phpMyAdmin module is already installed. See 'hestia module info phpmyadmin'."
        return 1
    fi

    local pma_version='5.0.2'
    echo "Installing phpMyAdmin module..."
    
    hestia_config_backup 'phpmyadmin-remove' $OSAL_PATH_NGINX_CONF_D/phpmyadmin.inc \
        $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf /usr/share/phpmyadmin /etc/phpmyadmin

    # Create and change to a temporal directory
    local tmp_dir=$(mktemp -d -t hestia-XXXXXXXXXX)
    if [ ! -d "$tmp_dir" ]; then
        echo Unable to create temporary directory
        exit 1
    fi
    cd $tmp_dir

    # Download and unpack
    [[ "$HESTIA_DEBUG" ]] && echo $tmp_dir
    local pma_archive_name="phpMyAdmin-$pma_version-all-languages.tar.gz"
    local pma_download_url="https://files.phpmyadmin.net/phpMyAdmin/$pma_version/$pma_archive_name"
    [[ "$HESTIA_DEBUG" ]] && echo wget --quiet $pma_download_url
    wget --quiet $pma_download_url
    if [ ! -f "$pma_archive_name" ]; then
        echo Error downloading phpMyAdmin from $pma_download_url
        rm -rf $tmp_dir
        exit 1
    fi

    mkdir -p /usr/share/phpmyadmin

    [[ "$HESTIA_DEBUG" ]] && echo tar --strip-components=1 -xz -f $pma_archive_name -C /usr/share/phpmyadmin
    tar --strip-components=1 -xz -f $pma_archive_name -C /usr/share/phpmyadmin


    cd - > /dev/null
    [[ "$HESTIA_DEBUG" ]] || rm -rf $tmp_dir
    rm -rf $tmp_dir

    # Delete file to prevent error. FIXME: prevent what error?
    hestia_safe_rm /usr/share/phpmyadmin/doc/html

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
}
