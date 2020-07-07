#!/bin/sh

hestia_module_exim_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled mta)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "MTA module is already installed. See 'hestia module info mta'."
        exit 1
    fi

    echo "Installing MTA (Exim) module..."

    osal_service_stop $OSAL_PKG_EXIM
    hestia_config_backup 'exim-install' $OSAL_PATH_EXIM_CONF

    osal_package_preinstall
    osal_package_install $OSAL_PKG_EXIM $OSAL_PKG_EXIM_DAEMON

    gpasswd -a $OSAL_USER_EXIM mail > /dev/null 2>&1
    cp -f $HESTIA_INSTALL_DIR/exim/${OSAL_PKG_EXIM}.conf.template $OSAL_PATH_EXIM_CONF/
    cp -f $HESTIA_INSTALL_DIR/exim/dnsbl.conf $OSAL_PATH_EXIM_CONF/
    cp -f $HESTIA_INSTALL_DIR/exim/spam-blocks.conf $OSAL_PATH_EXIM_CONF/
    touch $OSAL_PATH_EXIM_CONF/white-blocks.conf

    // TODO: move to clamd and spamd installers
    #if [ "$spamd" = 'yes' ]; then
    #    sed -i "s/#SPAM/SPAM/g" /etc/exim4/exim4.conf.template
    #fi
    #if [ "$clamd" = 'yes' ]; then
    #    sed -i "s/#CLAMD/CLAMD/g" /etc/exim4/exim4.conf.template
    #fi

    chmod 640 $OSAL_PATH_EXIM_CONF/${OSAL_PKG_EXIM}.conf.template
    rm -rf $OSAL_PATH_EXIM_CONF/domains
    mkdir -p $OSAL_PATH_EXIM_CONF/domains

    rm -f /etc/alternatives/mta
    ln -s /usr/sbin/$OSAL_PKG_EXIM /etc/alternatives/mta
    
    osal_service_stop sendmail
    osal_service_disable sendmail
    osal_service_stop postfix
    osal_service_disable postfix

    osal_service_enable $OSAL_PKG_EXIM
    osal_service_start $OSAL_PKG_EXIM
    check_result $? "Exim start failed"

    osal_kv_write $HESTIA/conf/hestia.conf 'MAIL_SYSTEM' 'exim'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'description' 'Hestia MTA (Exim) module'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'variant' 'exim'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'service_name' $OSAL_PKG_EXIM

    exit 0
}
