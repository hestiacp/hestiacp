#!/bin/bash

# This script migrates your apache2 installation form mod_prefork to mpm_event.

# Includes
source $HESTIA/conf/hestia.conf

# Check if apache2 is in use
if [ "$WEB_SYSTEM" != "apache2" ]; then
    echo "Apache2 isn't installed on your system, canceling migration..." && exit 1
fi

# Check if PHP-FPM is instaled
if [ "$WEB_BACKEND" != "php-fpm" ]; then
    echo "PHP-FPM not yet installed please run migrate_apache.sh first"  && exit 1
fi

# Check if mod_event is already enabled
if [ $(a2query -M) = 'event' ]; then
    echo "mod_event is already enabled, canceling migration..." && exit 1
fi

if ! apache2ctl configtest > /dev/null 2>&1; then
    echo "Apache2 configtest failed" && exit 1
fi

a2modules="php5.6 php7.0 php7.1 php7.2 php7.3 php7.4 ruid2 mpm_itk mpm_prefork"
changed_a2modules=""

for module in $a2modules; do
    a2query -q -m "$module" || continue
    a2dismod -q "$module"
    changed_a2modules="${changed_a2modules} ${module}"
done

a2enmod --quiet mpm_event
cp -f /usr/local/hestia/install/deb/apache2/hestia-event.conf /etc/apache2/conf.d/

# Check if all went well
if ! apache2ctl configtest >/dev/null 2>&1; then
    echo "Something went wrong, rolling back. Please try to migrate manually to mpm_event."
    a2dismod -q mpm_event
    for module in $changed_a2modules; do
        a2enmod "$module"
    done
    rm --force /etc/apache2/conf.d/hestia-event.conf

    exit 1
fi

# Validate if www.conf is existent and port 9000 is active
if ! lsof -Pi :9000 -sTCP:LISTEN -t >/dev/null; then
    if [ $(ls /etc/php/7.3/fpm/pool.d/www.conf) ]; then
        # Replace listen port to 9000
        sed -i "s/listen = 127.0.0.1:.*/listen = 127.0.0.1:9000/g" /etc/php/7.3/fpm/pool.d/www.conf
    else
        # Copy www.conf file
        cp -f /usr/local/hestia/install/deb/php-fpm/www.conf /etc/php/7.3/fpm/pool.d/
    fi
    # Restart php7.3 fpm service.
    systemctl restart php7.3-fpm
fi

# Check again if port 9000 is now in use.
if lsof -Pi :9000 -sTCP:LISTEN -t >/dev/null; then
    echo "mpm_event module was successfully activated."
else
    echo "There went something wrong with your php-fpm configuration - port 9000 isnt active. Please check if webmail and phpmyadmin (if installed) are working properly."
fi

systemctl restart apache2
