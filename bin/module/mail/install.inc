#!/bin/sh

hestia_module_vsftpd_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled mail)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "Mail module is already installed. See 'hestia module info mail'."
        exit 1
    fi

    echo "Installing Mail module..."

    osal_service_stop exim
    osal_service_stop dovecot
    #hestia_config_backup 'exim-install' $OSAL_PATH_VSFTPD_CONF/vsftpd.conf

    #osal_package_preinstall
    #osal_package_install vsftpd
    #cp -f $HESTIA_INSTALL_DIR/vsftpd/vsftpd.conf $OSAL_PATH_VSFTPD_CONF/
        
    osal_service_enable exim
    osal_service_start dovecot

    check_result $? "vsftpd start failed"

    #osal_kv_write $HESTIA/conf/hestia.conf 'FTP_SYSTEM' 'vsftpd'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'description' 'Hestia Mail module'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'service_name' 'exim dovecot'

    exit 0
}
