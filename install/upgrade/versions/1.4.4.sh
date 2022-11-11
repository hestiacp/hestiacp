#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.4

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ -d "/etc/nginx/conf.d/" ]; then
    #Add nginx user_agent separation to desktop/mobile
    cp -f $HESTIA_INSTALL_DIR/nginx/agents.conf /etc/nginx/conf.d/
fi

if [ -d "/etc/phpmyadmin/" ]; then
    echo "[ * ] Secure PHPmyAdmin"
    # limit access to /etc/phpmyadmin/ and /usr/share/phpmyadmin/tmp and so on
    chown -R root:www-data /etc/phpmyadmin/
    chmod -R 640  /etc/phpmyadmin/*
    if [ -d "/etc/phpmyadmin/conf.d/" ]; then
        chmod 750 /etc/phpmyadmin/conf.d/
    fi
    if [ -d "/var/lib/phpmyadmin/tmp" ]; then
        chown root:www-data /usr/share/phpmyadmin/tmp
        chmod 770 /usr/share/phpmyadmin/tmp
    fi
    if [ -d "/var/lib/phpmyadmin/tmp" ]; then
        chmod 770 /var/lib/phpmyadmin/tmp
        chown root:www-data /usr/share/phpmyadmin/tmp
    fi
fi

# Reset PMA SSO to fix bug with Nginx + Apache2
if [ "$PHPMYADMIN_KEY" != "" ]; then
    echo "[ * ] Refressh hestia-sso for PMA..."
    $BIN/v-delete-sys-pma-sso quiet
    $BIN/v-add-sys-pma-sso quiet
fi