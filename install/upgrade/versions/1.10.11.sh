#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.11

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

# Spread out Hestia automatic updates: only touch the job when it is active
# and still on the old fixed default time (41 4), so custom times are kept.
hestiaweb_cron="/var/spool/cron/crontabs/hestiaweb"
if [ -f "$hestiaweb_cron" ] && grep -qE '^41 4 \* \* \* sudo /usr/local/hestia/bin/v-update-sys-hestia-all' "$hestiaweb_cron"; then
	echo "[ * ] Spreading out Hestia automatic update schedule..."
	min=$(generate_password '012345' '2')
	hour=$(generate_password '123456' '1')
	sed -i -E "s|^41 4 (\* \* \* sudo /usr/local/hestia/bin/v-update-sys-hestia-all)|$min $hour \1|" "$hestiaweb_cron"
fi

# Restrict permissions of existing Restic configuration files (#5965)
if [[ -f "$HESTIA/conf/restic.conf" ]] && [[ ! -L "$HESTIA/conf/restic.conf" ]]; then
	chmod 660 -- "$HESTIA/conf/restic.conf"
fi
for restic_conf in "$HESTIA"/data/users/*/restic.conf; do
	if [[ -f "$restic_conf" ]] && [[ ! -L "$restic_conf" ]]; then
		chmod 600 -- "$restic_conf"
	fi
done
unset restic_conf
