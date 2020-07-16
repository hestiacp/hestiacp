#!/bin/bash

hestia_module_exim_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled mta)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "MTA module is already installed. See 'hestia module info mta'."
        exit 1
    fi

    echo "Installing MTA (Exim) module..."

    osal_service_stop $OSAL_SERVICE_EXIM > /dev/null 2>&1
    hestia_config_backup 'exim-install' $OSAL_PATH_EXIM_CONF

    osal_package_preinstall
    osal_package_install $OSAL_PKG_EXIM

    gpasswd -a $OSAL_USER_EXIM mail > /dev/null 2>&1

    if [ "$OS_BASE" = 'debian' ]; then
        cp -f $HESTIA_INSTALL_DIR/exim/${OSAL_PKG_EXIM}.conf.template $OSAL_PATH_EXIM_CONF/
        chmod 640 $OSAL_PATH_EXIM_CONF/${OSAL_PKG_EXIM}.conf.template
    else
        cp -f $HESTIA_INSTALL_DIR/exim/${OSAL_PKG_EXIM}.conf $OSAL_PATH_EXIM_CONF/
        chmod 640 $OSAL_PATH_EXIM_CONF/${OSAL_PKG_EXIM}.conf
    fi

    cp -f $HESTIA_INSTALL_DIR/exim/dnsbl.conf $OSAL_PATH_EXIM_CONF/
    cp -f $HESTIA_INSTALL_DIR/exim/spam-blocks.conf $OSAL_PATH_EXIM_CONF/
    touch $OSAL_PATH_EXIM_CONF/white-blocks.conf
    touch $OSAL_PATH_EXIM_CONF/mailhelo.conf

    rm -rf $OSAL_PATH_EXIM_CONF/domains
    mkdir -p $OSAL_PATH_EXIM_CONF/domains

    rm -f /etc/alternatives/mta
    ln -s /usr/sbin/$OSAL_PKG_EXIM /etc/alternatives/mta
    
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
    osal_kv_write $HESTIA_CONF_MODULES/mta.conf 'service_name' $OSAL_PKG_EXIM

    # Rebuild mail
    for user in $($HESTIA/bin/v-list-sys-users plain); do
        echo "Rebuilding mail domains for user $user"
        $BIN/v-rebuild-mail-domains $user 'no' >/dev/null 2>&1
    done

    # Setup ClamAv integration
    clamav_installed=$(hestia_module_variant_installed 'antivirus' 'clamav')
    if [ "$clamav_installed" ]; then
        $BIN/hestia module clamav exim-integration enable
    fi

    # Setup SpamAssassin integration
    spamassassin_installed=$(hestia_module_variant_installed 'antispam' 'spamassassin')
    if [ "$spamassassin_installed" ]; then
        $BIN/hestia module spamassassin exim-integration enable
    fi
}
