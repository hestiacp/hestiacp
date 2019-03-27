#!/bin/bash
HESTIA="/usr/local/hestia"
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"

# load hestia.conf
source $HESTIA/conf/hestia.conf

# Initialize backup directory structure
# Initialize backup directory
mkdir -p $HESTIA_BACKUP/templates/
mkdir -p $HESTIA_BACKUP/packages/

# load hestia main functions
source /usr/local/hestia/func/main.sh

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
if [ -f $HESTIA/data/firewall/ports.conf ]; then
    rm -f $HESTIA/data/firewall/ports.conf
fi

# Reset backend port
if [ ! -z "$BACKEND_PORT" ]; then
    $HESTIA/bin/v-change-sys-port $BACKEND_PORT
fi

# Set Purge to false in roundcube config - https://goo.gl/3Nja3u
if [ -f /etc/roundcube/config.inc.php ]; then
    sed -i "s/\['flag_for_deletion'] = 'Purge';/\['flag_for_deletion'] = false;/gI" /etc/roundcube/config.inc.php
fi
if [ -f /etc/roundcube/defaults.inc.php ]; then
    sed -i "s/\['flag_for_deletion'] = 'Purge';/\['flag_for_deletion'] = false;/gI" /etc/roundcube/defaults.inc.php
fi
if [ -f /etc/roundcube/main.inc.php ]; then
    sed -i "s/\['flag_for_deletion'] = 'Purge';/\['flag_for_deletion'] = false;/gI" /etc/roundcube/main.inc.php
fi

# Enable spell-check exception dictionary for Roundcube users
if [ -f /etc/roundcube/config.inc.php ]; then
    sed -i "s/\['spellcheck_dictionary'] = false;/\['spellcheck_dictionary'] = true;/gI" /etc/roundcube/config.inc.php
fi
if [ -f /etc/roundcube/defaults.inc.php ]; then
    sed -i "s/\['spellcheck_dictionary'] = false;/\['spellcheck_dictionary'] = true;/gI" /etc/roundcube/defaults.inc.php
fi
if [ -f /etc/roundcube/main.inc.php ]; then
    sed -i "s/\['spellcheck_dictionary'] = false;/\['spellcheck_dictionary'] = true;/gI" /etc/roundcube/main.inc.php
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

cp -rf $HESTIA/install/hestia-data/templates/web/unassigned/* /var/www/html/
cp -rf $HESTIA/install/hestia-data/templates/web/skel/document_errors/* /var/www/document_errors/
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

# Add unassigned hosts configuration to nginx and apache2
if [ -f /usr/local/hestia/data/ips/* ]; then
    for ip in /usr/local/hestia/data/ips/*; do
        ipaddr=${ip##*/}
        rm -f /etc/nginx/conf.d/$ip.conf
        cp -f $HESTIA/install/hestia-data/nginx/unassigned.inc /etc/nginx/conf.d/$ipaddr.conf
        sed -i 's/directIP/'$ipaddr'/g' /etc/nginx/conf.d/$ipaddr.conf

        rm -f /etc/apache2/conf.d/$ip.conf
        cp -f $HESTIA/install/hestia-data/apache2/unassigned.conf /etc/apache2/conf.d/$ipaddr.conf
        sed -i 's/directIP/'$ipaddr'/g' /etc/apache2/conf.d/$ipaddr.conf
    done
fi

# Move clamav to proper location - https://goo.gl/zNuM11
if [ ! -d $HESTIA/web/edit/server/clamav-daemon ]; then
    mv $HESTIA/web/edit/server/clamd $HESTIA/web/edit/server/clamav-daemon
fi

# Fix dovecot configuration
if [ -f /etc/dovecot/conf.d/15-mailboxes.conf ]; then
    # Remove mailboxes configuration if it exists
    rm -f /etc/dovecot/conf.d/15-mailboxes.conf
fi

if [ -f /etc/dovecot/dovecot.conf ]; then
    # Update dovecot configuration
    cp -f $HESTIA/install/hestia-data/dovecot/dovecot.conf /etc/dovecot/dovecot.conf
