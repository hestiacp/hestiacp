#!/bin/sh

hestia_module_clamav_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled antispam)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "Antispam module is not installed. See 'hestia module info antispam'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant antispam)
    if [ "$module_variant" != 'clamav' ] && [ ! "$param_force" ]; then
        echo "The installed antispam module is not SpamAssassin. See 'hestia module info antispam'."
        exit 1
    fi

    echo "Removing antispam (SpamAssassin) module..."

    osal_service_stop $OSAL_SERVICE_SPAMASSASSIN
    osal_service_disable $OSAL_SERVICE_SPAMASSASSIN

    hestia_config_backup 'clamav-remove' $OSAL_PATH_SPAMASSASSIN_CONF

    exim_installed=$(hestia_module_variant_installed 'mta' 'exim')
    if [ "$exim_installed" ]; then
        hestia module spamassassin exim-integration disable
    fi

    osal_package_remove $OSAL_PKG_SPAMASSASSIN

    rm -rf $OSAL_PATH_SPAMASSASSIN_CONF

    osal_kv_write $HESTIA/conf/hestia.conf 'ANTISPAM_SYSTEM' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'version' '0'

    exit 0
}
