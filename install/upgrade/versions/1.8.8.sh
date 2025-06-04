#!/bin/bash

# DevIT Control Panel upgrade script for target version 1.8.8

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

# Modify existing POLICY_USER directives (POLICY_USER_CHANGE_THEME, POLICY_USER_EDIT_WEB_TEMPLATES
# and POLICY_USER_VIEW_LOGS) that are using value 'true' instead of the correct value 'yes'

DevIT_conf="$DevIT/conf/DevIT.conf"
DevIT_defaults_conf="$DevIT/conf/defaults/DevIT.conf"

for i in POLICY_USER_CHANGE_THEME POLICY_USER_EDIT_WEB_TEMPLATES POLICY_USER_VIEW_LOGS; do
	if [[ -f "$DevIT_conf" ]]; then
		if grep "$i" "$DevIT_conf" | grep -q 'true'; then
			if "$BIN/v-change-sys-config-value" "$i" 'yes'; then
				echo "[ * ] Success: ${i} value changed from true to yes in DevIT.conf"
			else
				echo "[ ! ] Error: Couldn't change ${i} value from true to yes in DevIT.conf"
			fi
		fi
	fi
	if [[ -f "$DevIT_defaults_conf" ]]; then
		if grep "$i" "$DevIT_defaults_conf" | grep -q 'true'; then
			if sed -i "s/${i}='true'/${i}='yes'/" "$DevIT_defaults_conf"; then
				echo "[ * ] Success: ${i} value changed from true to yes in defaults/DevIT.conf"
			else
				echo "[ ! ] Error: Couldn't change ${i} value from true to yes in defaults/DevIT.conf"
			fi
		fi
	fi
done
