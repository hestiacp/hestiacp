#!/bin/bash

# DevIT Control Panel upgrade script for target version 1.9.0

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

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'yes'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'true'

# update config sftp jail
$BIN/v-delete-sys-sftp-jail
$BIN/v-add-sys-sftp-jail

# Check if DevITweb exists
if [ -z "$(grep ^DevITweb: /etc/passwd)" ]; then
	# Generate a random password
	random_password=$(generate_password '32')
	# Create the new DevITweb user
	/usr/sbin/useradd "DevITweb" -c "$email" --no-create-home
	# do not allow login into DevITweb user
	echo DevITweb:$random_password | sudo chpasswd -e
	cp $DevIT_COMMON_DIR/sudo/DevITweb /etc/sudoers.d/
	# Keep enabled for now
	# Remove sudo permissions admin user
	# rm /etc/sudoers.d/admin/
fi

# Check if cronjobs have been migrated
if [ ! -f "/var/spool/cron/crontabs/DevITweb" ]; then
	echo "MAILTO=\"\"" > /var/spool/cron/crontabs/DevITweb
	echo "CONTENT_TYPE=\"text/plain; charset=utf-8\"" >> /var/spool/cron/crontabs/DevITweb
	while read line; do
		parse_object_kv_list "$line"
		if [ -n "$(echo "$CMD" | grep ^sudo)" ]; then
			echo "$MIN $HOUR $DAY $MONTH $WDAY $CMD" \
				| sed -e "s/%quote%/'/g" -e "s/%dots%/:/g" \
					>> /var/spool/cron/crontabs/DevITweb
			$BIN/v-delete-cron-job admin "$JOB"
		fi
	done < $DevIT/data/users/admin/cron.conf
	# Update permissions
	chmod 600 /var/spool/cron/crontabs/DevITweb
	chown DevITweb:DevITweb /var/spool/cron/crontabs/DevITweb

fi

chown DevITweb:DevITweb /usr/local/DevIT/data/sessions

packages=$(ls --sort=time $DevIT/data/packages | grep .pkg)
# Update DevIT Packages
for package in $packages; do
	if [ -z "$(grep -e 'BACKUPS_INCREMENTAL' $DevIT/data/packages/$package)" ]; then
		echo "BACKUPS_INCREMENTAL='no'" >> $DevIT/data/packages/$package
	fi

	# Add additional key-value pairs if they don't exist
	for key in DISK_QUOTA CPU_QUOTA CPU_QUOTA_PERIOD MEMORY_LIMIT SWAP_LIMIT; do
		if [ -z "$(grep -e "$key" $DevIT/data/packages/$package)" ]; then
			echo "$key='unlimited'" >> $DevIT/data/packages/$package
		fi
	done
done

# Add xferlog to vsftpd logrotate
if [ -s /etc/logrotate.d/vsftpd ] && ! grep -Fq "/var/log/xferlog" /etc/logrotate.d/vsftpd; then
	sed -i 's|/var/log/vsftpd.log|/var/log/vsftpd.log /var/log/xferlog|g' /etc/logrotate.d/vsftpd
fi

# Use only TLS 1.2 cipher suites for vsftpd
if [ -s /etc/vsftpd.conf ]; then
	sed -i "s/ssl_ciphers.*/ssl_ciphers=ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384:DHE-RSA-CHACHA20-POLY1305/g" /etc/vsftpd.conf
fi

# Increase max connections and limit number of connections per host for Exim
if [ -s /etc/exim4/exim4.conf.template ] && ! grep -Fq "smtp_accept_max" /etc/exim4/exim4.conf.template; then
	sed -i '/disable_ipv6 = true/a\smtp_accept_max = 100\nsmtp_accept_max_per_host = 20' /etc/exim4/exim4.conf.template
fi

# Update www.conf due security issue
php_versions=$($BIN/v-list-sys-php plain)
# Substitute php-fpm service name formats
for version in $php_versions; do
	if [ -f "/etc/php/$version/fpm/pool.d/www.conf" ]; then
		cp -f $DevIT_INSTALL_DIR/php-fpm/www.conf "/etc/php/$version/fpm/pool.d/www.conf"
	fi
done

# Recreate PHPMYADMIN / PHPGADMIN conf correctly
if [ -n "$DB_PMA_ALIAS" ]; then
	old=$DB_PMA_ALIAS
	$BIN/v-change-sys-db-alias pma "randomstring"
	$BIN/v-change-sys-db-alias pma "$old"
fi
if [ -n "$DB_PGA_ALIAS" ]; then
	old=$DB_PGA_ALIAS
	$BIN/v-change-sys-db-alias pga "randomstring"
	$BIN/v-change-sys-db-alias pga "$old"
fi

# Fix MySQL lc-messages-dir path for mariadb
if [ -x /usr/bin/mariadb ]; then
	sed -i 's|/usr/share/mysql|/usr/share/mariadb|g' /etc/mysql/my.cnf
fi

$BIN/v-add-user-notification 'admin' 'DevIT security has been upgraded' ' A new user "DevITweb" has been created and is used for login. Make sure other DevIT packages are updated as well otherwise the system may not work as expected.'
add_upgrade_message 'Security has been upgraded, A new user "DevITweb" has been created and is used for login. Make sure other DevIT packages are updated as well otherwise the system may not work as expected.'
# Ensures proper permissions for DevIT service interactions.
/usr/sbin/adduser DevITmail DevIT-users
