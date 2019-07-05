#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.1.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Set default theme
if [ -z $THEME ]; then
    echo "(*) Enabling support for themes..."
    $BIN/v-change-sys-config-value 'THEME' default
fi

# Reduce SSH login grace time
if [ -e /etc/ssh/sshd_config ]; then
    echo "(*) Hardening SSH daemon configuration..."
    sed -i "s/LoginGraceTime 2m/LoginGraceTime 1m/g" /etc/ssh/sshd_config
    sed -i "s/#LoginGraceTime 2m/LoginGraceTime 1m/g" /etc/ssh/sshd_config
fi

# Implement recidive jail for fail2ban
if [ ! -z "$FIREWALL_EXTENSION" ]; then
    if ! cat /etc/fail2ban/jail.local | grep -q "recidive"; then
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