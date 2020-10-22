#!/bin/bash

hestia_module_apache_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'apache' && [ ! "$param_force" ]; then
        echo "Apache module is already installed. See 'hestia module info apache'."
        return 0
    fi

    echo "Installing Apache module..."

    osal_service_stop $OSAL_SERVICE_APACHE > /dev/null 2>&1
    hestia_config_backup 'apache-install' $OSAL_PATH_APACHE_CONF $OSAL_PATH_APACHE_CONF_D

    osal_package_preinstall
    osal_package_install $OSAL_PKG_APACHE $OSAL_PKG_APACHE_EXTRA

    mkdir -p $OSAL_PATH_APACHE_CONF_D/domains

    # Enable/disable required modules
    osal_apache_module_enable rewrite > /dev/null 2>&1
    osal_apache_module_enable suexec > /dev/null 2>&1
    osal_apache_module_enable ssl > /dev/null 2>&1
    osal_apache_module_enable actions > /dev/null 2>&1
    osal_apache_module_disable status > /dev/null 2>&1

    cp -f $HESTIA_INSTALL_DIR/$OSAL_PKG_APACHE/${OSAL_PKG_APACHE}.conf $OSAL_PATH_APACHE_CONF/
    cp -f $HESTIA_INSTALL_DIR/logrotate/${OSAL_PKG_APACHE} $OSAL_PATH_LOGROTATE_CONF_D/

    echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF/ports.conf

    if [ "$OS_BASE" = 'debian' ]; then
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF/sites-available/default
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF/sites-available/default-ssl
        echo -e "/home\npublic_html/cgi-bin" > /etc/apache2/suexec/www-data

        hestia_safe_rm $OSAL_PATH_APACHE_CONF/mods-available/hestia-status.load
        hestia_safe_rm $OSAL_PATH_APACHE_CONF/mods-available/hestia-status.conf

        # Copy configuration files
        cp -f $HESTIA_INSTALL_DIR/$OSAL_PKG_APACHE/status.conf $OSAL_PATH_APACHE_MODS_AVAILABLE/
        cp -f /etc/apache2/mods-available/status.load $OSAL_PATH_APACHE_MODS_AVAILABLE/
        # Prevent remote access to server-status page
        sed -i '/Allow from all/d' $OSAL_PATH_APACHE_MODS_AVAILABLE/status.conf
        osal_apache_module_enable status > /dev/null 2>&1
    elif [ "$OS_BASE" = 'rhel' ]; then
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF_D/ssl.conf
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF_D/welcome.conf
        echo "# Powered by Hestia" > $OSAL_PATH_APACHE_CONF_D/userdir.conf

        # Copy configuration files
        cp -f $HESTIA_INSTALL_DIR/$OSAL_PKG_APACHE/status.conf $OSAL_PATH_APACHE_CONF_D/
        # Prevent remote access to server-status page
        sed -i '/Allow from all/d' $OSAL_PATH_APACHE_CONF_D/status.conf
        osal_apache_module_enable status > /dev/null 2>&1

        # Delete CentOS style apache logs if present (Hestia will use error.log and access.log)
        [ -f /var/log/$OSAL_PKG_APACHE/access_log ] && hestia_safe_rm /var/log/$OSAL_PKG_APACHE/access_log
        [ -f /var/log/$OSAL_PKG_APACHE/error_log ] && hestia_safe_rm /var/log/$OSAL_PKG_APACHE/error_log

        # Mitigation for some Debian-only scripts
        [ ! -e /etc/httpd/mods-enabled ] && ln -s /etc/httpd/conf.modules.d/ /etc/httpd/mods-enabled
    fi

    touch /var/log/$OSAL_PKG_APACHE/access.log /var/log/$OSAL_PKG_APACHE/error.log
    mkdir -p /var/log/$OSAL_PKG_APACHE/domains
    chmod a+x /var/log/$OSAL_PKG_APACHE
    chmod 640 /var/log/$OSAL_PKG_APACHE/access.log /var/log/$OSAL_PKG_APACHE/error.log
    chmod 751 /var/log/$OSAL_PKG_APACHE/domains

    osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'service_name' $OSAL_SERVICE_APACHE
}
