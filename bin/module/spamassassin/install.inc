#!/bin/bash

hestia_module_spamassassin_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'antispam' && [ ! "$param_force" ]; then
        echo "Antispam module is already installed. See 'hestia module info antispam'."
        return 0
    fi

    echo "Installing Antispam (SpamAssassin) module..."

    osal_service_stop $OSAL_SERVICE_SPAMASSASSIN > /dev/null 2>&1
    hestia_config_backup 'spamassassin-install' $OSAL_PATH_SPAMASSASSIN_CONF

    osal_package_preinstall
    osal_package_install $OSAL_PKG_SPAMASSASSIN

    if [ $OS_BASE = 'debian' ]; then
        sed -i "s/ENABLED=0/ENABLED=1/" /etc/default/spamassassin
        unit_files="$(systemctl list-unit-files |grep spamassassin)"
        if [[ "$unit_files" =~ "disabled" ]]; then
            systemctl enable spamassassin > /dev/null 2>&1
        fi
    elif [ $OS_BASE = 'rhel' ]; then
        # Set SpamAssassin to listen on IPv4 localhost
        spamd_options=$(osal_kv_read '/etc/sysconfig/spamassassin' 'SPAMDOPTIONS')
        spamd_options="$spamd_options --listen-ip=127.0.0.1"
        osal_kv_write '/etc/sysconfig/spamassassin' 'SPAMDOPTIONS' "$spamd_options"
    fi

    osal_service_enable $OSAL_SERVICE_SPAMASSASSIN > /dev/null 2>&1
    osal_service_start $OSAL_SERVICE_SPAMASSASSIN > /dev/null 2>&1
    check_result $? "SpamAssassin start failed"

    osal_kv_write $HESTIA/conf/hestia.conf 'ANTISPAM_SYSTEM' 'spamassassin'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'description' 'Hestia Antispam (SpamAssassin) module'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'variant' 'spamassassin'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'service_name' $OSAL_SERVICE_SPAMASSASSIN

    osal_enqueue_integrate spamassassin
}