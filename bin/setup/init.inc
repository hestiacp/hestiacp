#!/bin/bash

hestia_setup_init() {
    echo "Initializing Hestia configuration..."

    if [ ! "$param_lang" ]; then
        param_lang='en'
    fi
    if [ ! "$param_server_name" ]; then
        param_server_name=$(hostname -f)
        read -e -p 'Please enter FQDN hostname: ' -i "$param_server_name" param_server_name
    fi
    if [ ! "${param_admin_email}" ]; then
        param_admin_email="admin@$param_server_name"
        mail_regex="^(([A-Za-z0-9]+((\.|\-|\_|\+)?[A-Za-z0-9]?)*[A-Za-z0-9]+)|[A-Za-z0-9]+)@(([A-Za-z0-9]+)+((\.|\-|\_)?([A-Za-z0-9]+)+)*)+\.([A-Za-z]{2,})+$"
        if [ -z "$param_admin_email" ] || [[ ! $i =~ $mail_regex ]]; then
            read -e -p 'Please enter admin email address: ' -i "$param_admin_email" param_admin_email
        fi
    fi

    mkdir -p $HESTIA/conf $HESTIA/log $HESTIA/ssl $HESTIA/data/ips \
        $HESTIA/data/queue $HESTIA/data/users $HESTIA/data/firewall \
        $HESTIA/data/sessions
    touch $HESTIA/data/queue/backup.pipe $HESTIA/data/queue/disk.pipe \
        $HESTIA/data/queue/webstats.pipe $HESTIA/data/queue/restart.pipe \
        $HESTIA/data/queue/traffic.pipe $HESTIA/log/system.log \
        $HESTIA/log/nginx-error.log $HESTIA/log/auth.log \
        $HESTIA/conf/hestia.conf $HESTIA/log/error.log
    chmod 750 $HESTIA/conf $HESTIA/data/users $HESTIA/data/ips $HESTIA/log
    chmod -R 750 $HESTIA/data/queue
    chmod 660 $HESTIA/conf/hestia.conf
    chmod 660 $HESTIA/log/*
    chmod 770 $HESTIA/data/sessions

    # Setup initial hestia.conf default values (if not already present)
    hestia_config_init_value 'THEME' 'default'
    hestia_config_init_value 'LANGUAGE' 'en'
    hestia_config_init_value 'WEB_SYSTEM' ''
    hestia_config_init_value 'PROXY_SYSTEM' ''
    hestia_config_init_value 'STATS_SYSTEM' ''
    hestia_config_init_value 'WEB_BACKEND' ''
    hestia_config_init_value 'DB_SYSTEM' ''
    hestia_config_init_value 'FTP_SYSTEM' ''
    hestia_config_init_value 'DNS_SYSTEM' ''
    hestia_config_init_value 'MAIL_SYSTEM' ''
    hestia_config_init_value 'ANTIVIRUS_SYSTEM' ''
    hestia_config_init_value 'ANTISPAM_SYSTEM' ''
    hestia_config_init_value 'IMAP_SYSTEM' ''
    hestia_config_init_value 'CRON_SYSTEM' ''
    hestia_config_init_value 'FIREWALL_SYSTEM' ''
    hestia_config_init_value 'FIREWALL_EXTENSION' ''
    hestia_config_init_value 'BACKUP_SYSTEM' 'local'
    hestia_config_init_value 'BACKUP_GZIP' '9'
    hestia_config_init_value 'RELEASE_BRANCH' 'release'
    hestia_config_init_value 'DB_PMA_ALIAS' 'phpmyadmin'
    hestia_config_init_value 'WEBMAIL_ALIAS' 'webmail'
    hestia_config_init_value 'API' 'yes'
    hestia_config_init_value 'BACKEND_PORT' '8083'
    hestia_config_init_value 'FILE_MANAGER' 'false'

    memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])

    if [ "$OS_BASE" = 'rhel' ]; then
        # FIXME: Hestia scripts should not assume ip is located in /bin
        ln -s /usr/sbin/ip /bin/ip > /dev/null 2>&1

        # Compatibility with Debian-only scripts
        groupadd nogroup > /dev/null 2>&1
    fi

    if [ ! -e /var/log/hestia ]; then
        ln -s $HESTIA/log /var/log/hestia
    fi

    # Setup repos
    if [ "$OS_BASE" = 'debian' ]; then
        cat > /etc/apt/sources.list.d/hestia.list <<EOL
# This file was added by Hestia Control Panel.
deb https://apt.hestiacp.com/ $OS_CODENAME main
EOL
        apt-key adv --keyserver keyserver.ubuntu.com --recv-keys A189E93654F0B0E5 > /dev/null 2>&1
    elif [ "$OS_BASE" = 'ubuntu' ]; then
        cat > /etc/apt/sources.list.d/hestia.list <<EOL
# This file was added by Hestia Control Panel.
deb https://apt.hestiacp.com/ $OS_CODENAME main
EOL
        apt-key adv --keyserver keyserver.ubuntu.com --recv-keys A189E93654F0B0E5 > /dev/null 2>&1
    elif [ "$OS_BASE" = 'rhel' ]; then
        echo " - Adding repositories and packages..."
        dnf -q config-manager --set-enabled extras powertools > /dev/null 2>&1
        dnf -q config-manager --set-enabled extras PowerTools > /dev/null 2>&1
        dnf -y -q install epel-release
        dnf -q config-manager --set-enabled epel epel-modular > /dev/null 2>&1

        # Install needed packages
        dnf -y -q install crudini

        # Disable SELinux
        setenforce 0
        crudini --set /etc/selinux/config '' SELINUX permissive

        # Enable Perl 5.26
        #dnf -y module disable perl:*
        #dnf -y module enable perl:5.26

        cat > "/etc/yum.repos.d/hestia.repo" <<EOL
# This file was added by Hestia Control Panel.
#[hestia]
#name=Hestia Control Panel
#baseurl=http://rpm.hestiacp.com/packages/centos/\$releasever/\$basearch/
#gpgcheck=0
#enabled=1
#gpgkey=https://rpm.hestiacp.com/keys/hestia.key
EOL
    fi

    HESTIA_VERSION=$(osal_package_getversion 'hestia')
    osal_kv_write $HESTIA/conf/hestia.conf 'VERSION' $HESTIA_VERSION

    

    echo " - Installing hosting packages and templates..."
    cp -rf $HESTIA_INSTALL_DIR/packages $HESTIA/data/
    cp -rf $HESTIA_INSTALL_DIR/templates $HESTIA/data/

    # General OS
    
    # Add nologin as a valid system shell
    if [ -z "$(grep nologin /etc/shells)" ]; then
        echo "/usr/sbin/nologin" >> /etc/shells
    fi

    lang=$(osal_kv_read $HESTIA/conf/hestia.conf 'LANGUAGE' '')
    if [ ! "$lang" ] || [ "$lang" != "$param_lang" ]; then
        osal_kv_write $HESTIA/conf/hestia.conf 'LANGUAGE' $param_lang
        lang=$param_lang
    fi

    # Default values
    if [ ! "$DB_PMA_ALIAS" ]; then
        osal_kv_write $HESTIA/conf/hestia.conf 'DB_PMA_ALIAS' 'phpMyAdmin'
    fi
    # Default values
    if [ ! "$DB_PMA_ALIAS" ]; then
        osal_kv_write $HESTIA/conf/hestia.conf 'DB_PGA_ALIAS' 'phpPgAdmin'
    fi

    if ! id "admin" &>/dev/null; then
        echo " - Creating admin user..."

        if [ ! "$param_admin_password" ]; then
            param_admin_password=$(osal_gen_pass)
        fi
        $HESTIA/bin/v-add-user admin ${param_admin_password} ${param_admin_email} default System Administrator
        $HESTIA/bin/v-change-user-shell admin nologin
        $HESTIA/bin/v-change-user-language admin $lang

        echo "    Username        :   admin"
        echo "    Password        :   $param_admin_password"
        echo "    E-mail          :   $param_admin_email"
    fi

    chown admin:admin $HESTIA/data/sessions
    chown admin:admin $HESTIA/php/var/log
    chown admin:admin $HESTIA/php/var/run

    echo " - Configuring system environment..."
    echo "export HESTIA='$HESTIA'" > /etc/profile.d/hestia.sh
    echo 'PATH=$PATH:'$HESTIA'/bin' >> /etc/profile.d/hestia.sh
    echo 'export PATH' >> /etc/profile.d/hestia.sh
    chmod 755 /etc/profile.d/hestia.sh
    source /etc/profile.d/hestia.sh

    echo " - Installing sudo configuration..."
    mkdir -p /etc/sudoers.d
    cp -f $HESTIA_INSTALL_DIR/sudo/admin /etc/sudoers.d/
    chmod 440 /etc/sudoers.d/admin

    echo " - Configuring logrotate for Hestia logs..."
    cp -f $HESTIA_INSTALL_DIR/logrotate/hestia /etc/logrotate.d/

    echo " - Installing firewall rules..."
    cp -rf $HESTIA_INSTALL_DIR/firewall $HESTIA/data/

    echo " - Configuring server hostname..."
    echo "    Server name     :   $param_server_name"
    $HESTIA/bin/v-change-sys-hostname $param_server_name > /dev/null 2>&1

    if [ ! -f $HESTIA/ssl/certificate.crt ]; then
        echo " - Generating default self-signed SSL certificate..."
        $HESTIA/bin/v-generate-ssl-cert $(hostname) $param_admin_email 'US' 'California' \
            'San Francisco' 'Hestia Control Panel' 'IT' > $HESTIA/ssl/tmp.pem

        # Parsing certificate file
        crt_end=$(grep -n "END CERTIFICATE-" $HESTIA/ssl/tmp.pem |cut -f 1 -d:)
        key_start=$(grep -n "BEGIN RSA" $HESTIA/ssl/tmp.pem |cut -f 1 -d:)
        key_end=$(grep -n  "END RSA" $HESTIA/ssl/tmp.pem |cut -f 1 -d:)

        # Adding SSL certificate
        echo " - Adding SSL certificate to Hestia Control Panel..."
        sed -n "1,${crt_end}p" $HESTIA/ssl/tmp.pem > $HESTIA/ssl/certificate.crt
        sed -n "$key_start,${key_end}p" $HESTIA/ssl/tmp.pem > $HESTIA/ssl/certificate.key
        chown root:mail $HESTIA/ssl/*
        chmod 660 $HESTIA/ssl/*
        rm $HESTIA/ssl/tmp.pem
    fi

    if [ -z "$(swapon -s)" ] && [ $memory -lt 1000000 ]; then
        echo " - Creating 1 GB swap file..."
        fallocate -l 1G /swapfile
        chmod 600 /swapfile
        mkswap /swapfile
        swapon /swapfile
        echo "/swapfile   none    swap    sw    0   0" >> /etc/fstab
    fi

    if [ ! -f /etc/ssl/dhparam.pem ]; then
        cp -f $HESTIA_INSTALL_DIR/ssl/dhparam.pem /etc/ssl/
    fi

    echo " - Configuring system IP addresses..."
    $HESTIA/bin/v-update-sys-ip > /dev/null 2>&1

    hestia module install php

    echo " - Enabling and starting Hestia service..."
    osal_service_enable hestia
    osal_service_restart hestia
}

# Writes a value only if it does not exists
hestia_config_init_value() {
    if ! osal_kv_haskey $HESTIA/conf/hestia.conf $1; then
        osal_kv_write $HESTIA/conf/hestia.conf $1 $2
    fi   
}