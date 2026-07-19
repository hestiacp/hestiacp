#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.9.8

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

# Fixing Subsystem sftp config for current installations
reload_ssh=false
echo "[ * ] Fixing Subsystem sftp config"
if grep -q "^Subsystem.*internal-sftp-.*" /etc/ssh/sshd_config; then
	sed -i 's/Subsystem sftp internal-sftp-.*/Subsystem sftp internal-sftp/' /etc/ssh/sshd_config
	reload_ssh=true
fi

if grep -q "^Subsystem.*/usr/lib/sftp-server-" /etc/ssh/sshd_config; then
	sed -i 's/^Subsystem sftp \/usr\/lib\/sftp-server-.*/Subsystem sftp \/usr\/lib\/sftp-server/' /etc/ssh/sshd_config
	reload_ssh=true
fi
[[ $reload_ssh == true ]] && systemctl reload ssh

# Enhance - Update current composer installations
echo "[ * ] Updating composer for users:"
for huser in $("$HESTIA/bin/v-list-users" list); do
	if [[ -f "/home/$huser/.composer/composer" ]]; then
		echo "      - $huser..."
		"$HESTIA/bin/v-add-user-composer" "$huser" 2 yes &> /dev/null
	fi
done
