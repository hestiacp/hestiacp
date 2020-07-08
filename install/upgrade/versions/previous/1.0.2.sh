#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Replace dhparam 1024 with dhparam 4096
echo "[ * ] Increasing Diffie-Hellman Parameter strength to 4096-bit..."
if [ -e /etc/ssl/dhparam.pem ]; then
    mv /etc/ssl/dhparam.pem $HESTIA_BACKUP/conf/
fi
cp -f $HESTIA/install/deb/ssl/dhparam.pem /etc/ssl/
chmod 600 /etc/ssl/dhparam.pem

# Enhance Vsftpd security
if [ "$FTP_SYSTEM" = "vsftpd" ]; then
    echo "[ * ] Hardening Vsftpd SSL configuration..."
    cp -f /etc/vsftpd.conf $HESTIA_BACKUP/conf/
    sed -i "s|ssl_tlsv1=YES|ssl_tlsv1=NO|g" /etc/vsftpd.conf
fi

# Enhance Dovecot security
if [ "$IMAP_SYSTEM" = "dovecot" ]; then
    echo "[ * ] Hardening Dovecot SSL configuration..."
    mv /etc/dovecot/conf.d/10-ssl.conf $HESTIA_BACKUP/conf/
    cp -f $HESTIA/install/deb/dovecot/conf.d/10-ssl.conf /etc/dovecot/conf.d/
fi

# Update DNS resolvers in hestia-nginx's configuration
echo "[ * ] Updating DNS resolvers for Hestia Internal Web Server..."
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
    echo "[ * ] Set awstats as default web statistics backend..."
    $HESTIA/bin/v-change-sys-config-value 'STATS_SYSTEM' 'awstats'
fi

# Remove old hestia.conf files from Apache & NGINX if they exist
if [ -f "/etc/apache2/conf.d/hestia.conf" ]; then
    echo "[ * ] Removing old Apache configuration file from previous version of Hestia Control Panel..."
    rm -f /etc/apache2/conf.d/hestia.conf
fi
if [ -f "/etc/nginx/conf.d/hestia.conf" ]; then
    echo "[ * ] Removing old NGINX configuration file from previous version of Hestia Control Panel..."
    rm -f /etc/nginx/conf.d/hestia.conf
fi

# Update webmail templates to enable OCSP/SSL stapling
if [ ! -z "$IMAP_SYSTEM" ]; then
    echo "[ * ] Enabling OCSP stapling support for webmail services..."
    $BIN/v-update-mail-templates > /dev/null 2>&1
fi 

# Enhance webmail security
if [ -e "/etc/nginx/conf.d/webmail.inc" ]; then
    cp -f /etc/nginx/conf.d/webmail.inc $HESTIA_BACKUP/conf/
    sed -i "s/config|temp|logs/README.md|config|temp|logs|bin|SQL|INSTALL|LICENSE|CHANGELOG|UPGRADING/g" /etc/nginx/conf.d/webmail.inc
fi