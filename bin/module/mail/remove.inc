#!/bin/sh

hestia_module_vsftpd_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled mail)
    if [ ! "$module_installed" ] || [ "$param_force" ]; then
        echo "Mail module is not installed. See 'hestia module info mail'."
        exit 1
    fi

    echo "Removing Mail module..."

    osal_service_stop exim
    osal_service_disable dovecot
    #osal_package_remove 

    #osal_kv_write $HESTIA/conf/hestia.conf 'FTP_SYSTEM' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/mail.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/mail.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/mail.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/mail.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/mail.conf 'version' '0'

    exit 0
}
