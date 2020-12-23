#!/bin/bash

hestia_module_exim_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'mta' && [ ! "$param_force" ]; then
        echo "MTA module is not installed. See 'hestia module info mta'."
        return 0
    fi

    module_variant=$(hestia_module_getvariant mta)
    if [ "$module_variant" != 'exim' ] && [ ! "$param_force" ]; then
        echo "The installed MTA module is not Exim. See 'hestia module info mta'."
        return 1
    fi

    echo "Removing MTA (Exim) module..."

    clamav_installed=$(hestia_module_variant_installed 'antivirus' 'clamav')
    if [ "$clamav_installed" ]; then
        hestia module clamav exim-integration disable
    fi

    spamassassin_installed=$(hestia_module_variant_installed 'antispam' 'spamassassin')
    if [ "$spamassassin_installed" ]; then
        hestia module spamassassin exim-integration disable
    fi

    osal_service_stop $OSAL_SERVICE_EXIM > /dev/null 2>&1
    osal_service_disable $OSAL_SERVICE_EXIM > /dev/null 2>&1

    hestia_config_backup 'exim-remove' "$OSAL_DIR_EXIM_CONF"

    osal_package_remove $OSAL_PKG_EXIM

    osal_kv_write $HESTIA/conf/hestia.conf 'MAIL_SYSTEM' ''
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'version' '0'

    osal_enqueue_integrate exim
}
