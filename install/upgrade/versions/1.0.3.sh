#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.3

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Set default theme
if [ -z $THEME ]; then
    echo "(*) Enabling support for customizable themes and configuring default..."
    $BIN/v-change-sys-theme default
fi

# Replace dhparam 1024 with dhparam 4096
echo "(*) Updating dhparam to 4096-bit..."
mv /etc/ssl/dhparam.pem $HESTIA_BACKUP/conf/
cp -rf $HESTIA/install/deb/ssl/dhparam.pem /etc/ssl/
systemctl reload nginx

# Enhance Dovecot security
echo "(*) Enhancing Dovecot security..."
mv /etc/dovecot/conf.d/10-ssl.conf $HESTIA_BACKUP/conf/
cp -rf $HESTIA/install/deb/dovecot/10-ssl.conf /etc/dovecot/conf.d/
systemctl restart dovecot
