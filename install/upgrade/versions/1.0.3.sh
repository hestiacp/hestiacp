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
echo "(*) Installing 4096-bit SSL security certificate..."
mv /etc/ssl/dhparam.pem $HESTIA_BACKUP/conf/
cp -f $hestiacp/ssl/dhparam.pem /etc/ssl/
chmod 600 /etc/ssl/dhparam.pem

# Enhance Vsftpd security
echo "(*) Modifying Vsftpd SSL configuration..."
cp -f /etc/vsftpd.conf $HESTIA_BACKUP/conf/
sed -i "s|ssl_tlsv1=YES|ssl_tlsv1=NO|g" /etc/vsftpd.conf

# Enhance Dovecot security
echo "(*) Modifying Dovecot SSL configuration..."
mv /etc/dovecot/conf.d/10-ssl.conf $HESTIA_BACKUP/conf/
cp -f $hestiacp/dovecot/conf.d/10-ssl.conf /etc/dovecot/conf.d/

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
    sed -i "s/STATS_SYSTEM='webalizer,awstats'/STATS_SYSTEM='awstats'/g" $HESTIA/conf/hestia.conf
fi