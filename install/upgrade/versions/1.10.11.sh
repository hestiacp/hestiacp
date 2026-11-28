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

# Repair grants affected by the 1.10.10 migration on MySQL with partial_revokes.
if [ -f "$HESTIA/conf/mysql.conf" ]; then
	echo "[ * ] Repairing MySQL database grants"
	if ! (
		source "$HESTIA/func/db.sh"
		mysql_migrate_legacy_db_grants
	); then
		echo "[ ! ] Some MySQL database grants could not be repaired; see the upgrade log"
		add_upgrade_message "Some MySQL database grants could not be repaired. Check the upgrade log for affected databases and connection errors."
	fi
fi

# Override sury ProtectHome=read-only so Apache can write to /home
if [ "$WEB_SYSTEM" = "apache2" ] && ! grep -qs "^ProtectHome=no" /etc/systemd/system/apache2.service.d/hestia.conf; then
	echo "[ * ] Adding ProtectHome=no override to Apache2 service..."
	mkdir -p /etc/systemd/system/apache2.service.d
	echo -e "[Service]\nProtectHome=no" > /etc/systemd/system/apache2.service.d/hestia.conf
	systemctl daemon-reload > /dev/null 2>&1
	systemctl restart apache2 > /dev/null 2>&1
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
