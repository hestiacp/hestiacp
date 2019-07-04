#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.3

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Set default theme
if [ -z $THEME ]; then
    echo "(*) Enabling support for themes..."
    $BIN/v-change-sys-theme default
fi

# Replace dhparam 1024 with dhparam 4096
echo "(*) Increasing Diffie-Hellman Parameter strength to 4096-bit..."
mv /etc/ssl/dhparam.pem $HESTIA_BACKUP/conf/
cp -f $HESTIA_INSTALL_DIR/ssl/dhparam.pem /etc/ssl/
chmod 600 /etc/ssl/dhparam.pem

# Reduce SSH login grace time
sed -i "s/LoginGraceTime 2m/LoginGraceTime 1m/g" /etc/ssh/sshd_config
sed -i "s/#LoginGraceTime 2m/LoginGraceTime 1m/g" /etc/ssh/sshd_config

# Enhance Vsftpd security
echo "(*) Hardening Vsftpd SSL configuration..."
cp -f /etc/vsftpd.conf $HESTIA_BACKUP/conf/
sed -i "s|ssl_tlsv1=YES|ssl_tlsv1=NO|g" /etc/vsftpd.conf

# Enhance Dovecot security
echo "(*) Hardening Dovecot SSL configuration..."
mv /etc/dovecot/conf.d/10-ssl.conf $HESTIA_BACKUP/conf/
cp -f $HESTIA_INSTALL_DIR/dovecot/conf.d/10-ssl.conf /etc/dovecot/conf.d/

# Update DNS resolvers in hestia-nginx's configuration
echo "(*) Updating DNS resolvers for Hestia Internal Web Server..."
dns_resolver=$(cat /etc/resolv.conf | grep -i '^nameserver' | cut -d ' ' -f2 | tr '\r\n' ' ' | xargs)
for ip in $dns_resolver; do
    if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        resolver="$ip $resolver"
    fi
done
if [ ! -z "$resolver" ]; then
    sed -i "s/1.0.0.1 1.1.1.1/$resolver/g" /usr/local/hestia/nginx/conf/nginx.conf
fi

# Remove Webalizer and set AWStats as default
WEBALIZER_CHECK=$(cat $HESTIA/conf/hestia.conf | grep webalizer)
if [ ! -z "$WEBALIZER_CHECK" ]; then
    echo "(*) Removing Webalizer and setting AWStats as default web statistics backend..."
    apt purge webalizer -y > /dev/null 2>&1
    if [ -d "$HESTIA/data/templates/web/webalizer" ]; then
        rm -rf $HESTIA/data/templates/web/webalizer
    fi
    if [ -d "/var/www/webalizer" ]; then
        rm -rf /var/www/webalizer
    fi
    sed -i "s/STATS_SYSTEM='webalizer,awstats'/STATS_SYSTEM='awstats'/g" $HESTIA/conf/hestia.conf
fi

# Remove old hestia.conf files from Apache & NGINX if they exist
if [ -f "/etc/apache2/conf.d/hestia.conf" ]; then
    echo "(*) Removing old Apache configuration file from previous version of Hestia Control Panel..."
    rm -f /etc/apache2/conf.d/hestia.conf
fi
if [ -f "/etc/nginx/conf.d/hestia.conf" ]; then
    echo "(*) Removing old NGINX configuration file from previous version of Hestia Control Panel..."
    rm -f /etc/nginx/conf.d/hestia.conf
fi

# Implement recidive jail for fail2ban
if [ ! -z "$FIREWALL_EXTENSION" ]; then
    if ! cat /etc/fail2ban/jail.local | grep -q "recidive"; then
        echo -e "\n\n[recidive]\nenabled  = true\nfilter   = recidive\naction   = hestia[name=HESTIA]\nlogpath  = /var/log/fail2ban.log\nmaxretry = 3\nfindtime = 86400\nbantime  = 864000" >> /etc/fail2ban/jail.local
    fi
fi

# Update webmail templates to enable OCSP/SSL stapling
if [ ! -z "$IMAP_SYSTEM" ]; then
    echo "(*) Enabling OCSP stapling support for webmail services..."
    $BIN/v-update-mail-templates > /dev/null 2>&1
fi 
