#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.0

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

# Apply SSH config if running on Debian 13
source /etc/os-release
if [[ "$ID" == "debian" && "$VERSION_ID" == "13" ]]; then
	_KEX_CONF="/etc/ssh/sshd_config.d/hestia-kex.conf"
	_KEX_LINE="KexAlgorithms +diffie-hellman-group-exchange-sha256"

	# Only create/modify the file if it doesn't already contain the correct config
	if [[ ! -f "$_KEX_CONF" ]] || ! grep -qxF "$_KEX_LINE" "$_KEX_CONF"; then
		echo "$_KEX_LINE" > "$_KEX_CONF"
		"$BIN"/v-restart-service ssh
	fi
fi
