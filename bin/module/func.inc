#!/bin/bash

HESTIA_CONF_MODULES=$HESTIA/conf/modules
mkdir -p $HESTIA_CONF_MODULES

# Tests if module is installed, exits successfully if it does.
hestia_module_isinstalled() {
    osal_kv_read_bool $HESTIA_CONF_MODULES/${1}.conf 'installed'
}

# Returns a module version
hestia_module_getversion() {
    osal_kv_read $HESTIA_CONF_MODULES/${1}.conf 'version'
}

# Returns module installed variant
hestia_module_getvariant() {
    osal_kv_read $HESTIA_CONF_MODULES/${1}.conf 'variant'
}

# Tests if a specific variant of a module is installed,
# exits successfully if it does.
hestia_module_variant_installed() {
    local module_installed=$(hestia_module_isinstalled $1 && echo 1)
    if [ "$module_installed" ]; then
        local module_variant=$(hestia_module_getvariant $1)
        if [ "$module_variant" = "$2" ]; then
            return 0
        fi
    fi
    return 1
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
    local dest=$HESTIA/data/backup/
    local filename=${1}-$(date +%Y%m%d%H%M%S)
    shift

    mkdir -p $dest
    tar -zc --warning=none --ignore-failed-read -f $dest/${filename}.tar.gz $@ > /dev/null 2>&1
}
