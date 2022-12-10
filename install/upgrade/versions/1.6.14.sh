#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.6.14

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

# Clean up firewall rules restore file.
if [ "$FIREWALL_SYSTEM" = "iptables" ]; then
	add_upgrade_message "About iptables rules [non-urgent]\n\nJust in case, if you added custom iptables rules in an unsupported way, they may have been lost.\n\nSee this issue to learn more:\nhttps://github.com/hestiacp/hestiacp/issues/3128"
	echo "[ * ] Clean up firewall rules restore file..."
	$BIN/v-update-firewall
fi
