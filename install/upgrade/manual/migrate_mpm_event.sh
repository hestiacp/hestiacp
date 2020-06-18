#!/bin/bash

# This script migrates your apache2 installation form mod_prefork to mpm_event.

# Includes
source $HESTIA/conf/hestia.conf

# Check if apache2 is in use
if [ "$WEB_SYSTEM" != "apache2" ]; then
    echo "Apache2 isn't installed on your system, canceling migration..." && exit 1
fi

#Check if PHP-FPM is instaled 
if [ "$WEB_BACKEND" != "php-fpm"]; then
    echo "PHP-FPM not yet installed please run migrate_apache.sh first"  && exit 1
fi

# Check if mod_event is already enabled
if [ $(a2query -M) = 'event' ]; then
    echo "mod_event is already enabled, canceling migration..." && exit 1
fi

if ! apache2ctl configtest > /dev/null 2>&1; then
    echo "Apache2 configtest failed" && exit 1
fi

a2modules="php5.6 php7.0 php7.1 php7.2 php7.3 php7.4 mpm_prefork mpm_itk ruid2"
changed_a2modules=""

for module in $a2modules; do
    a2query -q -m "$module" || continue
    a2dismod "$module"
    changed_a2modules="${changed_a2modules} ${module}"
done

a2enmod --quiet mpm_event

# Check if all went well
if ! apache2ctl configtest >/dev/null 2>&1; then
    echo "Something went wrong, rolling back. Please try to migrate manually to mpm_event."
    for module in $changed_a2modules; do
        a2enmod "$module"
    done
    exit 1
fi

echo "mpm_event module was successfully activated."
systemctl restart apache2
