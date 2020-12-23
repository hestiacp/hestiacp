#!/bin/bash

hestia_module_clamav_install() {
    source $HESTIA/bin/module/func.inc

    if hestia_module_isinstalled 'antivirus' && [ ! "$param_force" ]; then
        echo "Antivirus module is already installed. See 'hestia module info antivirus'."
        return 0
    fi

    echo "Installing antivirus (ClamAV) module..."

    osal_service_stop $OSAL_SERVICE_CLAMAV > /dev/null 2>&1
    hestia_config_backup 'clamav-install' $OSAL_PATH_CLAMAV_CONF $OSAL_PATH_CLAMAV_CONF_D

    osal_package_preinstall
    osal_package_install $OSAL_PKG_CLAMAV

    cp -f $HESTIA_INSTALL_DIR/clamav/clamd.conf $OSAL_PATH_CLAMAV_CONF

    if [ $OS_BASE = 'debian' ]; then
        # FIXME: Why do we do this?
        if [ -e "/lib/systemd/system/clamav-daemon.service" ]; then
            exec_pre1='ExecStartPre=-/bin/mkdir -p /var/run/clamav'
            exec_pre2='ExecStartPre=-/bin/chown -R clamav:clamav /var/run/clamav'
            sed -i "s|\[Service\]/|[Service]\n$exec_pre1\n$exec_pre2|g" \
                /lib/systemd/system/clamav-daemon.service
            systemctl daemon-reload
        fi
    elif [ $OS_BASE = 'rhel' ]; then
        # Setup freshclam
        groupadd clamav 2>/dev/null
        useradd clamav -g clamav -s /sbin/nologin -d /var/lib/clamav 2>/dev/null
        cp -f $HESTIA_INSTALL_DIR/clamav/freshclam.conf /etc/
        chown -R clamav:clamav /var/lib/clamav
        chown root:clamav /etc/freshclam.conf
        chmod 0664 /etc/freshclam.conf
        cp -f $HESTIA_INSTALL_DIR/clamav/clamd.service /etc/systemd/system/
        systemctl daemon-reload
    fi

    mkdir -p /var/log/clamav /var/run/clamav
    chown -R $OSAL_USER_CLAMAV:$OSAL_USER_CLAMAV /var/log/clamav /var/run/clamav

    echo "Updating virus database..."
    osal_execute_with_spinner /usr/bin/freshclam

    osal_service_enable $OSAL_SERVICE_CLAMAV
    osal_service_start $OSAL_SERVICE_CLAMAV
    check_result $? "ClamAV start failed"

    osal_kv_write $HESTIA/conf/hestia.conf 'ANTIVIRUS_SYSTEM' 'clamav-daemon'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'installed' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'description' 'Hestia Antivirus (ClamAV) module'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'enabled' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'variant' 'clamav'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'version' '1'
    osal_kv_write $HESTIA_CONF_MODULES/antivirus.conf 'service_name' $OSAL_SERVICE_CLAMAV

    osal_enqueue_integrate clamav
}