#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.9.0

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
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'yes'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# update config sftp jail
$BIN/v-delete-sys-sftp-jail
$BIN/v-add-sys-sftp-jail

codename="$(lsb_release -s -c)"
apt=/etc/apt/sources.list.d

# Installing Node.js repo
if [ "$webterminal" = 'yes' ]; then
	node_v="20"
	echo "[ * ] Adding Node.js $node_v repo and install"
	echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/nodejs.gpg] https://deb.nodesource.com/node_$node_v.x nodistro main" > $apt/nodejs.list
	curl -s https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor | tee /usr/share/keyrings/nodejs.gpg > /dev/null 2>&1
	curl -fsSL https://deb.nodesource.com/setup_$node_v.x | bash -
fi

# Check if hestiaweb exists
if [ -z "$(grep ^hestiaweb: /etc/passwd)" ]; then
	# Generate a random password
	random_password=$(generate_password '32')
	# Create the new hestiaweb user
	/usr/sbin/useradd "hestiaweb" -c "$email" --no-create-home
	# do not allow login into hestiaweb user
	echo hestiaweb:$random_password | sudo chpasswd -e
	cp $HESTIA_COMMON_DIR/sudo/hestiaweb /etc/sudoers.d/
	# Keep enabled for now
	# Remove sudo permissions admin user
	#rm /etc/sudoers.d/admin/
fi

# Check if cronjobs have been migrated
if [ ! -f "/var/spool/cron/crontabs/hestiaweb" ]; then
	echo "MAILTO=\"\"" > /var/spool/cron/crontabs/hestiaweb
	echo "CONTENT_TYPE=\"text/plain; charset=utf-8\"" >> /var/spool/cron/crontabs/hestiaweb
	while read line; do
		parse_object_kv_list "$line"
		if [ -n "$(echo "$CMD" | grep ^sudo)" ]; then
			echo "$MIN $HOUR $DAY $MONTH $WDAY $CMD" \
				| sed -e "s/%quote%/'/g" -e "s/%dots%/:/g" \
					>> /var/spool/cron/crontabs/hestiaweb
			$BIN/v-delete-cron-job admin "$JOB"
		fi
	done < $HESTIA/data/users/admin/cron.conf
	# Update permissions
	chmod 600 /var/spool/cron/crontabs/hestiaweb
	chown hestiaweb:hestiaweb /var/spool/cron/crontabs/hestiaweb

fi

chown hestiaweb:hestiaweb /usr/local/hestia/data/sessions

packages=$(ls --sort=time $HESTIA/data/packages | grep .pkg)
# Update Hestia Packages
for package in $packages; do
	if [ -z "$(grep -e 'SHELL_JAIL_ENABLED' $HESTIA/data/packages/$package)" ]; then
		echo "SHELL_JAIL_ENABLED='no'" >> $HESTIA/data/packages/$package
	fi
	if [ -z "$(grep -e 'BACKUPS_INCREMENTAL' $HESTIA/data/packages/$package)" ]; then
		echo "BACKUPS_INCREMENTAL='no'" >> $HESTIA/data/packages/$package
	fi

	# Add additional key-value pairs if they don't exist
	for key in DISK_QUOTA CPU_QUOTA CPU_QUOTA_PERIOD MEMORY_LIMIT SWAP_LIMIT; do
		if [ -z "$(grep -e "$key" $HESTIA/data/packages/$package)" ]; then
			echo "$key='unlimited'" >> $HESTIA/data/packages/$package
		fi
	done
done

$BIN/v-add-user-notification 'admin' 'Hestia security has been upgraded' ' A new user "hestiaweb" has been created and is used for login. Make sure other Hestia packages are updated as well otherwise the system may not work as expected.'
add_upgrade_message 'Security has been upgraded, A new user "hestiaweb" has been created and is used for login. Make sure other Hestia packages are updated as well otherwise the system may not work as expected.'
