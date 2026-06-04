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
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'

# fix/file manager ignores user language
echo "[ * ] Fix File Manager ignoring user language"
cp -f "$HESTIA"/install/deb/filemanager/filegator/configuration.php "$HESTIA"/web/fm/configuration.php

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

# Fix: migrate SnappyMail data directory /etc/snappymail/data to /var/lib/snappymail/data
SNAPPYMAIL_ETC_DIR="/etc/snappymail"
SNAPPYMAIL_ETC_DATA="/etc/snappymail/data"
SNAPPYMAIL_VAR_DATA="/var/lib/snappymail/data"
if [[ -d "$SNAPPYMAIL_ETC_DATA" ]] && ! [[ -L "$SNAPPYMAIL_ETC_DATA" ]]; then
	echo "[ * ] Migrating SnappyMail data directory to '$SNAPPYMAIL_VAR_DATA'"
	if [ -L "$SNAPPYMAIL_VAR_DATA" ]; then
		echo "[ * ] Removing existing symlink at '$SNAPPYMAIL_VAR_DATA'"
		rm -f "$SNAPPYMAIL_VAR_DATA"
		echo "[ * ] Moving '$SNAPPYMAIL_ETC_DATA' to '$SNAPPYMAIL_VAR_DATA'"
		if ! mv "$SNAPPYMAIL_ETC_DATA" "$SNAPPYMAIL_VAR_DATA"; then
			echo "[ ! ] Failed to move '$SNAPPYMAIL_ETC_DATA' to '$SNAPPYMAIL_VAR_DATA'. Skipping cleanup."
		else
			echo "[ * ] Removing '$SNAPPYMAIL_ETC_DIR' directory"
			if ! rm -rf "$SNAPPYMAIL_ETC_DIR"; then
				echo "[ ! ] Failed to remove '$SNAPPYMAIL_ETC_DIR'."
			else
				echo "[ * ] SnappyMail data directory migration completed successfully"
			fi
		fi
	else
		echo "[ * ] '$SNAPPYMAIL_VAR_DATA' is not a symlink. Skipping SnappyMail data migration."
	fi
fi
