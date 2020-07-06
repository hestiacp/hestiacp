#!/bin/sh

# Returns 1 if module is installed
hestia_module_isinstalled() {
    osal_ini_get $HESTIA/conf/modules.conf $1 'installed' '1'
}

# Returns 1 if module is installed and enabled
hestia_module_isenabled() {
    is_installed=$(osal_ini_get $HESTIA/conf/modules.conf $1 'installed' '1')
    is_enabled=$(osal_ini_get $HESTIA/conf/modules.conf $1 'enabled' '1')
    if [[ "$is_installed" && "$is_enabled" ]]; then
        echo 1
    fi
}

hestia_module_getversion() {
    osal_ini_get $HESTIA/conf/modules.conf $1 'version'
}

hestia_module_getvariant() {
    osal_ini_get $HESTIA/conf/modules.conf $1 'variant'
}

# Backup config files (usually prior to module install)
hestia_config_backup() {
    dest=$HESTIA/data/backups/$1
    shift

    mkdir -p $dest
    cp -f $@ $dest
}
