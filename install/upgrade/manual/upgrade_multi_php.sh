#!/bin/bash
# Update www.conf to a different version so users can safely delete older php version.
# www.conf is used for Roundcube, Rainloop, SnappyMail and phpmyadmin
# Removal of the "www.conf" php version will cause issues with Rainloop not working. Current script updates it to the latest version of PHP installed. If that is not wanted use this script

version=$1
if [ ! -x "$(command -v php)" ]; then
	echo "PHP is not installed. Aborting."
	exit 1
fi

# Verify php version format
if [[ ! $version =~ ^[0-9]\.[0-9]+ ]]; then
	echo "The PHP version format is invalid, it should look like [0-9].[0-9]."
	echo "Example:  7.0, 7.4"
	exit
fi

if [ ! -f /etc/php/$version/fpm/pool.d/dummy.conf ]; then
	echo "PHP versions doesn't exists"
	exit
fi

rm -f /etc/php/*/fpm/pool.d/www.conf
cp -f $HESTIA/install/deb/php-fpm/www.conf /etc/php/$version/fpm/pool.d/www.conf
$HESTIA/bin/v-restart-web-backend
