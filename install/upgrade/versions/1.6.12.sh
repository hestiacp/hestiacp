#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.6.12

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### Pass through information to the end user in case of a issue or problem  #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### in the upgrade notification email. Example:                             #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

if [ -f "/etc/fail2ban/jail.local" ]; then
	sed -i "s|/var/log/mysql.log|/var/log/mysql/error.log|g" /etc/fail2ban/jail.local
fi

# Fixed firewall loading failed after reboot, applying update to devcp-iptables Systemd unit.
if [ "$FIREWALL_SYSTEM" = "iptables" ]; then
	echo "[ * ] Update loading firewall rules service..."
	$BIN/v-delete-sys-firewall
	$BIN/v-add-sys-firewall
fi
