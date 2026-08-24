#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.6

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
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'

# Add support for custom DKIM selector on existing Exim installations
exim_conf="/etc/exim4/exim4.conf.template"
exim_conf_bak="${exim_conf}.before_dkim_custom_selector.bak"
pattern='dkim_selector = mail'

if [[ -f "$exim_conf" ]] && grep -qF "$pattern" "$exim_conf"; then
	echo "[ + ] Adding support for custom DKIM selector in Exim"
	sed -i.before_dkim_custom_selector.bak 's#dkim_selector = mail#dkim_selector = ${if exists{/etc/exim4/domains/${lookup{$dkim_domain}dsearch{/etc/exim4/domains/}}/selector}{${lookup{selector}lsearch{/etc/exim4/domains/${lookup{$dkim_domain}dsearch{/etc/exim4/domains/}}/selector}{$value}{mail}}}{mail}}#' "$exim_conf"

	if systemctl restart exim4; then
		echo "[ + ] Exim4 restarted successfully with custom DKIM selector support"
	else
		echo "[ ! ] Exim4 failed to restart, rolling back configuration"

		if [[ -f "$exim_conf_bak" ]]; then
			mv "$exim_conf_bak" "$exim_conf"

			if systemctl restart exim4; then
				echo "[ + ] Exim4 restarted successfully after rollback"
			else
				echo "[ ! ] Exim4 failed to restart even after rollback"
			fi
		else
			echo "[ ! ] Backup file not found, unable to rollback"
		fi
	fi
fi
