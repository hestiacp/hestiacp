#!/bin/bash

# Set version(s)
pma_v='4.8.5'

# Upgrade phpMyAdmin
if [ "$DB_SYSTEM" = 'mysql' ]; then
    # Display upgrade information
    echo "Upgrade phpMyAdmin to v$pma_v..."

    # Download latest phpMyAdmin release
    wget --quiet https://files.phpmyadmin.net/phpMyAdmin/$pma_v/phpMyAdmin-$pma_v-all-languages.tar.gz

    # Unpack files
    tar xzf phpMyAdmin-$pma_v-all-languages.tar.gz

    # Delete file to prevent error
    rm -fr /usr/share/phpmyadmin/doc/html

    # Overwrite old files
    cp -rf phpMyAdmin-$pma_v-all-languages/* /usr/share/phpmyadmin

    # Set config and log directory
    sed -i "s|define('CONFIG_DIR', '');|define('CONFIG_DIR', '/etc/phpmyadmin/');|" /usr/share/phpmyadmin/libraries/vendor_config.php
    sed -i "s|define('TEMP_DIR', './tmp/');|define('TEMP_DIR', '/var/lib/phpmyadmin/tmp/');|" /usr/share/phpmyadmin/libraries/vendor_config.php

    # Create temporary folder and change permissions
    if [ ! -d /usr/share/phpmyadmin/tmp ]; then
        mkdir /usr/share/phpmyadmin/tmp
        chmod 777 /usr/share/phpmyadmin/tmp
    fi

    # Clean up
    rm -fr phpMyAdmin-$pma_v-all-languages
    rm -f phpMyAdmin-$pma_v-all-languages.tar.gz
fi

# Add amd64 to repositories to prevent notifications - https://goo.gl/hmsSV7
if ! grep -q 'arch=amd64' /etc/apt/sources.list.d/nginx.list; then
    sed -i s/"deb "/"deb [arch=amd64] "/g /etc/apt/sources.list.d/nginx.list
fi
if ! grep -q 'arch=amd64' /etc/apt/sources.list.d/mariadb.list; then
    sed -i s/"deb "/"deb [arch=amd64] "/g /etc/apt/sources.list.d/mariadb.list
fi

# Fix named rule for AppArmor - https://goo.gl/SPqHdq
if [ "$DNS_SYSTEM" = 'bind9' ] && [ ! -f /etc/apparmor.d/local/usr.sbin.named ]; then
        echo "/home/** rwm," >> /etc/apparmor.d/local/usr.sbin.named 2> /dev/null
fi

# Remove obsolete ports.conf if exists.
if [ -f /usr/local/hestia/data/firewall/ports.conf ]; then
    rm -f /usr/local/hestia/data/firewall/ports.conf
fi

# Reset backend port
if [ ! -z "$BACKEND_PORT" ]; then
    /usr/local/hestia/bin/v-change-sys-port $BACKEND_PORT
fi


# Update default page templates
echo '************************************************************************'
echo "Replacing default templates and packages...                             "
echo "Existing templates have been backed up to the following location:       "
echo "$HESTIA_BACKUP/templates/                                               "
echo '************************************************************************'

# Back up default package and install latest version
if [ -d $HESTIA/data/packages/ ]; then
    cp -f $HESTIA/data/packages/default.pkg $HESTIA_BACKUP/packages/
fi

# Back up old template files and install the latest versions
if [ -d $HESTIA/data/templates/ ]; then
    cp -rf $HESTIA/data/templates $HESTIA_BACKUP/
    $HESTIA/bin/v-update-web-templates
    $HESTIA/bin/v-update-dns-templates
    $HESTIA/bin/v-update-mail-templates
    $HESTIA/bin/v-update-sys-packages
fi

# Remove old Office 365 template as there is a newer version with an updated name
if [ -f $HESTIA/data/templates/dns/o365.tpl ]; then 
    rm -f $HESTIA/data/templates/dns/o365.tpl
fi

# Back up and remove default index.html if it exists
if [ -f /var/www/html/index.html ]; then
    cp -rf /var/www/html/index.html $HESTIA_BACKUP/templates/
    rm -rf /var/www/html/index.html
fi

# Configure default success page and set permissions on CSS, JavaScript, and Font dependencies for unassigned hosts
if [ ! -d /var/www/html ]; then
    mkdir -p /var/www/html/
fi

if [ ! -d /var/www/document_errors/ ]; then
    mkdir -p /var/www/document_errors/
fi

cp -rf $HESTIA/install/deb/templates/web/unassigned/* /var/www/html/
cp -rf $HESTIA/install/deb/templates/web/skel/document_errors/* /var/www/document_errors/
chmod 644 /var/www/html/*
chmod 751 /var/www/html/css
chmod 751 /var/www/html/js
chmod 751 /var/www/html/webfonts
chmod 644 /var/www/document_errors/*
chmod 751 /var/www/document_errors/css
chmod 751 /var/www/document_errors/js
chmod 751 /var/www/document_errors/webfonts

# Correct permissions on CSS, JavaScript, and Font dependencies for default templates
chmod 751 $HESTIA/data/templates/web/skel/document_errors/css
chmod 751 $HESTIA/data/templates/web/skel/document_errors/js
chmod 751 $HESTIA/data/templates/web/skel/document_errors/webfonts
chmod 751 $HESTIA/data/templates/web/skel/public_*html/css
chmod 751 $HESTIA/data/templates/web/skel/public_*html/js
chmod 751 $HESTIA/data/templates/web/skel/public_*html/webfonts
chmod 751 $HESTIA/data/templates/web/suspend/css
chmod 751 $HESTIA/data/templates/web/suspend/js
chmod 751 $HESTIA/data/templates/web/suspend/webfonts
chmod 751 $HESTIA/data/templates/web/unassigned/css
chmod 751 $HESTIA/data/templates/web/unassigned/js
chmod 751 $HESTIA/data/templates/web/unassigned/webfonts

# Set Purge to false in roundcube config - https://goo.gl/3Nja3u
if [ -f /etc/roundcube/config.inc.php ]; then
    sed -i "s/deletion'] = 'Purge'/deletion'] = false/g" /etc/roundcube/config.inc.php
fi
if [ -f /etc/roundcube/main.inc.php ]; then
    sed -i "s/deletion'] = 'Purge'/deletion'] = false/g" /etc/roundcube/main.inc.php
fi

# Copy default "Success" page for unassigned hosts
cp -rf /usr/local/hestia/install/ubuntu/18.04/templates/web/unassigned/* /var/www/

# Move clamav to proper location - https://goo.gl/zNuM11
if [ ! -d /usr/local/hestia/web/edit/server/clamav-daemon ]; then
    mv /usr/local/hestia/web/edit/server/clamd /usr/local/web/edit/server/clamav-daemon
fi

# Fix dovecot configuration
if [ -f /etc/dovecot/conf.d/15-mailboxes.conf ]; then
    # Remove mailboxes configuration if it exists
    rm -f /etc/dovecot/conf.d/15-mailboxes.conf
fi
if [ -f /etc/dovecot/dovecot.conf ]; then
    # Update dovecot configuration and restart dovecot service
    cp -f /usr/local/hestia/install/hestia-data/dovecot/dovecot.conf /etc/dovecot/dovecot.conf
    systemctl restart dovecot
fi

# Rebuild mailboxes
for user in `ls /usr/local/hestia/data/users/`; do
    v-rebuild-mail-domains $user
done