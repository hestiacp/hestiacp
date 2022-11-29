#!/bin/bash

# Function Description
# Manual upgrade script from Nginx + Apache2 + PHP-FPM to Nginx + PHP-FPM

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
# shellcheck source=/etc/hestiacp/hestia.conf
source /etc/hestiacp/hestia.conf
# shellcheck source=/usr/local/hestia/func/main.sh
source $HESTIA/func/main.sh
# shellcheck source=/usr/local/hestia/conf/hestia.conf
source $HESTIA/conf/hestia.conf

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

if [ "$WEB_BACKEND" != "php-fpm" ]; then
	check_result $E_NOTEXISTS "PHP-FPM is not enabled" > /dev/null
	exit 1
fi

if [ "$WEB_SYSTEM" != "apache2" ]; then
	check_result $E_NOTEXISTS "Apache2 is not enabled" > /dev/null
	exit 1
fi

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

# Remove apache2 from config
sed -i "/^WEB_PORT/d" $HESTIA/conf/hestia.conf
sed -i "/^WEB_SSL/d" $HESTIA/conf/hestia.conf
sed -i "/^WEB_SSL_PORT/d" $HESTIA/conf/hestia.conf
sed -i "/^WEB_RGROUPS/d" $HESTIA/conf/hestia.conf
sed -i "/^WEB_SYSTEM/d" $HESTIA/conf/hestia.conf

# Remove nginx (proxy) from config
sed -i "/^PROXY_PORT/d" $HESTIA/conf/hestia.conf
sed -i "/^PROXY_SSL_PORT/d" $HESTIA/conf/hestia.conf
sed -i "/^PROXY_SYSTEM/d" $HESTIA/conf/hestia.conf

# Add Nginx settings to config
echo "WEB_PORT='80'" >> $HESTIA/conf/hestia.conf
echo "WEB_SSL='openssl'" >> $HESTIA/conf/hestia.conf
echo "WEB_SSL_PORT='443'" >> $HESTIA/conf/hestia.conf
echo "WEB_SYSTEM='nginx'" >> $HESTIA/conf/hestia.conf

# Rebuild web config

for user in $($HESTIA/bin/v-list-users plain | cut -f1); do
	echo $user
	for domain in $($HESTIA/bin/v-list-web-domains $user plain | cut -f1); do
		$HESTIA/bin/v-change-web-domain-tpl $user $domain 'default'
		$HESTIA/bin/v-rebuild-web-domain $user $domain no
	done
done

systemctl restart nginx
