#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.8.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### upgrade_config_set_value only accepts true or false.                    #######
#######                                                                         #######
####### Pass through information to the end user in case of a issue or problem  #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### in the upgrade notification email. Example:                             #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# Disable TLS 1.3 support for ProFTPD versions older than v1.3.7a
if [ "$FTP_SYSTEM" = "proftpd" ]; then
	os_release="$(lsb_release -s -i | tr "[:upper:]" "[:lower:]")-$(lsb_release -s -r)"

	if [ "$os_release" = "debian-10" ] || [ "$os_release" = "ubuntu-20.04" ]; then
		if grep -qw "^TLSProtocol                             TLSv1.2 TLSv1.3$" test.conf 2> /dev/null; then
			sed -i 's/TLSProtocol                             TLSv1.2 TLSv1.3/TLSProtocol                             TLSv1.2/' /etc/proftpd/tls.conf
		else
			sed -i '/^TLSProtocol .\+$/d;/TLSServerCipherPreference               on$/i TLSProtocol                             TLSv1.2' /etc/proftpd/tls.conf
		fi
	fi
fi
