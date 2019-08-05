#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.1.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Set default theme
if [ -z $THEME ]; then
    echo "(*) Enabling support for themes..."
    $BIN/v-change-sys-theme 'default'
fi

# Reduce SSH login grace time
if [ -e /etc/ssh/sshd_config ]; then
    echo "(*) Hardening SSH daemon configuration..."
    sed -i "s/LoginGraceTime 2m/LoginGraceTime 1m/g" /etc/ssh/sshd_config
    sed -i "s/#LoginGraceTime 2m/LoginGraceTime 1m/g" /etc/ssh/sshd_config
fi

# Implement recidive jail for fail2ban
if [ ! -z "$FIREWALL_EXTENSION" ]; then
    if ! cat /etc/fail2ban/jail.local | grep -q "\[recidive\]"; then
        echo -e "\n\n[recidive]\nenabled  = true\nfilter   = recidive\naction   = hestia[name=HESTIA]\nlogpath  = /var/log/fail2ban.log\nmaxretry = 3\nfindtime = 86400\nbantime  = 864000" >> /etc/fail2ban/jail.local
    fi
fi

# Enable OCSP SSL stapling and harden nginx configuration for roundcube
if [ ! -z "$IMAP_SYSTEM" ]; then
    echo "(*) Hardening security of Roundcube webmail..."
    $BIN/v-update-mail-templates > /dev/null 2>&1
    if [ -e /etc/nginx/conf.d/webmail.inc ]; then
        cp -f /etc/nginx/conf.d/webmail.inc $HESTIA_BACKUP/conf/
        sed -i "s/config|temp|logs/README.md|config|temp|logs|bin|SQL|INSTALL|LICENSE|CHANGELOG|UPGRADING/g" /etc/nginx/conf.d/webmail.inc
    fi
fi

# Fix restart queue
if [ -z "$($BIN/v-list-cron-jobs admin | grep 'v-update-sys-queue restart')" ]; then
    command="sudo $BIN/v-update-sys-queue restart"
    $BIN/v-add-cron-job 'admin' '*/2' '*' '*' '*' '*' "$command"
fi

# Remove deprecated line from ClamAV configuration file
if [ -e "/etc/clamav/clamd.conf" ]; then
    clamd_conf_update_check=$(grep DetectBrokenExecutables /etc/clamav/clamd.conf)
    if [ ! -z "$clamd_conf_update_check" ]; then
        echo "(*) Updating ClamAV configuration..."
        sed -i '/DetectBrokenExecutables/d' /etc/clamav/clamd.conf
    fi
fi

# Remove errornous history.log file created by certain builds due to bug in v-restart-system
if [ -e "$HESTIA/data/users/history.log" ]; then
    rm -f $HESTIA/data/users/history.log
fi

# Use exim4 hostname without hardcoded mailprefix
if [ ! -z "$MAIL_SYSTEM" ]; then
    if cat /etc/exim4/exim4.conf.template | grep -q 'helo_data = mail.${sender_address_domain}'; then
        sed -i 's/helo_data = mail.${sender_address_domain}/helo_data = ${sender_address_domain}/g' /etc/exim4/exim4.conf.template
    fi
fi

# Members of admin group should be permitted to enter admin folder
if [ -d "/home/admin" ]; then
    setfacl -m "g:admin:r-x" /home/admin
fi

# Remove old Vesta Filemanager files completly
if [ ! -d "$HESTIA_BACKUP/bin" ]; then
    mkdir -p $HESTIA_BACKUP/bin
fi
mv $HESTIA/bin/v-add-fs-archive $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-add-fs-directory $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-add-fs-file $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-change-fs-file-permission $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-check-fs-permission $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-copy-fs-directory $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-copy-fs-file $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-delete-fs-directory $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-delete-fs-file $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-extract-fs-archive $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-get-fs-file-type $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-list-fs-directory $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-move-fs-directory $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-move-fs-file $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-open-fs-file $HESTIA_BACKUP/bin/
mv $HESTIA/bin/v-search-fs-object $HESTIA_BACKUP/bin/
if [ ! -d "$HESTIA_BACKUP/web" ]; then
    mkdir -p $HESTIA_BACKUP/web
fi
mv $HESTIA/web/upload $HESTIA_BACKUP/web/