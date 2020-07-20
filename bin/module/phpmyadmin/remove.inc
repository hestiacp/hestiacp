#!/bin/bash

hestia_module_phpmyadmin_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'phpmyadmin' && [ ! "$param_force" ]; then
        echo "phpMyAdmin module is not installed. See 'hestia module info phpmyadmin'."
        return 1
    fi

    echo "Removing phpMyAdmin module..."

    hestia_config_backup 'phpmyadmin-remove' $OSAL_PATH_NGINX_CONF_D/phpmyadmin.inc \
        $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf /usr/share/phpmyadmin /etc/phpmyadmin
    
    hestia_safe_rm $OSAL_PATH_NGINX_CONF_D/phpmyadmin.inc \
        $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf /usr/share/phpmyadmin /etc/phpmyadmin \
        /var/lib/phpmyadmin

    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/phpmyadmin.conf 'version' '0'
}
