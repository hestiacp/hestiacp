#!/bin/bash

hestia_module_mariadb_upgrade() {
    source $HESTIA/bin/module/func.inc

    if ! hestia_module_isinstalled 'mariadb' && [ ! "$param_force" ]; then
        echo "MariaDB database module is not installed."
        return 0
    fi

    # TODO: check version before upgrade

    local hmd="$HESTIA_INSTALL_DIR/../modules/mariadb.hmd"
    local mariadb_version=$(osal_kv_read $hmd 'version')

    echo "Upgrading MariaDB to version ${mariadb_version}..."

    osal_service_stop $OSAL_SERVICE_MARIADB > /dev/null 2>&1
    hestia_config_backup 'mariadb-upgrade' $OSAL_PATH_MARIADB_CONF $OSAL_DIR_MARIADB_CONF_D
    cp -f /etc/my.cnf /etc/my.cnf.upg

    osal_package_remove ${OSAL_PKG_MARIADB}-server

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

    cp -f /etc/my.cnf.upg /etc/my.cnf
    rm -f /etc/my.cnf.upg

    osal_service_enable $OSAL_SERVICE_MARIADB
    osal_service_start $OSAL_SERVICE_MARIADB

    mysql_upgrade > /dev/null
}
