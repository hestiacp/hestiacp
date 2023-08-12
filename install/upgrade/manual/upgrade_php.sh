#!/bin/bash

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

phpnewversion=7.4
phpoldversion=7.3

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

if [ ! -x "$(command -v php)" ]; then
	echo "PHP is not installed. Aborting."
	exit 1
fi

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

echo "PHP current version : $phpoldversion"
echo "PHP target version  : $phpnewversion"

echo "Do you want to upgrade PHP now? [Y|N]"
read upgradeconfirmation
if [ "$upgradeconfirmation" = "Y" ] || [ "$upgradeconfirmation" = "y" ]; then
	echo "Process: Upgrading PHP to $phpnewversion"
	echo ""
	cd /tmp
	dpkg-query --showformat='${Package}\t\n' --show | grep php$phpoldversion > /tmp/phpoldpackages.txt
	cp -a /tmp/phpoldpackages.txt /tmp/phpnewpackages.txt
	sed -i "s|$phpoldversion|$phpnewversion|g" /tmp/phpnewpackages.txt
	apt-get update > /dev/null 2>&1
	apt-get install $(cat /tmp/phpnewpackages.txt)
	update-rc.d php$phpnewversion-fpm defaults
	mv /etc/php/$phpoldversion/cli/php.ini /etc/php/$phpnewversion/cli/php.ini
	mv /etc/php/$phpoldversion/fpm/php.ini /etc/php/$phpnewversion/fpm/php.ini
	sed -i "s|$phpoldversion|$phpnewversion|g" /etc/php/$phpoldversion/fpm/php-fpm.conf
	mv /etc/php/$phpoldversion/fpm/php-fpm.conf /etc/php/$phpnewversion/fpm/php-fpm.conf
	rm -rf /etc/php/$phpnewversion/fpm/pool.d
	mkdir -p /etc/php/$phpnewversion/fpm/pool.d
	mv /etc/php/$phpoldversion/fpm/pool.d/* /etc/php/$phpnewversion/fpm/pool.d
	mv /etc/logrotate.d/php$phpoldversion-fpm /etc/logrotate.d/php$phpnewversion-fpm
	sed -i "s|$phpoldversion|$phpnewversion|g" /etc/logrotate.d/php$phpnewversion-fpm
	rm -rf /etc/logrotate.d/php$phpnewversion-fpm.dpkg-dist
	systemctl stop php$phpoldversion-php
	apt-get purge $(cat /tmp/phpoldpackages.txt)
	apt-get -y purge php-imagick
	apt-get -y install php$phpnewversion-imagick
	systemctl restart php$phpnewversion-fpm
	rm -rf /etc/php/$phpoldversion
	rm -rf /var/lib/php/modules/$phpoldversion
	rm -rf /tmp/phpoldpackages.txt
	rm -rf /tmp/phpnewpackages.txt
	if [ -d /var/cache/nginx/micro ]; then
		rm -rf /var/cache/nginx/micro/*
	fi
	systemctl reload nginx
	echo ""
	echo "PHP has been upgraded succcesfully to version $phpnewversion"
else
	echo "Process: Aborted"
	exit 0
fi
