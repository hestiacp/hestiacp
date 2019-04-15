#!/bin/bash

# define vars
HESTIA="/usr/local/hestia"
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"
hestiacp="$HESTIA/install/deb"

# load hestia.conf
source $HESTIA/conf/hestia.conf

# load hestia main functions
source /usr/local/hestia/func/main.sh

# Initialize backup directory
mkdir -p $HESTIA_BACKUP/templates/
mkdir -p $HESTIA_BACKUP/packages/

echo "(*) Upgrading to Hestia Control Panel v$VERSION..."

# Update Apache and NGINX configuration to support new file structure
if [ -f /etc/apache2/apache.conf ]; then
    echo "(*) Updating Apache configuration..."
    cp -f $HESTIA/install/deb/apache2/apache.conf /etc/apache2/apache.conf
fi
if [ -f /etc/nginx/nginx.conf ]; then
    echo "(*) Updating nginx configuration..."
    cp -f $HESTIA/install/deb/nginx/nginx.conf /etc/nginx/nginx.conf
fi

# Generating dhparam.
if [ ! -e /etc/ssl/dhparam.pem ]; then
    echo "(*) Enabling HTTPS Strict Transport Security (HSTS) support..."

    # Backup existing conf
    mv /etc/nginx/nginx.conf /etc/nginx/nginx.conf.bak

    # Copy new nginx config
    cp -f $hestiacp/nginx/nginx.conf /etc/nginx/

    # Copy dhparam
    cp -f $hestiacp/ssl/dhparam.pem /etc/ssl/

    # Update dns servers in nginx.conf
    dns_resolver=$(cat /etc/resolv.conf | grep -i '^nameserver' | cut -d ' ' -f2 | tr '\r\n' ' ' | xargs)
    sed -i "s/1.0.0.1 1.1.1.1/$dns_resolver/g" /etc/nginx/nginx.conf

    # Restart nginx service
    service nginx restart >/dev/null 2>&1
fi

# Update default page templates
echo "(*) Replacing default templates and packages..."
echo "    Existing templates have been backed up to the following location:"
echo "    $HESTIA_BACKUP/templates/"

# Back up default package and install latest version
if [ -d $HESTIA/data/packages/ ]; then
    cp -f $HESTIA/data/packages/default.pkg $HESTIA_BACKUP/packages/
fi

# Back up old template files and install the latest versions
if [ -d $HESTIA/data/templates/ ]; then
    cp -rf $HESTIA/data/templates $HESTIA_BACKUP/
    $HESTIA/bin/v-update-web-templates
    $HESTIA/bin/v-update-dns-templates
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

# Add unassigned hosts configuration to nginx and apache2
if [ "$WEB_SYSTEM" = "apache2" ]; then
    echo "(*) Adding unassigned hosts configuration to apache2..."
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
    echo "(*) Adding unassigned hosts configuration to nginx..."
    if [ -f /usr/local/hestia/data/ips/* ]; then
        for ip in /usr/local/hestia/data/ips/*; do
            ipaddr=${ip##*/}
            rm -f /etc/nginx/conf.d/$ip.conf
            cp -f $HESTIA/install/deb/nginx/unassigned.inc /etc/nginx/conf.d/$ipaddr.conf
            sed -i 's/directIP/'$ipaddr'/g' /etc/nginx/conf.d/$ipaddr.conf
        done
    fi
fi

# Set Purge to false in roundcube config - https://goo.gl/3Nja3u
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
    echo "(*) Removing old installation data files for Ubuntu..."
    rm -rf $HESTIA/install/ubuntu
fi
if [ -d $HESTIA/install/debian ]; then
    echo "(*) Removing old installation data files for Debian..."
    rm -rf $HESTIA/install/debian
fi

# Fix dovecot configuration
echo "(*) Updating dovecot IMAP/POP server configuration..."
if [ -f /etc/dovecot/conf.d/15-mailboxes.conf ]; then
    # Remove mailboxes configuration if it exists
    rm -f /etc/dovecot/conf.d/15-mailboxes.conf
fi
if [ -f /etc/dovecot/dovecot.conf ]; then
    # Update dovecot configuration and restart dovecot service
    cp -f $HESTIA/install/deb/dovecot/dovecot.conf /etc/dovecot/dovecot.conf
    systemctl restart dovecot
    sleep 0.5
fi

# Update Roundcube webmail configuration
if [ -f /etc/apache2/conf.d/webmail.conf ]; then
    echo "(*) Updating Roundcube global subdomain configuration for apache2..."
    cp -f $HESTIA/install/deb/roundcube/apache.conf /etc/apache2/conf.d/roundcube.conf.conf
fi
if [ -f /etc/nginx/conf.d/webmail.inc ]; then
    echo "(*) Updating Roundcube global subdomain configuration for nginx..."
    rm -f /etc/nginx/conf.d/webmail.inc
    cp -f $HESTIA/install/deb/nginx/webmail.conf /etc/nginx/conf.d/webmail.conf
fi

# Add IMAP system variable to configuration if dovecot is installed
if [ -z "$IMAP_SYSTEM" ]; then 
    if [ -f /usr/bin/dovecot ]; then
        echo "(*) Adding missing IMAP_SYSTEM variable to hestia.conf..."
        echo "IMAP_SYSTEM = 'dovecot'" >> $HESTIA/conf/hestia.conf
    fi
fi

# Remove Webalizer and replace it with awstats as default
echo "(*) Setting awstats as default web statistics backend..."
apt purge webalizer -y > /dev/null 2>&1
sed -i "s/STATS_SYSTEM='webalizer,awstats'/STATS_SYSTEM='awstats'/g" $HESTIA/conf/hestia.conf

# Run sftp jail once
$HESTIA/bin/v-add-sys-sftp-jail

# Rebuild user
for user in `ls /usr/local/hestia/data/users/`; do
    echo "(*) Rebuilding domains and account for user: $user..."
    v-rebuild-web-domains $user
    sleep 0.5
    v-rebuild-dns-domains $user
    sleep 0.5
    v-rebuild-mail-domains $user
    sleep 0.5
done
