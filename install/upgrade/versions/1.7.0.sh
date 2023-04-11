#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.7.0

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

# load config because we need to know if proftpd is installed

# Includes
# shellcheck source=/etc/hestiacp/hestia.conf
source /etc/hestiacp/hestia.conf
# shellcheck source=/usr/local/hestia/func/main.sh
source $HESTIA/func/main.sh
# shellcheck source=/usr/local/hestia/func/ip.sh
source $HESTIA/func/ip.sh
# load config file
source_conf "$HESTIA/conf/hestia.conf"

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# Make sure to sync install quoteshell arg
if [ "$FILE_MANAGER" = "true" ]; then
	echo "[ * ] Force update filemanager..."
	$HESTIA/bin/v-delete-sys-filemanager quiet
	$HESTIA/bin/v-add-sys-filemanager quiet
fi

packages=$(ls --sort=time $HESTIA/data/packages | grep .pkg)
echo "[ * ] Update existing packages to support rate limit mail accounts..."
for package in $packages; do
	if [ -z "$(grep -e 'RATE_LIMIT' $HESTIA/data/packages/$package)" ]; then
		echo "RATE_LIMIT='200'" >> $HESTIA/data/packages/$package
	fi
done

if [ -z "$(grep -e 'condition =  ${lookup{$local_part@$domain}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/aliases}{false}{true}}' /etc/exim4/exim4.conf.template)" ]; then
	for line in $(sed -n '/redirect_router = dnslookup/=' /etc/exim4/exim4.conf.template); do
		testline=$((line - 1))
		newline=$((line + 1))
		if [ "$(awk NR==$testline /etc/exim4/exim4.conf.template)" = "  file_transport = local_delivery" ]; then
			# Add new line
			sed -i "$newline i \ \ condition = \${lookup{$local_part@\$domain}lsearch{/etc/exim4/domains/\${lookup{\$domain}dsearch{/etc/exim4/domains/}}/aliases}{false}{true}}" /etc/exim4/exim4.conf.template
		fi
	done
fi

# Allow Email@domain.com for login
if [ -f "/etc/dovecot/conf.d/10-auth.conf" ]; then
	sed -i "s/auth_username_format = %u/auth_username_format = %Lu/g" /etc/dovecot/conf.d/10-auth.conf
fi

# rename /var/run/xx to /run/
for file in /etc/dovecot/dovecot.conf /etc/clamav/clamd.conf /etc/exim/exim.conf.template /etc/logrotate.d/apache2 /etc/logrotate.d/nginx /etc/mysql/my.cnf /etc/nginx/nginx.conf; do
	if [ -f "$file" ]; then
		echo "[ * ] Update $file legacy /var/run/ to /run/..."
		sed -i 's|/var/run/|/run/|g' $file
	fi
done
# Update any custom php templates
for file in $HESTIA/data/templates/web/php-fpm/*; do
	echo "[ * ] Update $file legacy /var/run/ to /run/..."
	sed -i 's|/var/run/|/run/|g' $file
done

for file in /etc/php/*/fpm/pool.d/www.conf; do
	echo "[ * ] Update $file legacy /var/run/ to /run/..."
	sed -i 's|/var/run/|/run/|g' $file
done

#update proftpd
if [ "$FTP_SYSTEM" = 'proftpd' ]; then
	contains_conf_d=$(grep -c "Include /etc/proftpd/conf.d/\*.conf" "/etc/proftpd/proftpd.conf")
	# the line below is for testing only:
	#        echo "contains proftpd? $contains_conf_d"
	if [ $contains_conf_d = 0 ]; then
		sed -i 's/Include \/etc\/proftpd\/tls.conf/&\nInclude \/etc\/proftpd\/conf.d\/*.conf/' /etc/proftpd/proftpd.conf
	fi
	$BIN/v-restart-ftp
fi

if echo "$BACKUP_SYSTEM" | grep "google" > /dev/null; then
	echo "[ ! ] Deprecation notice: Backup via Google Cloud has been removed setup backup again via Rclone to reinstate the backup and restore capebilities!"
	add_upgrade_message "Deprecation notice: Backup via Google Cloud has been removed setup backup again via Rclone to reinstate the backup and restore capebilities!"
fi

if [ -f /etc/logrotate.d/httpd-prerotate/awstats ]; then
	echo "[ * ] Update Awstats prerotate to Hestia update method..."
	# Replace awstatst function
	cp -f $HESTIA_INSTALL_DIR/logrotate/httpd-prerotate/awstats /etc/logrotate.d/httpd-prerotate/
fi

if [ "$PHPMYADMIN_KEY" != "" ]; then
	echo "[ * ] Refresh hestia-sso for PMA..."
	$BIN/v-delete-sys-pma-sso quiet
	$BIN/v-add-sys-pma-sso quiet
fi

if [ -f /etc/nginx/nginx.conf ] && [ ! -f /etc/nginx/conf.d/cloudflare.inc ]; then
	echo "[ * ] Enable support for updating Cloudflare Ips..."
	sed -i '/set_real_ip_from/d' /etc/nginx/nginx.conf
	sed -i '/real_ip_header/d' /etc/nginx/nginx.conf
	sed -i 's|# Cloudflare https://www.cloudflare.com/ips|# Cloudflare https://www.cloudflare.com/ips\n    include /etc/nginx/conf.d/cloudflare.inc;|g' /etc/nginx/nginx.conf
	# At a later stage a function  will run and will load all the new rules
fi
