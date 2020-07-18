#!/bin/sh

hestia_module_awstats_remove() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled webstats)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "Web stats module is not installed. See 'hestia module info webstats'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant webstats)
    if [ "$module_variant" != 'awstats' ] && [ ! "$param_force" ]; then
        echo "The installed web stats module is not Awstats. See 'hestia module info webstats'."
        exit 1
    fi

    echo "Removing web stats (Awstats) module..."

    hestia_config_backup 'awstats-remove' $OSAL_PATH_AWSTATS_CONF
    osal_package_remove $OSAL_PKG_AWSTATS
    rm -rf $OSAL_PATH_AWSTATS_CONF

    osal_kv_write $HESTIA/conf/hestia.conf 'WEBSTATS_SYSTEM' 'no'
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/webstats.conf 'version' '0'

    exit 0
}
