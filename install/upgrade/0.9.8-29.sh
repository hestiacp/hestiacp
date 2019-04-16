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
mkdir -p $HESTIA_BACKUP/templates/
mkdir -p $HESTIA_BACKUP/packages/

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
echo "This process may take a few minutes, please wait..."
echo ""
echo ""

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

# Install and configure z-push
if [ ! -z "$MAIL_SYSTEM" ]; then
    echo "(*) Installing Z-Push..."
    # Remove previous Z-Push configuration
    rm -rf /etc/z-push/*
    
    # Disable apt package lock to install z-push
    mv /var/lib/dpkg/lock-frontend /var/lib/dpkg/lock-frontend.bak
    mv /var/lib/dpkg/lock /var/lib/dpkg/lock.bak
    mv /var/cache/apt/archives/lock /var/cache/apt/archives/lock.bak
    mv /var/lib/dpkg/updates/ /var/lib/dpkg/updates.bak/
    mkdir -p /var/lib/dpkg/updates/

    apt="/etc/apt/sources.list.d"
    if [ "$os" = 'ubuntu' ]; then
        echo "deb http://repo.z-hub.io/z-push:/final/Ubuntu_$release/ /" > $apt/z-push.list
        wget --quiet http://repo.z-hub.io/z-push:/final/Ubuntu_$release/Release.key -O /tmp/z-push_signing.key
        APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key add /tmp/z-push_signing.key > /dev/null 2>&1
    else
        if [ "$release" -eq 8 ]; then
            zpush_os='Debian_8.0'
        else
            zpush_os='Debian_9.0'
        fi

        echo "deb http://repo.z-hub.io/z-push:/final/$zpush_os/ /" > $apt/z-push.list
        wget --quiet http://repo.z-hub.io/z-push:/final/$zpush_os/Release.key -O /tmp/z-push_signing.key
        APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key add /tmp/z-push_signing.key > /dev/null 2>&1
    fi

    apt-get -qq update > /dev/null 2>&1
    apt-get -qq -y install php-imap php-apcu php-ldap z-push-common z-push-backend-imap z-push-backend-combined z-push-autodiscover > /dev/null 2>&1

    echo "(I) Adding Z-Push configuration directory"
    mkdir -p /etc/z-push/
    cp -f $hestiacp/zpush/z-push.conf.php /etc/z-push/
    cp -f $hestiacp/zpush/imap.conf.php /etc/z-push/

    # Set permissions - chmod 777 needs further testing!
    echo "(I) Adding Z-Push logs directory"
    mkdir -p /var/log/z-push

    chmod 777 /var/lib/z-push
    chown -R www-data:www-data /var/lib/z-push
    chmod 777 /var/log/z-push
    chown -R www-data:www-data /var/log/z-push

    # Enable apt package lock
    mv /var/lib/dpkg/lock-frontend.bak /var/lib/dpkg/lock-frontend
    mv /var/lib/dpkg/lock.bak /var/lib/dpkg/lock
    mv /var/cache/apt/archives/lock.bak /var/cache/apt/archives/lock
    rm -rf /var/lib/dpkg/updates/
    mv /var/lib/dpkg/updates.bak/ /var/lib/dpkg/updates/
fi

# Update default page templates
echo "(*) Replacing default templates and packages..."
echo "    Existing files have been backed up to the following location:"
echo "    $HESTIA_BACKUP/"

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

# Fix empty pool error message for multiphp
php_versions=$( ls -l /etc/php/ | grep ^d | wc -l )
if [ "$php_versions" -gt 1 ]; then
    for v in $(ls /etc/php/); do
        cp -f $hestiacp/php-fpm/dummy.conf /etc/php/$d/fpm/pool.d/
        v1=$(echo "$v" | sed -e 's/[.]//')
        sed -i "s/9999/9999$v1/g" /etc/php/$v/fpm/pool.d/dummy.conf
    done
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

# Fix exim configuration
if [ -f /etc/exim4/exim4.conf.template ]; then
    echo "(*) Updating exim SMTP server configuration..."
    cp -f $HESTIA/install/deb/exim/exim4.conf.template /etc/exim4/exim4.conf.template
    # Reconfigure spam filter and virus scanning
    if [ ! -z "$ANTISPAM_SYSTEM" ]; then
        sed -i "s/#SPAM/SPAM/g" /etc/exim4/exim4.conf.template
    fi
    if [ ! -z "$ANTIVIRUS_SYSTEM" ]; then
        sed -i "s/#CLAMD/CLAMD/g" /etc/exim4/exim4.conf.template
    fi
fi

# Add IMAP system variable to configuration if dovecot is installed
if [ -z "$IMAP_SYSTEM" ]; then
    if [ -f /usr/bin/dovecot ]; then
        echo "(*) Adding missing IMAP_SYSTEM variable to hestia.conf..."
        echo "IMAP_SYSTEM = 'dovecot'" >> $HESTIA/conf/hestia.conf
    fi
fi

# Remove global webmail configuration files in favor of per-domain vhosts
if [ -f /etc/apache2/conf.d/roundcube.conf ]; then
    echo "(*) Removing global webmail configuration files for Apache2..."
    rm -f /etc/apache2/conf.d/roundcube.conf
fi
if [ -f /etc/nginx/conf.d/webmail.inc ]; then
    echo "(*) Removing global webmail configuration files for nginx..."
    rm -f /etc/nginx/conf.d/webmail.inc
fi
if [ -f /etc/nginx/conf.d/webmail.conf ]; then
    echo "(*) Removing global webmail configuration files for nginx..."
    rm -f /etc/nginx/conf.d/webmail.conf
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
    v-rebuild-web-domains $user >/dev/null 2>&1
    sleep 1
    v-rebuild-dns-domains $user >/dev/null 2>&1
    sleep 1
    v-rebuild-mail-domains $user >/dev/null 2>&1
    sleep 1
done
echo ""
echo "    Upgrade complete! Please report any bugs or issues to"
echo "    https://github.com/hestiacp/hestiacp/issues."
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
