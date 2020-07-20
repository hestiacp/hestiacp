#!/bin/bash

hestia_module_mariadb_remove() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'mydb' && [ ! "$param_force" ]; then
        echo "MySQL/MariaDB database module is not installed. See 'hestia module info mydb'."
        return 1
    fi

    module_variant=$(hestia_module_getvariant mydb)
    if [ "$module_variant" != 'mariadb' ] && [ ! "$param_force" ]; then
        echo "The installed database module is not MariaDB. See 'hestia module info mydb'."
        return 1
    fi

    echo "Removing database (MariaDB) module..."

    osal_service_stop $OSAL_SERVICE_MARIADB > /dev/null 2>&1
    osal_service_disable $OSAL_SERVICE_MARIADB > /dev/null 2>&1

    hestia_config_backup 'mariadb-remove' $OSAL_PATH_MARIADB_CONF $OSAL_DIR_MARIADB_CONF_D

    osal_package_remove ${OSAL_PKG_MARIADB}-server

    osal_kv_write $HESTIA/conf/hestia.conf 'DB_SYSTEM' ''
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'installed' '0'
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'description' ''
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'enabled' '0'
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'variant' ''
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'version' '0'
}
