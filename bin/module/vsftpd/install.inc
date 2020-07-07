#!/bin/sh

hestia_module_vsftpd_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled ftp)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "FTP module is already installed. See 'hestia module info ftp'."
        exit 1
    fi

    echo "Installing FTP (vsftpd) module..."

    osal_service_stop vsftpd
    hestia_config_backup 'vsftpd-install' $OSAL_PATH_VSFTPD_CONF/vsftpd.conf

    osal_package_preinstall
    osal_package_install vsftpd
    cp -f $HESTIA_INSTALL_DIR/vsftpd/vsftpd.conf $OSAL_PATH_VSFTPD_CONF/
    touch /var/log/vsftpd.log
    chown root:adm /var/log/vsftpd.log
    chmod 640 /var/log/vsftpd.log
    touch /var/log/xferlog
    chown root:adm /var/log/xferlog
    chmod 640 /var/log/xferlog
    
    osal_service_enable vsftpd
    osal_service_start vsftpd

    check_result $? "vsftpd start failed"

    osal_kv_write $HESTIA/conf/hestia.conf 'FTP_SYSTEM' 'vsftpd'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'description' 'Hestia FTP (Vsftpd) module'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'variant' 'vsftpd'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/ftp.conf 'service_name' 'vsftpd'

    exit 0
}

# If this files is _not_ being sourced, act immediately
# (otherise, wait for hestia cli to call the main function)
[[ $_ == $0 ]] && hestia_module_vsftpd_install $@
