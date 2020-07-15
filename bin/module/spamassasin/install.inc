#!/bin/sh

hestia_module_spamassassin_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled antispam)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "Antispam module is already installed. See 'hestia module info antispam'."
        exit 1
    fi

    echo "Installing Antispam (SpamAssassin) module..."

    osal_service_stop $OSAL_SERVICE_SPAMASSASSIN
    hestia_config_backup 'spamassassin-install' $OSAL_PATH_SPAMASSASSIN_CONF

    osal_package_preinstall
    osal_package_install $OSAL_PKG_SPAMASSASSIN

    if [ $OS_BASE = 'debian' ]; then
        sed -i "s/ENABLED=0/ENABLED=1/" /etc/default/spamassassin
        unit_files="$(systemctl list-unit-files |grep spamassassin)"
        if [[ "$unit_files" =~ "disabled" ]]; then
            systemctl enable spamassassin > /dev/null 2>&1
        fi
    fi

    osal_service_enable $OSAL_SERVICE_SPAMASSASSIN
    osal_service_start $OSAL_SERVICE_SPAMASSASSIN
    check_result $? "SpamAssassin start failed"

    exim_installed=$(hestia_module_variant_installed 'mta' 'exim')
    if [ "$exim_installed" ]; then
        hestia module spamassassin exim-integration enable
    fi

    osal_kv_write $HESTIA/conf/hestia.conf 'ANTISPAM_SYSTEM' 'spamassassin'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'description' 'Hestia Antispam (SpamAssassin) module'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'variant' 'spamassassin'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antispam.conf 'service_name' $OSAL_SERVICE_SPAMASSASSIN
}