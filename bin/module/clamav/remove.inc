#!/bin/bash

hestia_module_clamav_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'antivirus' && [ ! "$param_force" ]; then
        echo "antivirus module is not installed. See 'hestia module info antivirus'."
        return 0
    fi

    module_variant=$(hestia_module_getvariant antivirus)
    if [ "$module_variant" != 'clamav' ] && [ ! "$param_force" ]; then
        echo "The installed antivirus module is not ClamAV. See 'hestia module info antivirus'."
        return 1
    fi

    echo "Removing antivirus (ClamAV) module..."

    if [[ $(hestia_module_variant_installed 'mta') == 'exim' ]]; then
        hestia module clamav exim-integration disable
    fi

    osal_service_stop $OSAL_SERVICE_CLAMAV > /dev/null 2>&1
    osal_service_disable $OSAL_SERVICE_CLAMAV > /dev/null 2>&1

    hestia_config_backup 'clamav-remove' $OSAL_PATH_CLAMAV_CONF $OSAL_PATH_CLAMAV_CONF_D

    hestia_safe_rm $OSAL_PATH_CLAMAV_CONF $OSAL_PATH_CLAMAV_CONF_D

    osal_package_remove $OSAL_PKG_CLAMAV

    osal_kv_write $HESTIA/conf/hestia.conf 'ANTIVIRUS_SYSTEM' ''
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'version' '0'

    osal_enqueue_integrate clamav
}
