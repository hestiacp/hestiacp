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

touch $HESTIA/data/queue/laravel.pipe
chmod 660 $HESTIA/data/queue/laravel.pipe

if [ "$(id -u)" -eq 0 ] && command -v crontab > /dev/null 2>&1; then
	tmp_cron=$(mktemp)
	crontab -u hestiaweb -l > "$tmp_cron" 2> /dev/null || true
	if ! grep -q "v-update-sys-queue laravel" "$tmp_cron" 2> /dev/null; then
		echo "* * * * * sudo /usr/local/hestia/bin/v-update-sys-queue laravel" >> "$tmp_cron"
		crontab -u hestiaweb "$tmp_cron"
	fi
	rm -f "$tmp_cron"
fi
