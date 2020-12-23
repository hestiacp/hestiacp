#!/bin/bash

hestia_module_spamassassin_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'antispam' && [ ! "$param_force" ]; then
        echo "Antispam module is not installed. See 'hestia module info antispam'."
        return 0
    fi

    module_variant=$(hestia_module_getvariant antispam)
    if [ "$module_variant" != 'spamassassin' ] && [ ! "$param_force" ]; then
        echo "The installed antispam module is not SpamAssassin. See 'hestia module info antispam'."
        return 1
    fi

    echo "Removing antispam (SpamAssassin) module..."

    if [[ $(hestia_module_variant_installed 'mta') == 'exim' ]]; then
        hestia module spamassassin exim-integration disable
    fi

    osal_service_stop $OSAL_SERVICE_SPAMASSASSIN > /dev/null 2>&1
    osal_service_disable $OSAL_SERVICE_SPAMASSASSIN > /dev/null 2>&1

    hestia_config_backup 'spamassassin-remove' $OSAL_PATH_SPAMASSASSIN_CONF

    osal_package_remove $OSAL_PKG_SPAMASSASSIN

    hestia_safe_rm $OSAL_PATH_SPAMASSASSIN_CONF

    osal_kv_write $HESTIA/conf/hestia.conf 'ANTISPAM_SYSTEM' ''
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'version' '0'

    osal_enqueue_integrate spamassassin
}
