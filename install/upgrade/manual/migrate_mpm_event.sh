#!/bin/bash

# This script migrates your apache2 installation form mod_prefork to mpm_event.

# Includes
source $HESTIA/conf/hestia.conf

# Check if apache2 is in use
if [ "$WEB_SYSTEM" != "apache2" ]; then
    echo "Apache2 isnt installed on your system, canceling migration..."
    exit
fi

# Check if mod_event is already enabled
if apache2ctl -M | grep -q mpm_event_module; then
    echo "mod_event is already enabled, canceling migration..."
    exit
fi

# Disable prefork and php, enable event
a2dismod php5.6 > /dev/null 2>&1
a2dismod php7.0 > /dev/null 2>&1
a2dismod php7.1 > /dev/null 2>&1
a2dismod php7.2 > /dev/null 2>&1
a2dismod php7.3 > /dev/null 2>&1
a2dismod php7.4 > /dev/null 2>&1
a2dismod mpm_prefork > /dev/null 2>&1
a2dismod mpm_itk > /dev/null 2>&1
a2dismod ruid2 > /dev/null 2>&1
a2enmod mpm_event > /dev/null 2>&1

# Restart apache2 service
systemctl restart apache2

# Check if all went well
if apache2ctl -M | grep -q mpm_event_module; then
    echo "mpm_event module was successfully activated."
else
    echo "Something went wrong, please try to migrate manualy to mpm_event."
fi
