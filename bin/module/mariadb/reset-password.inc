#!/bin/bash

hestia_module_mariadb_reset-password() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'mydb' && [ ! "$param_force" ]; then
        echo "MySQL/MariaDB database module is not installed. See 'hestia module info mydb'."
        return 1
    fi

    module_variant=$(hestia_module_getvariant 'mydb')
    if [ "$module_variant" != 'mariadb' ] && [ ! "$param_force" ]; then
        echo "The installed database module is not MariaDB. See 'hestia module info mydb'."
        return 1
    fi

    echo "Resetting MariaDB server root password..."

    osal_service_stop $OSAL_SERVICE_MARIADB
    check_result $? "MariaDB stop failed"

    local mariadb_root_password=$(osal_gen_pass)

    mysqld_safe --skip-grant-tables --skip-networking &
    sleep 2
    mysql -e "FLUSH PRIVILEGES;"
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$mariadb_root_password';"
    mysql -e "FLUSH PRIVILEGES;"

    echo -e "[client]\npassword='$mariadb_root_password'\n" > /root/.my.cnf
    chmod 600 /root/.my.cnf

    kill `cat /var/run/mysqld/mysqld.pid`

    osal_service_start $OSAL_SERVICE_MARIADB

    echo "The root password for the MariaDB server is now '$mariadb_root_password'"
}
