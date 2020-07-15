#!/bin/sh

hestia_module_clamav_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled antivirus)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "antivirus module is not installed. See 'hestia module info antivirus'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant antivirus)
    if [ "$module_variant" != 'clamav' ] && [ ! "$param_force" ]; then
        echo "The installed antivirus module is not ClamAV. See 'hestia module info antivirus'."
        exit 1
    fi

    echo "Removing antivirus (ClamAV) module..."

    osal_service_stop $OSAL_SERVICE_CLAMAV
    osal_service_disable $OSAL_SERVICE_CLAMAV

    hestia_config_backup 'clamav-remove' $OSAL_PATH_CLAMAV_CONF

    rm -rf $OSAL_PATH_CLAMAV_CONF

    exim_installed=$(hestia_module_variant_installed 'antivirus' 'exim')
    if [ "$exim_installed" ]; then
        hestia module clamav exim-integration disable
    fi

    osal_package_remove $OSAL_PKG_EXIM

    osal_kv_write $HESTIA/conf/hestia.conf 'MAIL_SYSTEM' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'version' '0'

    exit 0
}
