#!/bin/bash
# info: enable multiphp
#
# This function enables php-fpm backend for standalone apache2 configurations.

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
# shellcheck source=/usr/local/DevIT/func/main.sh
source $DevIT/func/main.sh
# shellcheck source=/usr/local/DevIT/conf/DevIT.conf
source $DevIT/conf/DevIT.conf

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

if [ ! -z "$WEB_BACKEND" ]; then
	check_result $E_EXISTS "Web backend already enabled" > /dev/null
fi

if [ "$(multiphp_count)" -gt 1 ]; then
	check_result $E_EXISTS "Multiphp already enabled" > /dev/null
fi

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

php_v="$(multiphp_default_version)"

$BIN/v-add-web-php "$php_v"

cp -f "${DevIT_INSTALL_DIR}/php-fpm/www.conf" "/etc/php/${php_v}/fpm/pool.d/www.conf"
systemctl start php${php_v}-fpm
check_result $? "php${php_v}-fpm start failed"
update-alternatives --set php /usr/bin/php${php_v}

if [ ! -z "$WEB_SYSTEM" ]; then
	cp -rf "${DevIT_INSTALL_DIR}/templates/web/$WEB_SYSTEM" "${WEBTPL}/"
fi

sed -i "/^WEB_BACKEND=/d" $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf
echo "WEB_BACKEND='php-fpm'" >> $DevIT/conf/DevIT.conf
echo "WEB_BACKEND='php-fpm'" >> $DevIT/conf/defaults/DevIT.conf

for user in $($BIN/v-list-sys-users plain); do
	# Define user data and get suspended status
	USER_DATA=$DevIT/data/users/$user
	SUSPENDED=$(get_user_value '$SUSPENDED')

	# Check if user is suspended
	if [ "$SUSPENDED" = "yes" ]; then
		suspended="yes"
		$BIN/v-unsuspend-user $user
	fi

	for domain in $($BIN/v-list-web-domains $user plain | cut -f1); do
		SUSPENDED_WEB=$(get_object_value 'web' 'DOMAIN' "$domain" '$SUSPENDED')
		# Check if web domain is suspended
		if [ "$SUSPENDED_WEB" = "yes" ]; then
			suspended_web="yes"
			$BIN/v-unsuspend-web-domain $user $domain
		fi

		echo "Processing domain: $domain"
		$BIN/v-change-web-domain-backend-tpl "$user" "$domain" "PHP-${php_v/\./_}" "no"
		$BIN/v-change-web-domain-tpl "$user" "$domain" "default" "no"

		# Suspend domain again, if it was suspended
		if [ "$suspended_web" = "yes" ]; then
			unset suspended_web
			$BIN/v-suspend-web-domain $user $domain
		fi
	done

	# Suspend user again, if he was suspended
	if [ "$suspended" = "yes" ]; then
		unset suspended
		$BIN/v-suspend-user $user
	fi
done

$BIN/v-update-web-templates "yes"

# Restarting backend
$BIN/v-restart-web-backend "yes"
check_result $? "Backend restart" > /dev/null 2>&1

#----------------------------------------------------------#
#                       DevIT                             #
#----------------------------------------------------------#

# Logging
log_history "Enabled multiphp $version" '' 'admin'
log_event "$OK" "$ARGUMENTS"

exit
