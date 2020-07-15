#!/bin/sh

hestia_module_clamav_install() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled antivirus)
    if [ "$module_installed" ] && [ ! "$param_force" ]; then
        echo "Antivirus module is already installed. See 'hestia module info antivirus'."
        exit 1
    fi

    echo "Installing Antivirus (ClamAV) module..."

    osal_service_stop $OSAL_SERVICE_CLAMAV
    hestia_config_backup 'clamav-install' $OSAL_PATH_CLAMAV_CONF

    osal_package_preinstall
    osal_package_install $OSAL_PKG_CLAMAV

    cp -f $HESTIA_INSTALL_DIR/clamav/clamd.conf $OSAL_PATH_CLAMAV_CONF

    mkdir -p /var/log/clamav /var/run/clamav
    chown -R clamav:clamav /var/log/clamav /var/run/clamav

    if [ $OS_BASE = 'debian' ]; then
        if [ -e "/lib/systemd/system/clamav-daemon.service" ]; then
            exec_pre1='ExecStartPre=-/bin/mkdir -p /var/run/clamav'
            exec_pre2='ExecStartPre=-/bin/chown -R clamav:clamav /var/run/clamav'
            sed -i "s|\[Service\]/|[Service]\n$exec_pre1\n$exec_pre2|g" \
                /lib/systemd/system/clamav-daemon.service
            systemctl daemon-reload
        fi
    elif [ $OS_BASE = 'rhel' ]; then
        useradd clamav -g clamupdate -s /sbin/nologin -d /var/lib/clamav 2>/dev/null
        cp -f $HESTIA_INSTALL_DIR/clamav/freshclam.conf /etc/
        chown clamav:clamupdate /var/log/clamav /var/run/clamav
        chown -R clamav:clamupdate /var/lib/clamav
        chmod 0775 /var/lib/clamav /var/log/clamav
    fi

    osal_execute_with_spinner /usr/bin/freshclam

    exim_installed=$(hestia_module_variant_installed 'mta' 'exim')
    if [ "$exim_installed" ]; then
        hestia module clamav exim-integration enable
    fi

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
}