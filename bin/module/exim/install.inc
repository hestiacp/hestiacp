#!/bin/bash

hestia_module_exim_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'mta' && [ ! "$param_force" ]; then
        echo "MTA module is already installed. See 'hestia module info mta'."
        return 0
    fi

    echo "Installing MTA (Exim) module..."

    osal_service_stop $OSAL_SERVICE_EXIM > /dev/null 2>&1
    hestia_config_backup 'exim-install' $OSAL_DIR_EXIM_CONF

    osal_package_preinstall
    osal_package_install $OSAL_PKG_EXIM

    gpasswd -a $OSAL_USER_EXIM mail > /dev/null 2>&1

    cp -f $HESTIA_INSTALL_DIR/exim/${OSAL_FILENAME_EXIM_CONF} "$OSAL_PATH_EXIM_CONF"
    chmod 640 "$OSAL_PATH_EXIM_CONF"

    cp -f $HESTIA_INSTALL_DIR/exim/dnsbl.conf $OSAL_DIR_EXIM_CONF/
    cp -f $HESTIA_INSTALL_DIR/exim/spam-blocks.conf $OSAL_DIR_EXIM_CONF/
    touch $OSAL_DIR_EXIM_CONF/white-blocks.conf
    touch $OSAL_DIR_EXIM_CONF/mailhelo.conf

    hestia_safe_rm $OSAL_DIR_EXIM_CONF/domains
    mkdir -p $OSAL_DIR_EXIM_CONF/domains

    hestia_safe_rm /etc/alternatives/mta
    ln -s /usr/sbin/$OSAL_SERVICE_EXIM /etc/alternatives/mta
    
    osal_service_stop sendmail > /dev/nul 2>&1
    osal_service_disable sendmail > /dev/nul 2>&1
    osal_service_stop postfix > /dev/nul 2>&1
    osal_service_disable postfix > /dev/nul 2>&1

    osal_service_enable $OSAL_SERVICE_EXIM
    osal_service_start $OSAL_SERVICE_EXIM
    check_result $? "Exim start failed"

    osal_kv_write $HESTIA/conf/hestia.conf 'MAIL_SYSTEM' 'exim'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'description' 'Hestia MTA (Exim) module'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'variant' 'exim'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'service_name' "$OSAL_SERVICE_EXIM"

    osal_enqueue_integrate exim
}
