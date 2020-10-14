#!/bin/bash

hestia_module_php_integrate() {
    source $HESTIA/bin/module/func.inc
    
    if hestia_module_isinstalled 'apache'; then
        local php_variant=$(hestia_module_variant_installed 'php')
        if [ "$php_variant" = 'php-fpm' ]; then
            # Enable mod_ruid/mpm_itk or mpm_event
            # Disable prefork and php, enable event
            osal_apache_module_disable php$fpm_v > /dev/null 2>&1
            osal_apache_module_disable mpm_prefork > /dev/null 2>&1
            osal_apache_module_enable mpm_event > /dev/null 2>&1
            cp -f $HESTIA_INSTALL_DIR/$WEB_SYSTEM/hestia-event.conf $OSAL_PATH_APACHE_CONF_D/
        elif [ "$php_variant" = 'ruid2' ]; then
            # FIXME: enable only if needed
            osal_apache_module_enable ruid2 > /dev/null 2>&1
        fi
    fi
}
