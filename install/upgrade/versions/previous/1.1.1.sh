#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.1.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Remove 5s delay when sending mail through exim4
if [ -e "/etc/exim4/exim4.conf.template" ]; then
    echo "[ * ] Updating exim4 configuration..."
    sed -i "s|rfc1413_query_timeout = 5s|rfc1413_query_timeout = 0s|g" /etc/exim4/exim4.conf.template
fi

# Fix phpMyAdmin blowfish and tmp directory issues
if [ -e "/usr/share/phpmyadmin/libraries/vendor_config.php" ]; then
    echo "[ * ] Updating phpMyAdmin configuration..."
    sed -i "s|define('CONFIG_DIR', ROOT_PATH);|define('CONFIG_DIR', '/etc/phpmyadmin/');|" /usr/share/phpmyadmin/libraries/vendor_config.php
    sed -i "s|define('TEMP_DIR', ROOT_PATH . 'tmp/');|define('TEMP_DIR', '/var/lib/phpmyadmin/tmp/');|" /usr/share/phpmyadmin/libraries/vendor_config.php
fi
