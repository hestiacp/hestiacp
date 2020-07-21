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

    # Start MariaDB in safe mode, without grant tables
    mysqld_safe --skip-grant-tables --skip-networking & > /dev/null
    sleep 2

    # Reset root password
    mysql -e "FLUSH PRIVILEGES;" > /dev/null
    mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$mariadb_root_password';" > /dev/null
    mysql -e "FLUSH PRIVILEGES;" > /dev/null

    # Update MariaDB client config file
    echo -e "[client]\npassword='$mariadb_root_password'\n" > /root/.my.cnf
    chmod 600 /root/.my.cnf

    # Update Hestia server config file
    sed -i "/HOST='localhost'/ s/\s*PASSWORD='[^']*'\s*/ PASSWORD='$mariadb_root_password' /g" $HESTIA/conf/mysql.conf

    # Kill mysqld_safe and restart MariaDB service
    kill `cat /var/run/mysqld/mysqld.pid`
    osal_service_start $OSAL_SERVICE_MARIADB

    echo "The root password for the MariaDB server is now '$mariadb_root_password'"
}
