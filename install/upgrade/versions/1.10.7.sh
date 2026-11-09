#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.7

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

# Add actionflush to Hestia Fail2Ban action for existing installations (#5022 / #5936)
f2b_action="/etc/fail2ban/action.d/hestia.conf"
hestia_f2b_action="$HESTIA_INSTALL_DIR/fail2ban/action.d/hestia.conf"

if [[ -f "$f2b_action" ]] && [[ -f "$hestia_f2b_action" ]] && ! grep -qE '^[[:space:]]*actionflush[[:space:]]*=' "$f2b_action"; then
	echo "[ + ] Updating Hestia actions for Fail2Ban"

	f2b_was_active=false
	if systemctl is-active --quiet fail2ban; then
		f2b_was_active=true
	fi

	backup_action="$(mktemp)"

	if ! cp -p "$f2b_action" "$backup_action"; then
		echo "[ ! ] Failed to create temporary backup"
		rm -f "$backup_action"
	elif ! cp -f "$hestia_f2b_action" "$f2b_action"; then
		echo "[ ! ] Failed to update Hestia Fail2Ban action"
		rm -f "$backup_action"
	elif ! fail2ban-client -t &> /dev/null; then
		echo "[ ! ] Error validating Fail2Ban configuration, restoring previous action"

		if cp -p "$backup_action" "$f2b_action" && fail2ban-client -t; then
			echo "[ + ] Previous Fail2Ban action restored successfully"
			rm -f "$backup_action"
		else
			echo "[ ! ] CRITICAL: Failed to restore previous Fail2Ban configuration"
			echo "[ ! ] Temporary backup preserved at: $backup_action"
		fi
	elif $f2b_was_active; then
		if systemctl restart fail2ban && systemctl is-active --quiet fail2ban; then
			echo "[ + ] Service Fail2Ban restarted successfully"
			rm -f "$backup_action"
		else
			echo "[ ! ] Error restarting Fail2Ban, restoring previous action"

			if cp -p "$backup_action" "$f2b_action" \
				&& fail2ban-client -t \
				&& systemctl restart fail2ban \
				&& systemctl is-active --quiet fail2ban; then

				echo "[ + ] Previous Fail2Ban action restored successfully"
				rm -f "$backup_action"
			else
				echo "[ ! ] CRITICAL: Failed to restore previous Fail2Ban configuration"
				echo "[ ! ] Temporary backup preserved at: $backup_action"
			fi
		fi
	else
		rm -f "$backup_action"
	fi
fi
