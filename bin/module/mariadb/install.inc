#!/bin/bash

hestia_module_mariadb_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'mydb' && [ ! "$param_force" ]; then
        echo "MySQL/MariaDB database module is already installed. See 'hestia module info mydb'."
        return 0
    fi

    echo "Installing database (MariaDB) module..."
    local hmd="$HESTIA_INSTALL_DIR/../modules/mariadb.hmd"
    local mariadb_version=$(osal_kv_read $hmd 'version')

    osal_service_stop $OSAL_SERVICE_MARIADB > /dev/null 2>&1
    hestia_config_backup 'mariadb-install' $OSAL_PATH_MARIADB_CONF $OSAL_DIR_MARIADB_CONF_D $OSAL_PATH_MARIADB_DATA

    # Setup repos
    if [ "$OS_BASE" = 'debian' ]; then
        cat > /etc/apt/sources.list.d/mariadb.list <<EOL
# This file was added by Hestia Control Panel.
deb [arch=amd64] https://mirror.mva-n.net/mariadb/repo/$mariadb_version/debian $OS_CODENAME main
EOL
        apt-key adv --fetch-keys 'https://mariadb.org/mariadb_release_signing_key.asc' > /dev/null 2>&1
    elif [ "$OS_BASE" = 'ubuntu' ]; then
        cat > /etc/apt/sources.list.d/mariadb.list <<EOL
# This file was added by Hestia Control Panel.
deb [arch=amd64] https://mirror.mva-n.net/mariadb/repo/$mariadb_version/ubuntu $OS_CODENAME main
EOL
        if [ "$OS_VERSION" = '16.04' ]; then 
            apt-key adv --fetch-keys 'http://mariadb.org/mariadb_release_signing_key.asc' > /dev/null 2>&1
        else
            apt-key adv --fetch-keys 'https://mariadb.org/mariadb_release_signing_key.asc' > /dev/null 2>&1
        fi
    elif [ "$OS_BASE" = 'rhel' ]; then
        # Remove conflicting OS-provided packages
        osal_package_remove mariadb-server mariadb-gssapi-server

        cat > "/etc/yum.repos.d/mariadb.repo" <<EOL
# This file was added by Hestia Control Panel.
[mariadb]
name = MariaDB
baseurl = http://yum.mariadb.org/${mariadb_version}/${OS_TYPE}${OS_VERSION}-amd64
module_hotfixes=1
gpgkey=https://yum.mariadb.org/RPM-GPG-KEY-MariaDB
gpgcheck=1
EOL
    fi

    osal_package_preinstall
    osal_package_install ${OSAL_PKG_MARIADB}-server ${OSAL_PKG_MARIADB}-client ${OSAL_PKG_MARIADB}-common

    local memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])
    local mycnf="my-small.cnf"
    if [ $memory -gt 1200000 ]; then
        mycnf="my-medium.cnf"
    fi
    if [ $memory -gt 3900000 ]; then
        mycnf="my-large.cnf"
    fi

    # Configuring MariaDB
    cp -f $HESTIA_INSTALL_DIR/mysql/$mycnf $OSAL_DIR_MARIADB_CONF/my.cnf
    if [ ! -d $OSAL_PATH_MARIADB_DATA ]; then
        mysql_install_db > /dev/null
    fi

    mkdir -p /var/run/mysqld
    chown mysql:mysql /var/run/mysqld

    osal_service_enable $OSAL_SERVICE_MARIADB
    osal_service_start $OSAL_SERVICE_MARIADB

    if [ ! -f /root/.my.cnf ]; then
        # If /root/.my.cnf does not exist, secure MariaDB by setting root password
        local mariadb_root_password=$(osal_gen_pass)
        mysqladmin -u root password $mariadb_root_password
        echo -e "[client]\npassword='$mariadb_root_password'\n" > /root/.my.cnf
        chmod 600 /root/.my.cnf
    fi

    # Add this host to MySQL server list
    if [ -e $HESTIA/conf/mysql.conf ]; then
        # Remove previous line
        sed -i "/HOST='localhost'/d" $HESTIA/conf/mysql.conf
    fi
    echo "HOST='localhost' USER='root' PASSWORD='$mariadb_root_password' CHARSETS='UTF8,LATIN1,WIN1250,WIN1251,WIN1252,WIN1256,WIN1258,KOI8' MAX_DB='500' U_SYS_USERS='' U_DB_BASES='0' SUSPENDED='no' TIME='$(date +%H:%M:%S)' DATE='$(date +%Y-%m-%d)' PORT='3306'" >> $HESTIA/conf/mysql.conf

    mysql_upgrade > /dev/null

    # Clear MariaDB test users and databases
    # FIXME: MariaDB 10.4 and up use global_priv
    mysql -e "DELETE FROM mysql.user WHERE User=''" > /dev/null 2>&1
    mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%'" > /dev/null 2>&1
    mysql -e "DELETE FROM mysql.user WHERE user='';" > /dev/null 2>&1
    mysql -e "DELETE FROM mysql.user WHERE password='' AND authentication_string='';" > /dev/null 2>&1
    mysql -e "DROP DATABASE test" > /dev/null 2>&1

    osal_kv_write $HESTIA/conf/hestia.conf 'DB_SYSTEM' 'mysql'
}
