#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.8.8

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

# Modify existing POLICY_USER directives (POLICY_USER_CHANGE_THEME, POLICY_USER_EDIT_WEB_TEMPLATES
# and POLICY_USER_VIEW_LOGS) that are using value 'true' instead of the correct value 'yes'

hestia_conf="$HESTIA/conf/hestia.conf"
hestia_defaults_conf="$HESTIA/conf/defaults/hestia.conf"

if [ -f /etc/nginx/nginx.conf ]; then
	echo "[ * ] Mitigate HTTP/2 Rapid Reset Attack via Nginx CVE CVE-2023-44487"
	sed -i -E 's/(.*keepalive_requests\s{1,})10000;/\11000;/' /etc/nginx/nginx.conf /usr/local/hestia/nginx/conf/nginx.conf
fi

# Fix security issue wit FPM pools
if [ -z "$(grep ^hestiamail: /etc/passwd)" ]; then
	echo "[ * ] Limit permissions www.conf and dummy.conf"
	/usr/sbin/useradd "hestiamail" -c "$email" --no-create-home

	sed -i "s/user = www-data/user = hestiamail/g" /etc/php/*/fpm/pool.d/www.conf

	php_versions=$($BIN/v-list-sys-php plain)
	# Substitute php-fpm service name formats
	for version in $php_versions; do
		cp -f $HESTIA_INSTALL_DIR/php-fpm/dummy.conf /etc/php/$version/fpm/pool.d/
		sed -i "s/%backend_version%/$version/g" /etc/php/$version/fpm/pool.d/dummy.conf
	done
fi