fi

# Remove old OS-specific installation files if they exist to free up space
if [ -d $HESTIA/install/ubuntu ]; then
    rm -rf $HESTIA/install/ubuntu
fi
if [ -d $HESTIA/install/debian ]; then
    rm -rf $HESTIA/install/debian
fi

# Remove old webmail configuration files in favor of per-domain configuration
if [ -f /etc/nginx/conf.d/webmail.inc ]; then
    rm -f /etc/nginx/conf.d/webmail.inc
fi
if [ -f /etc/apache2/conf.d/roundcube.conf ]; then
    rm -f /etc/apache2/conf.d/roundcube.conf
fi

# Update user information for mail domain SSL configuration
userlist=$(ls --sort=time $HESTIA/data/users/)
for user in $userlist; do
    USER_DATA="$HESTIA/data/users/$user"
    # Update user counter if SSL variable doesn't exist
    if [ -z "$(grep "U_MAIL_SSL" $USER_DATA/user.conf)" ]; then
        echo "U_MAIL_SSL='0'" >> $USER_DATA/user.conf
    fi

    # Update mail configuration file
    conf="$USER_DATA/mail.conf"
    while read line ; do
        eval $line
        
        add_object_key "mail" 'DOMAIN' "$DOMAIN" 'SSL' 'SUSPENDED'
        update_object_value 'mail' 'DOMAIN' "$DOMAIN" '$SSL' 'no'
        
        add_object_key "mail" 'DOMAIN' "$DOMAIN" 'LETSENCRYPT' 'SUSPENDED'
        update_object_value 'mail' 'DOMAIN' "$DOMAIN" '$LETSENCRYPT' 'no'
    done < $conf
done

# Update exim configuration to support multi-domain mail SSL
if [ -f /etc/exim4/exim4.conf.template ]; then
    rm -f /etc/exim4/exim4.conf.template
    cp -f $HESTIA/install/hestia-data/exim/exim4.conf.template /etc/exim4/

    # Reconfigure spam filter and virus scanning
    if [ ! -z "$ANTISPAM_SYSTEM" ]; then
        sed -i "s/#SPAM/SPAM/g" /etc/exim4/exim4.conf.template
    fi
    if [ ! -z "$ANTIVIRUS_SYSTEM" ]; then
        sed -i "s/#CLAMD/CLAMD/g" /etc/exim4/exim4.conf.template
    fi

fi

# Update web and proxy service configuration files to include user domains
if [ -f /etc/$WEB_SYSTEM/$WEB_SYSTEM.conf ]; then
    cp $HESTIA/install/hestia-data/$WEB_SYSTEM/$WEB_SYSTEM.conf /etc/$WEB_SYSTEM/$WEB_SYSTEM.conf 
fi
if [ -f /etc/$PROXY_SYSTEM/nginx.conf ]; then
    cp $HESTIA/install/hestia-data/$PROXY_SYSTEM/$PROXY_SYSTEM.conf /etc/$PROXY_SYSTEM/$PROXY_SYSTEM.conf
fi

# Add webmail alias variable to system configuration
sed -i "/WEBMAIL_ALIAS='mail'/d" $HESTIA/conf/hestia.conf
echo "WEBMAIL_ALIAS='mail'" >> $HESTIA/conf/hestia.conf

# Remove webalizer in favor of awstats as default
apt purge webalizer -y > /dev/null 2>&1
sed -i "s/STATS_SYSTEM='webalizer,awstats'/STATS_SYSTEM='awstats'/g" $HESTIA/conf/hestia.conf

# Rebuild users
userlist=$(ls --sort=time $HESTIA/data/users/)
for user in $userlist; do
    echo "Rebuilding user: $user ..."
    v-rebuild-user $user $restart
done

# Restart services
$HESTIA/bin/v-restart-web $restart
$HESTIA/bin/v-restart-proxy $restart
$HESTIA/bin/v-restart-mail $restart
$HESTIA/bin/v-restart-service $IMAP_SYSTEM $restart
