#!/bin/bash

hestia_setup_init() {
    echo "Initializing Hestia configuration..."
    mkdir -p $HESTIA/conf $HESTIA/log $HESTIA/ssl $HESTIA/data/ips \
        $HESTIA/data/queue $HESTIA/data/users $HESTIA/data/firewall \
        $HESTIA/data/sessions
    touch $HESTIA/data/queue/backup.pipe $HESTIA/data/queue/disk.pipe \
        $HESTIA/data/queue/webstats.pipe $HESTIA/data/queue/restart.pipe \
        $HESTIA/data/queue/traffic.pipe $HESTIA/log/system.log \
        $HESTIA/log/nginx-error.log $HESTIA/log/auth.log \
        $HESTIA/conf/hestia.conf
    chmod 750 $HESTIA/conf $HESTIA/data/users $HESTIA/data/ips $HESTIA/log
    chmod -R 750 $HESTIA/data/queue
    chmod 660 $HESTIA/conf/hestia.conf
    chmod 660 $HESTIA/log/*
    chmod 770 $HESTIA/data/sessions

    if [ ! -e /var/log/hestia ]; then
        ln -s $HESTIA/log /var/log/hestia
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

    if [ ! "$param_lang" ]; then
        param_lang='en'
    fi
    if [ ! "$param_server_name" ]; then
        param_server_name=$(hostname -f)
    fi
    if [ ! "${param_admin-email}" ]; then
        param_admin_email="admin@$param_server_name"
    fi

    if ! id "admin" &>/dev/null; then
        echo " - Creating admin user..."

        if [ ! "$param_admin_password" ]; then
            param_admin_password=$(osal_gen_pass)
        fi
        $HESTIA/bin/v-add-user admin ${param_admin_password} ${param_admin_email} default System Administrator
        $HESTIA/bin/v-change-user-shell admin nologin
        $HESTIA/bin/v-change-user-language admin $param_lang

        echo "    Username        :   admin"
        echo "    Password        :   $param_admin_password"
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
    $HESTIA/bin/v-update-sys-ip

    echo " - Enabling and starting Hestia service..."
    osal_service_enable hestia
    osal_service_restart hestia
}