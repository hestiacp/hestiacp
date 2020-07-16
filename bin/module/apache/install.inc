#!/bin/bash

hestia_module_apache_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled apache)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "Apache module is already installed. See 'hestia module info apache'."
        exit 1
    fi

    echo "Installing Apache module..."

    osal_service_stop $OSAL_SERVICE_APACHE > /dev/null 2>&1
    hestia_config_backup 'apache-install' $OSAL_PATH_APACHE_CONF

    osal_package_preinstall
    osal_package_install $OSAL_PKG_APACHE $OSAL_PKG_APACHE_EXTRA $OSAL_PKG_APACHE_MOD_RUID2

    mkdir -p $OSAL_PATH_APACHE_CONF/conf.d
    mkdir -p $OSAL_PATH_APACHE_CONF/conf.d/domains

    # Enable/disable required modules
    osal_apache_module_enable rewrite > /dev/null 2>&1
    osal_apache_module_enable suexec > /dev/null 2>&1
    osal_apache_module_enable ssl > /dev/null 2>&1
    osal_apache_module_enable actions > /dev/null 2>&1
    osal_apache_module_disable status > /dev/null 2>&1

    cp -f $HESTIA_INSTALL_DIR/$OSAL_PKG_APACHE/${OSAL_PKG_APACHE}.conf $OSAL_PATH_APACHE_CONF/
    cp -f $HESTIA_INSTALL_DIR/logrotate/${OSAL_PKG_APACHE} $OSAL_PATH_LOGROTATE_CONF_D/

    if [ "$OS_BASE" = 'debian' ]; then
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF/sites-available/default
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF/sites-available/default-ssl
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF/ports.conf
        echo -e "/home\npublic_html/cgi-bin" > /etc/apache2/suexec/www-data

        # Copy configuration files
        # FIXME: why don't we just overwrite status.conf?
        a2dismod status > /dev/null 2>&1
        mv -f $HESTIA_INSTALL_DIR/$OSAL_PKG_APACHE/status.conf $OSAL_PATH_APACHE_CONF/mods-available/hestia-status.conf
        mv -f /etc/apache2/mods-available/status.load $OSAL_PATH_APACHE_CONF/mods-available/hestia-status.load
        a2enmod hestia-status > /dev/null 2>&1
        # Prevent remote access to server-status page
        sed -i '/Allow from all/d' $OSAL_PATH_APACHE_CONF/mods-available/hestia-status.conf
    elif [ "$OS_BASE" = 'rhel' ]; then
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF/conf.d/welcome.conf
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF/conf.d/userdir.conf

        # Copy configuration files
        cp -f $HESTIA_INSTALL_DIR/$OSAL_PKG_APACHE/status.conf $OSAL_PATH_APACHE_CONF/conf.d/hestia-status.conf
        osal_apache_module_enable status > /dev/null 2>&1
    fi

    touch /var/log/$OSAL_PKG_APACHE/access.log /var/log/$OSAL_PKG_APACHE/error.log
    mkdir -p /var/log/$OSAL_PKG_APACHE/domains
    chmod a+x /var/log/$OSAL_PKG_APACHE
    chmod 640 /var/log/$OSAL_PKG_APACHE/access.log /var/log/$OSAL_PKG_APACHE/error.log
    chmod 751 /var/log/$OSAL_PKG_APACHE/domains

    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'description' 'Hestia Apache module'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'variant' 'apache'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'service_name' $OSAL_PKG_APACHE

    # Setup web module (depending on Nginx and/or Apache config)
    $BIN/hestia module web setup
}
