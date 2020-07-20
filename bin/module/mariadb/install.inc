#!/bin/bash

hestia_module_mariadb_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'mydb' && [ ! "$param_force" ]; then
        echo "MySQL/MariaDB database module is already installed. See 'hestia module info mydb'."
        return 1
    fi

    echo "Installing database (MariaDB) module..."
    mariadb_v='10.4'

    if [ "$OS_TYPE" == 'ubuntu' ]; then
        echo "deb [arch=amd64] http://ams2.mirrors.digitalocean.com/mariadb/repo/$mariadb_v/$OS_TYPE $OS_CODENAME main" > $apt/mariadb.list
        APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0xF1656F24C74CD1D8 > /dev/null 2>&1
    elif [ "$OS_TYPE" == 'debian' ]; then
        echo "deb [arch=amd64] http://ams2.mirrors.digitalocean.com/mariadb/repo/$mariadb_v/$OS_TYPE $OS_CODENAME main" > $apt/mariadb.list
        if [ "$OS_VERSION" -eq 8 ]; then
            APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key adv --recv-keys --keyserver keyserver.ubuntu.com CBCB082A1BB943DB > /dev/null 2>&1
        else
            APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key adv --recv-keys --keyserver keyserver.ubuntu.com F1656F24C74CD1D8 > /dev/null 2>&1
        fi
    else
        
    fi




    osal_service_stop $OSAL_SERVICE_MARIADB > /dev/null 2>&1
    hestia_config_backup 'mariadb-install' $OSAL_PATH_MARIADB_CONF

    osal_package_preinstall
    osal_package_install $OSAL_PKG_MARIADB

    gpasswd -a $OSAL_USER_MARIADB mail > /dev/null 2>&1

    if [ "$OS_BASE" = 'debian' ]; then
        cp -f $HESTIA_INSTALL_DIR/mariadb/${OSAL_PKG_MARIADB}.conf.template $OSAL_PATH_MARIADB_CONF/
        chmod 640 $OSAL_PATH_MARIADB_CONF/${OSAL_PKG_MARIADB}.conf.template
    else
        cp -f $HESTIA_INSTALL_DIR/mariadb/${OSAL_PKG_MARIADB}.conf $OSAL_PATH_MARIADB_CONF/
        chmod 640 $OSAL_PATH_MARIADB_CONF/${OSAL_PKG_MARIADB}.conf
    fi

    cp -f $HESTIA_INSTALL_DIR/mariadb/dnsbl.conf $OSAL_PATH_MARIADB_CONF/
    cp -f $HESTIA_INSTALL_DIR/mariadb/spam-blocks.conf $OSAL_PATH_MARIADB_CONF/
    touch $OSAL_PATH_MARIADB_CONF/white-blocks.conf
    touch $OSAL_PATH_MARIADB_CONF/mailhelo.conf

    hestia_safe_rm $OSAL_PATH_MARIADB_CONF/domains
    mkdir -p $OSAL_PATH_MARIADB_CONF/domains

    hestia_safe_rm /etc/alternatives/mydb
    ln -s /usr/sbin/$OSAL_PKG_MARIADB /etc/alternatives/mydb
    
    osal_service_stop sendmail > /dev/nul 2>&1
    osal_service_disable sendmail > /dev/nul 2>&1
    osal_service_stop postfix > /dev/nul 2>&1
    osal_service_disable postfix > /dev/nul 2>&1

    osal_service_enable $OSAL_SERVICE_MARIADB
    osal_service_start $OSAL_SERVICE_MARIADB
    check_result $? "Mariadb start failed"

    osal_kv_write $HESTIA/conf/hestia.conf 'DB_SYSTEM' 'mysql'
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'description' 'Hestia Database (MariaDB) module'
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'variant' 'mariadb'
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/mydb.conf 'service_name' $OSAL_PKG_MARIADB

    # Rebuild mail
    for user in $($HESTIA/bin/v-list-sys-users plain); do
        echo "Rebuilding mail domains for user $user"
        $BIN/v-rebuild-mail-domains $user 'no' >/dev/null 2>&1
    done

    # Setup ClamAv integration
    clamav_installed=$(hestia_module_variant_installed 'antivirus' 'clamav')
    if [ "$clamav_installed" ]; then
        $BIN/hestia module clamav mariadb-integration enable
    fi

    # Setup SpamAssassin integration
    spamassassin_installed=$(hestia_module_variant_installed 'antispam' 'spamassassin')
    if [ "$spamassassin_installed" ]; then
        $BIN/hestia module spamassassin mariadb-integration enable
    fi
}
