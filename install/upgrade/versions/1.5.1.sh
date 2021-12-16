#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.5.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### Pass trough information to the end user incase of a issue or problem    #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### to the upgrade email. Please add it using:                              #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

echo "[ * ] Apply changes for 1.5.1"

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'true'

RHOST='apt.hestiacp.com'
codename="$(lsb_release -s -c)"
if [ -z "$codename" ]; then 
    codename="$(cat /etc/os-release |grep VERSION= |cut -f 2 -d \(|cut -f 1 -d \))"
fi
architecture="$(uname -m)"
case $architecture in 
    x86_64)
        ARCH="amd64"
        ;;
    aarch64)
        ARCH="arm64"
        ;;
    *)
esac

chmod +x $HESTIA/install/deb/

echo "[ * ] Update /etc/apt/sources.list.d/hestia.list"
sed -i "s|deb https://$RHOST/ $codename main|deb [arch=$ARCH] https://$RHOST/ $codename main|g" /etc/apt/sources.list.d/hestia.list

if [ -n "$IMAP_SYSTEM" ]; then 
    sed -i "s/mail_plugins = \$mail_plugins sieve/mail_plugins = \$mail_plugins quota sieve/g" /etc/dovecot/conf.d/15-lda.conf
fi

if [ -n "$MAIL_SYSTEM" ]; then
    echo "[ ! ] Update Exim config"
    if [ -f "/etc/exim4/exim4.conf.template" ]; then
        sed -i 's/^smtp_active_hostname = \${lookup dnsdb{>: ptr=\$interface_address}{\${listextract{1}{\$value}}}{\$primary_hostname}}$/smtp_active_hostname = \${lookup dnsdb{>: defer_never,ptr=\$interface_address}{\${listextract{1}{\$value}}}{\$primary_hostname}}/' /etc/exim4/exim4.conf.template
        sed -i 's/^  helo_data = \${lookup dnsdb{>: ptr=\$sending_ip_address}{\${listextract{1}{\$value}}}{\$primary_hostname}}$/  helo_data = \${lookup dnsdb{>: defer_never,ptr=\$sending_ip_address}{\${listextract{1}{\$value}}}{\$primary_hostname}}/' /etc/exim4/exim4.conf.template
    fi
fi
