#!/bin/bash

# Function Description
# Manual upgrade script from Nginx + Apache2 + PHP-FPM to Nginx + PHP-FPM

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
# shellcheck source=/etc/DevITcp/DevIT.conf
source /etc/DevITcp/DevIT.conf
# shellcheck source=/usr/local/DevIT/func/main.sh
source $DevIT/func/main.sh
# shellcheck source=/usr/local/DevIT/conf/DevIT.conf
source $DevIT/conf/DevIT.conf

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
sed -i "/^WEB_PORT/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf
sed -i "/^WEB_SSL/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf
sed -i "/^WEB_SSL_PORT/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf
sed -i "/^WEB_RGROUPS/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf
sed -i "/^WEB_SYSTEM/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf

# Remove nginx (proxy) from config
sed -i "/^PROXY_PORT/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf
sed -i "/^PROXY_SSL_PORT/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf
sed -i "/^PROXY_SYSTEM/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf

# Add Nginx settings to config
echo "WEB_PORT='80'" >> $DevIT/conf/DevIT.conf
echo "WEB_SSL='openssl'" >> $DevIT/conf/DevIT.conf
echo "WEB_SSL_PORT='443'" >> $DevIT/conf/DevIT.conf
echo "WEB_SYSTEM='nginx'" >> $DevIT/conf/DevIT.conf

# Add Nginx settings to config
echo "WEB_PORT='80'" >> $DevIT/conf/defaults/DevIT.conf
echo "WEB_SSL='openssl'" >> $DevIT/conf/defaults/DevIT.conf
echo "WEB_SSL_PORT='443'" >> $DevIT/conf/defaults/DevIT.conf
echo "WEB_SYSTEM='nginx'" >> $DevIT/conf/defaults/DevIT.conf

rm $DevIT/conf/defaults/DevIT.conf
cp $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf

# Rebuild web config

for user in $($BIN/v-list-users plain | cut -f1); do
	echo $user
	for domain in $($BIN/v-list-web-domains $user plain | cut -f1); do
		$BIN/v-change-web-domain-tpl $user $domain 'default'
		$BIN/v-rebuild-web-domain $user $domain no
	done
done

systemctl restart nginx
