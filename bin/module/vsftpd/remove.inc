#!/bin/sh

hestia_module_vsftpd_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled ftp)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "FTP module is not installed. See 'hestia module info ftp'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant ftp)
    if [ "$module_variant" != 'vsftpd' ] && [ ! "$param_force" ]; then
        echo "The installed FTP module is not vsftpd. See 'hestia module info ftp'."
        exit 1
    fi

    echo "Removing FTP (vsftpd) module..."

    osal_service_stop vsftpd
    osal_service_disable vsftpd

    hestia_config_backup 'vsftpd-remove' $OSAL_PATH_VSFTPD_CONF

    osal_package_remove vsftpd
   
    rm -rf $OSAL_PATH_VSFTPD_CONF

    osal_kv_write $HESTIA/conf/hestia.conf 'FTP_SYSTEM' ''
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'version' '0'

    exit 0
}
