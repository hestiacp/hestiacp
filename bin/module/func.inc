#!/bin/bash

HESTIA_CONF_MODULES=$HESTIA/conf/modules
mkdir -p $HESTIA_CONF_MODULES

# Returns 1 if module is installed
hestia_module_isinstalled() {
    osal_kv_read_bool $HESTIA_CONF_MODULES/${1}.conf 'installed'
}

# Returns 1 if module is installed and enabled
hestia_module_isenabled() {
    is_installed=$(osal_kv_read_bool $HESTIA_CONF_MODULES/${1}.conf 'installed')
    is_enabled=$(osal_kv_read_bool $HESTIA_CONF_MODULES/${1}.conf 'enabled')
    if [ "$is_installed" ] && [ "$is_enabled" ]; then
        echo 1
    fi
}

hestia_module_getversion() {
    osal_kv_read $HESTIA_CONF_MODULES/${1}.conf 'version'
}

hestia_module_getvariant() {
    osal_kv_read $HESTIA_CONF_MODULES/${1}.conf 'variant'
}

hestia_module_variant_installed() {
    module_installed=$(hestia_module_isinstalled $1)
    if [ "$module_installed" ]; then
        module_variant=$(hestia_module_getvariant $1)
        if [ "$module_variant" = "$2" ]; then
            echo 1
        fi
    fi
}

# Schedules a service restart (when doing multiple actions)
hestia_module_service_restart_schedule() {
    if [ ! "$hestia_module_service_restart_list" ]; then
        hestia_module_service_restart_list=$1
    else
        # FIXME: don't add services twice
        hestia_module_service_restart_list="hestia_module_service_restart_list $1"
    fi
}

# Restart services scheduled for restart
hestia_module_service_restart_restart() {
    if [ ! "$hestia_module_service_restart_list" ]; then
        for svc in $hestia_module_service_restart_list; do
            osal_service_restart $svc
        done
    fi
}

# Backup config files (usually prior to module install)
# hestia_config_backup 'prefix' file1 file2 file3 ...
hestia_config_backup() {
    dest=$HESTIA/data/backup/
    filename=${1}-$(date +%Y%m%d%H%M%S)
    shift

    mkdir -p $dest
    tar -zc --warning=none --ignore-failed-read -f $dest/${filename}.tar.gz $@ > /dev/null 2>&1
}
