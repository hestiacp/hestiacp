#!/bin/sh

hestia_module_exim_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled mta)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "MTA module is not installed. See 'hestia module info mta'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant mta)
    if [ "$module_variant" != 'exim' ] && [ ! "$param_force" ]; then
        echo "The installed MTA module is not Exim. See 'hestia module info mta'."
        exit 1
    fi

    echo "Removing MTA (Exim) module..."

    osal_service_stop $OSAL_SERVICE_EXIM
    osal_service_disable $OSAL_SERVICE_EXIM

    hestia_config_backup 'exim-remove' $OSAL_PATH_EXIM_CONF

    osal_package_remove $OSAL_PKG_EXIM

    osal_kv_write $HESTIA/conf/hestia.conf 'MAIL_SYSTEM' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'version' '0'

    exit 0
}
