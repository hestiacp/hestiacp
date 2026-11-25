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

# Restore vsftpd jails removed when users were switched to a login shell.
if [ "$FTP_SYSTEM" = 'vsftpd' ] && grep -qxF 'local_root=/srv/jail/%u' /etc/vsftpd.conf; then
	echo "[ * ] Restoring FTP jails for users with a login shell..."
	if ! hestia_users=$("$BIN/v-list-users" list); then
		echo "Error: could not list Hestia users for FTP jail recovery" >&2
		exit 1
	fi
	while IFS= read -r user; do
		[ -n "$user" ] || continue
		user_shell=$(getent passwd "$user" | cut -d: -f7)
		if [ -n "$user_shell" ] && [[ ! "$user_shell" =~ (rssh|nologin)$ ]]; then
			if ! "$BIN/v-add-user-sftp-jail" "$user" no; then
				echo "Error: could not restore the FTP jail for $user" >&2
				exit 1
			fi
		fi
	done <<< "$hestia_users"
fi

# Spread out Hestia automatic updates: only touch the job when it is active
# and still on the old fixed default time (41 4), so custom times are kept.
hestiaweb_cron="/var/spool/cron/crontabs/hestiaweb"
if [ -f "$hestiaweb_cron" ] && grep -qE '^41 4 \* \* \* sudo /usr/local/hestia/bin/v-update-sys-hestia-all' "$hestiaweb_cron"; then
	echo "[ * ] Spreading out Hestia automatic update schedule..."
	min=$(generate_password '012345' '2')
	hour=$(generate_password '123456' '1')
	sed -i -E "s|^41 4 (\* \* \* sudo /usr/local/hestia/bin/v-update-sys-hestia-all)|$min $hour \1|" "$hestiaweb_cron"
fi
