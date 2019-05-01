#!/bin/bash

# define vars
HESTIA="/usr/local/hestia"
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"
hestiacp="$HESTIA/install/deb"

# Add webmail alias variable to system configuration
sed -i "/WEBMAIL_ALIAS/d" $HESTIA/conf/hestia.conf
echo "WEBMAIL_ALIAS='webmail'" >> $HESTIA/conf/hestia.conf

# load hestia.conf
source $HESTIA/conf/hestia.conf

# load hestia main functions
source /usr/local/hestia/func/main.sh

# Initialize backup directory
mkdir -p $HESTIA_BACKUP/conf/
mkdir -p $HESTIA_BACKUP/packages/
mkdir -p $HESTIA_BACKUP/templates/

# Detect OS
case $(head -n1 /etc/issue | cut -f 1 -d ' ') in
    Debian)     os="debian" ;;
    Ubuntu)     os="ubuntu" ;;
esac

# Detect release for Debian
if [ "$os" = "debian" ]; then
    release=$(cat /etc/debian_version|grep -o [0-9]|head -n1)
    VERSION='debian'
elif [ "$os" = "ubuntu" ]; then
    release="$(lsb_release -s -r)"
    VERSION='ubuntu'
fi


# Clear the screen from apt output to prepare for upgrade installer experience
clear
echo
echo '     _   _           _   _        ____ ____  '
echo '    | | | | ___  ___| |_(_) __ _ / ___|  _ \ '
echo '    | |_| |/ _ \/ __| __| |/ _` | |   | |_) |'
echo '    |  _  |  __/\__ \ |_| | (_| | |___|  __/ '
echo '    |_| |_|\___||___/\__|_|\__,_|\____|_|    '
echo
echo -e "\n\n"
echo "       Hestia Control Panel Upgrade Script"
echo "==================================================="
echo ""
echo "Existing files will be backed up to the following location:"
echo "$HESTIA_BACKUP/"
echo ""
echo "This process may take a few moments, please wait..."
echo ""

# Update Apache and Nginx configuration to support new file structure
if [ -f /etc/apache2/apache.conf ]; then
    echo "(*) Updating Apache configuration..."
    mv  /etc/apache2/apache.conf $HESTIA_BACKUP/conf/
    cp -f $HESTIA/install/deb/apache2/apache.conf /etc/apache2/apache.conf
fi
if [ -f /etc/nginx/nginx.conf ]; then
    echo "(*) Updating Nginx configuration..."
    mv  /etc/nginx/nginx.conf $HESTIA_BACKUP/conf/
    cp -f $HESTIA/install/deb/nginx/nginx.conf /etc/nginx/nginx.conf
fi

# Generating dhparam
if [ ! -e /etc/ssl/dhparam.pem ]; then
    echo "(*) Enabling HTTPS Strict Transport Security (HSTS) support..."
    mv  /etc/nginx/nginx.conf $HESTIA_BACKUP/conf/
    cp -f $hestiacp/nginx/nginx.conf /etc/nginx/

    # Copy dhparam
    cp -f $hestiacp/ssl/dhparam.pem /etc/ssl/

    # Update DNS servers in nginx.conf
    dns_resolver=$(cat /etc/resolv.conf | grep -i '^nameserver' | cut -d ' ' -f2 | tr '\r\n' ' ' | xargs)
    sed -i "s/1.0.0.1 1.1.1.1/$dns_resolver/g" /etc/nginx/nginx.conf

    # Restart Nginx service
    service nginx restart >/dev/null 2>&1
fi

# Update default page templates
echo "(*) Replacing default templates and packages..."

# Back up default package and install latest version
if [ -d $HESTIA/data/packages/ ]; then
    cp -f $HESTIA/data/packages/default.pkg $HESTIA_BACKUP/packages/
fi

# Back up old template files and install the latest versions
if [ -d $HESTIA/data/templates/ ]; then
    cp -rf $HESTIA/data/templates $HESTIA_BACKUP/templates/
    $HESTIA/bin/v-update-web-templates
    $HESTIA/bin/v-update-dns-templates
    $HESTIA/bin/v-update-mail-templates
fi

# Remove old Office 365 template as there is a newer version with an updated name
if [ -f $HESTIA/data/templates/dns/o365.tpl ]; then
    rm -f $HESTIA/data/templates/dns/o365.tpl
fi

# Back up and remove default index.html if it exists
if [ -f /var/www/html/index.html ]; then
    mv /var/www/html/index.html $HESTIA_BACKUP/templates/
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

# Correct other permissions
chown bind:bind /var/cache/bind
chmod 640 /etc/roundcube/debian-db*
chown root:www-data /etc/roundcube/debian-db*

# Add unassigned hosts configuration to Nginx and Apache
if [ "$WEB_SYSTEM" = "apache2" ]; then
    echo "(*) Adding unassigned hosts configuration to Apache..."
    if [ -f /usr/local/hestia/data/ips/* ]; then
        for ip in /usr/local/hestia/data/ips/*; do
            ipaddr=${ip##*/}
            rm -f /etc/apache2/conf.d/$ip.conf
            cp -f $HESTIA/install/deb/apache2/unassigned.conf /etc/apache2/conf.d/$ipaddr.conf
            sed -i 's/directIP/'$ipaddr'/g' /etc/apache2/conf.d/$ipaddr.conf
        done
    fi
fi
if [ "$PROXY_SYSTEM" = "nginx" ]; then
    echo "(*) Adding unassigned hosts configuration to Nginx..."
    if [ -f /usr/local/hestia/data/ips/* ]; then
        for ip in /usr/local/hestia/data/ips/*; do
            ipaddr=${ip##*/}
            rm -f /etc/nginx/conf.d/$ip.conf
            cp -f $HESTIA/install/deb/nginx/unassigned.inc /etc/nginx/conf.d/$ipaddr.conf
            sed -i 's/directIP/'$ipaddr'/g' /etc/nginx/conf.d/$ipaddr.conf
        done
    fi
