#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.3

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

# Reinstall FileManager to fix the missing symfony/mime module
# Only reinstall if the current version matches the one in upgrade.conf
# If the current version is older, the upgrade process will update it later
if [[ "${FILE_MANAGER:-false}" == "true" ]] \
	&& [[ -f "$HESTIA/web/fm/version" ]]; then

	fm_v="${fm_v:-1.0.0}"
	current_fm_v="$(< "$HESTIA/web/fm/version")"

	if dpkg --compare-versions "$current_fm_v" eq "$fm_v"; then
		echo "[ * ] Reinstalling FileManager $fm_v"

		"$BIN/v-delete-sys-filemanager" quiet yes \
			|| echo "[ ! ] Failed to remove FileManager, continuing..."

		if "$BIN/v-add-sys-filemanager" quiet; then
			echo "[ * ] FileManager $fm_v successfully reinstalled"
		else
			echo "[ ! ] Failed to reinstall FileManager, continuing..."
		fi
	fi
fi
