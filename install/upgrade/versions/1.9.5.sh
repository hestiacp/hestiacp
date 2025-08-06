#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.9.3

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
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# Migrate cron job commands to new base64 encoded form
# Search for all user cron jobs
for user in $(v-list-users plain | cut -f1); do
	# Check if user has cron jobs
	if [ -f "$HESTIA/data/users/$user/cron.conf" ]; then
		# Read cron jobs
		while IFS= read -r line; do
			parse_object_kv_list "$line"
			# Attempt to decode the command from base64
			decoded_command=$(echo "$CMD" | sed -e "s/%quote%/'/g" -e "s/%dots%/:/g" | base64 -d 2> /dev/null)
			if [ $? -eq 0 ]; then # Successfully decoded, meaning it was already base64
				# Check if decoded command matches the original to determine if it was actually base64 encoded
				if ! echo "$decoded_command" | base64 | grep -q "$CMD"; then
					# It wasn't a properly base64 encoded command; re-encode it.
					command=$(echo -n "$decoded_command" | base64 -w 0)
					sed -i "s~CMD='$CMD'~CMD='$command'~" "$HESTIA/data/users/$user/cron.conf"
				fi
			else
				# The command is not in base64 format; encode it.
				command=$(echo -n "$CMD" | base64 -w 0)
				sed -i "s~CMD='$CMD'~CMD='$command'~" "$HESTIA/data/users/$user/cron.conf"
			fi
		done < "$HESTIA/data/users/$user/cron.conf"
	fi
done
# Run Sync cron jobs
sync_cron_jobs
