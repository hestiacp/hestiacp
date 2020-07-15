#!/bin/sh

hestia_module_dovecot_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled imap)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "IMAP module is not installed. See 'hestia module info imap'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant imap)
    if [ "$module_variant" != 'dovecot' ] && [ ! "$param_force" ]; then
        echo "The installed IMAP module is not Dovecot. See 'hestia module info imap'."
        exit 1
    fi

    echo "Removing IMAP (Dovecot) module..."

    osal_service_stop $OSAL_SERVICE_DOVECOT > /dev/null 2>&1
    osal_service_disable $OSAL_SERVICE_DOVECOT > /dev/null 2>&1

    hestia_config_backup 'dovecot-remove' $OSAL_PATH_DOVECOT_CONF
    rm -rf $OSAL_PATH_DOVECOT_CONF

    osal_package_remove $OSAL_PKG_DOVECOT

    osal_kv_write $HESTIA/conf/hestia.conf 'IMAP_SYSTEM' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/imap.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/imap.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/imap.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/imap.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/imap.conf 'version' '0'

    exit 0
}