fi

# Fix empty pool error message for MultiPHP
php_versions=$( ls -l /etc/php/ | grep ^d | wc -l )
if [ "$php_versions" -gt 1 ]; then
    for v in $(ls /etc/php/); do
        cp -f $hestiacp/php-fpm/dummy.conf /etc/php/$v/fpm/pool.d/
        v1=$(echo "$v" | sed -e 's/[.]//')
        sed -i "s/9999/99$v1/g" /etc/php/$v/fpm/pool.d/dummy.conf
    done
fi

# Set Purge to false in Roundcube configuration - https://goo.gl/3Nja3u
echo "(*) Updating Roundcube configuration..."
if [ -f /etc/roundcube/config.inc.php ]; then
    sed -i "s/\['flag_for_deletion'] = 'Purge';/\['flag_for_deletion'] = false;/gI" /etc/roundcube/config.inc.php
fi
if [ -f /etc/roundcube/defaults.inc.php ]; then
    sed -i "s/\['flag_for_deletion'] = 'Purge';/\['flag_for_deletion'] = false;/gI" /etc/roundcube/defaults.inc.php
fi
if [ -f /etc/roundcube/main.inc.php ]; then
    sed -i "s/\['flag_for_deletion'] = 'Purge';/\['flag_for_deletion'] = false;/gI" /etc/roundcube/main.inc.php
fi

# Remove old OS-specific installation files if they exist to free up space
if [ -d $HESTIA/install/ubuntu ]; then
    echo "(*) Removing old HestiaCP installation files for Ubuntu..."
    rm -rf $HESTIA/install/ubuntu
fi
if [ -d $HESTIA/install/debian ]; then
    echo "(*) Removing old HestiaCP installation files for Debian..."
    rm -rf $HESTIA/install/debian
fi

# Fix Dovecot configuration
echo "(*) Updating Dovecot IMAP/POP server configuration..."
if [ -f /etc/dovecot/conf.d/15-mailboxes.conf ]; then
    mv  /etc/dovecot/conf.d/15-mailboxes.conf $HESTIA_BACKUP/conf/
fi
if [ -f /etc/dovecot/dovecot.conf ]; then
    # Update Dovecot configuration and restart Dovecot service
    mv  /etc/dovecot/dovecot.conf $HESTIA_BACKUP/conf/
    cp -f $HESTIA/install/deb/dovecot/dovecot.conf /etc/dovecot/dovecot.conf
    systemctl restart dovecot
    sleep 0.5
fi

# Fix Exim configuration
if [ -f /etc/exim4/exim4.conf.template ]; then
    echo "(*) Updating Exim SMTP server configuration..."
    mv  /etc/exim4/exim4.conf.template $HESTIA_BACKUP/conf/
    cp -f $HESTIA/install/deb/exim/exim4.conf.template /etc/exim4/exim4.conf.template
    # Reconfigure spam filter and virus scanning
    if [ ! -z "$ANTISPAM_SYSTEM" ]; then
        sed -i "s/#SPAM/SPAM/g" /etc/exim4/exim4.conf.template
        sed -i "s/#SPAM_SCORE/SPAM_SCORE/g" /etc/exim4/exim4.conf.template
    fi
    if [ ! -z "$ANTIVIRUS_SYSTEM" ]; then
        sed -i "s/#CLAMD/CLAMD/g" /etc/exim4/exim4.conf.template
    fi
fi

# Add IMAP system variable to configuration if Dovecot is installed
if [ -z "$IMAP_SYSTEM" ]; then
    if [ -f /usr/bin/dovecot ]; then
        echo "(*) Adding missing IMAP_SYSTEM variable to hestia.conf..."
        echo "IMAP_SYSTEM = 'dovecot'" >> $HESTIA/conf/hestia.conf
    fi
fi

# Remove Webalizer and set AWStats as default
echo "(*) Removing Webalizer and setting AWStats as default web statistics backend..."
apt purge webalizer -y > /dev/null 2>&1
sed -i "s/STATS_SYSTEM='webalizer,awstats'/STATS_SYSTEM='awstats'/g" $HESTIA/conf/hestia.conf

# Run sftp jail once
$HESTIA/bin/v-add-sys-sftp-jail

# Rebuild user
for user in `ls /usr/local/hestia/data/users/`; do
    echo "(*) Rebuilding domains and account for user: $user..."
    v-rebuild-web-domains $user >/dev/null 2>&1
    sleep 1
    v-rebuild-dns-domains $user >/dev/null 2>&1
    sleep 1
    v-rebuild-mail-domains $user >/dev/null 2>&1
    sleep 1
done

# Restart server services
echo "(*) Restarting services..."
sleep 5
$BIN/v-restart-mail $restart
$BIN/v-restart-service $IMAP_SYSTEM $restart
$BIN/v-restart-web $restart
$BIN/v-restart-proxy $restart
$BIN/v-restart-dns $restart

echo ""
echo "    Upgrade complete! Please report any bugs or issues to"
echo "    https://github.com/hestiacp/hestiacp/issues"
echo ""
echo "    We hope that you enjoy this release of Hestia Control Panel,"
echo "    enjoy your day!"
echo ""
echo "    Sincerely,"
echo "    The Hestia Control Panel development team"
echo ""
echo "    www.hestiacp.com"
echo "    Made with love & pride from the open-source community around the world."
echo ""
echo ""
