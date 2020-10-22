#!/bin/bashh

hestia_module_awstats_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'webstats' && [ ! "$param_force" ]; then
        echo "Stats module is already installed. See 'hestia module info webstats'."
        return 0
    fi

    echo "Installing web stats (AWStats) module..."


    hestia_config_backup 'awstats-install' $OSAL_PATH_AWSTATS_CONF /etc/cron.d/awstats

    osal_package_preinstall
    osal_package_install $OSAL_PKG_AWSTATS

    # Disable AWStats cron
    hestia_safe_rm /etc/cron.d/awstats

    osal_kv_write $HESTIA/conf/hestia.conf 'STATS_SYSTEM' 'awstats'
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'description' 'Hestia web stats (AWStats) module'
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'variant' 'awstats'
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'version' '1'

    return 0
}
