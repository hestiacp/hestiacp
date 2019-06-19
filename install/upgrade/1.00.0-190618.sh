#!/bin/bash

# Define vars
if [ -z "$HESTIA" ] || [ ! -f "${HESTIA}/conf/hestia.conf"]; then
    export HESTIA="/usr/local/hestia"
fi
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"
hestiacp="$HESTIA/install/deb"
pma_v='4.9.0.1'

# Add webmail alias variable to system configuration if non-existent
WEBMAIL_ALIAS_CHECK=$(cat $HESTIA/conf/hestia.conf | grep WEBMAIL_ALIAS)
if [ -z "$WEBMAIL_ALIAS_CHECK" ]; then
    echo "(*) Adding global webmail alias to system configuration..."
    sed -i "/WEBMAIL_ALIAS/d" $HESTIA/conf/hestia.conf
    echo "WEBMAIL_ALIAS='webmail'" >> $HESTIA/conf/hestia.conf
fi

# Add release branch system configuration if non-existent
release_branch_check=$(cat $HESTIA/conf/hestia.conf | grep RELEASE_BRANCH)
if [ -z "$release_branch_check" ]; then
    echo "(*) Adding global release branch variable to system configuration..."
    sed -i "/RELEASE_BRANCH/d" $HESTIA/conf/hestia.conf
    echo "RELEASE_BRANCH='develop'" >> $HESTIA/conf/hestia.conf
fi

# Load hestia.conf
source $HESTIA/conf/hestia.conf

# Load hestia main functions
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

# Configure apt to retry downloading on error
if [ ! -f /etc/apt/apt.conf.d/80-retries ]; then
    echo "APT::Acquire::Retries \"3\";" > /etc/apt/apt.conf.d/80-retries
fi

# Clear the screen from apt output to prepare for upgrade installer experience
clear
echo
echo '     _   _           _   _        ____ ____  '
echo '    | | | | ___  ___| |_(_) __ _ / ___|  _ \ '
echo '    | |_| |/ _ \/ __| __| |/ _` | |   | |_) |'
echo '    |  _  |  __/\__ \ |_| | (_| | |___|  __/ '
echo '    |_| |_|\___||___/\__|_|\__,_|\____|_|    '
echo ""
echo "       Hestia Control Panel Upgrade Script"
echo "==================================================="
echo ""
echo "Existing files will be backed up to the following location:"
echo "$HESTIA_BACKUP/"
echo ""
echo "This process may take a few moments, please wait..."
echo ""

# Set new version
sed -i "/VERSION/d" $HESTIA/conf/hestia.conf
# Set new branch variable
echo "VERSION='0.10.0'" >> $HESTIA/conf/hestia.conf

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

# Generate dhparam
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
    systemctl restart nginx >/dev/null 2>&1
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
chmod 644 /var/www/document_errors/*

for user in `ls /usr/local/hestia/data/users/`; do
    USER_DATA=$HESTIA/data/users/$user
    for domain in $($BIN/v-list-web-domains $user plain |cut -f 1); do
        WEBFOLDER="/home/$user/web/$domain/public_html"
        folderchecksum=$(find "$WEBFOLDER/css" "$WEBFOLDER/js" "$WEBFOLDER/webfonts" -type f -print0 2>/dev/null |sort -z |xargs -r0 cat |md5sum |cut -d" " -f1)
        if [ "$folderchecksum" = "926feacc51384fe13598631f9d1360c3" ]; then
            rm -rf "$WEBFOLDER/css" "$WEBFOLDER/js" "$WEBFOLDER/webfonts"
        fi
        unset folderchecksum
        unset WEBFOLDER
    done
done
folderchecksum=$(find /var/www/html/css /var/www/html/js /var/www/html/webfonts -type f -print0 2>/dev/null |sort -z |xargs -r0 cat |md5sum |cut -d" " -f1)
if [ "$folderchecksum" = "d148d5173e5e4162d7af0a60585392cb" ]; then
    rm -rf /var/www/html/css /var/www/html/js /var/www/html/webfonts
fi
unset folderchecksum

# Correct other permissions
if [ -d "/var/cache/bind" ]; then
    chown bind:bind /var/cache/bind
fi
if [ -d "/etc/roundcube" ]; then
    chmod 640 /etc/roundcube/debian-db*
    chown root:www-data /etc/roundcube/debian-db*
fi

# Add a general group for normal users created by Hestia
echo "(*) Verifying ACLs and hardening user permissions..."
if [ -z "$(grep ^hestia-users: /etc/group)" ]; then
    groupadd --system "hestia-users"
fi

# Make sure non-admin users belong to correct Hestia group
for user in `ls /usr/local/hestia/data/users/`; do
    if [ "$user" != "admin" ]; then
        usermod -a -G "hestia-users" "$user"
        setfacl -m "u:$user:r-x" "$HOMEDIR/$user"

        # Update FTP users groups membership
        uid=$(id -u $user)
        for ftp_user in $(cat /etc/passwd | grep -v "^$user:" | grep "^$user.*:$uid:$uid:" | cut -d ":" -f1); do
            usermod -a -G "hestia-users" "$ftp_user"
        done
    fi
    setfacl -m "g:hestia-users:---" "$HOMEDIR/$user"
done

# Add unassigned hosts configuration to Nginx and Apache
for ipaddr in $(ls /usr/local/hestia/data/ips/ 2>/dev/null); do

    web_conf="/etc/$WEB_SYSTEM/conf.d/$ipaddr.conf"
    rm -f $web_conf

    if [ "$WEB_SYSTEM" = "apache2" ]; then
        echo "(*) Adding unassigned hosts configuration to Apache..."
        if [ -z "$(/usr/sbin/apache2 -v | grep Apache/2.4)" ]; then
            echo "NameVirtualHost $ipaddr:$WEB_PORT" >  $web_conf
        fi
        echo "Listen $ipaddr:$WEB_PORT" >> $web_conf
        cat $HESTIA/install/deb/apache2/unassigned.conf >> $web_conf
        sed -i 's/directIP/'$ipaddr'/g' $web_conf
        sed -i 's/directPORT/'$WEB_PORT'/g' $web_conf

        if [ "$WEB_SSL" = 'mod_ssl' ]; then
            if [ -z "$(/usr/sbin/apache2 -v | grep Apache/2.4)" ]; then
                sed -i "1s/^/NameVirtualHost $ipaddr:$WEB_SSL_PORT\n/" $web_conf
            fi
            sed -i "1s/^/Listen $ipaddr:$WEB_SSL_PORT\n/" $web_conf
            sed -i 's/directSSLPORT/'$WEB_SSL_PORT'/g' $web_conf
        fi
    
    elif [ "$WEB_SYSTEM" = "nginx" ]; then
        cp -f $HESTIA/install/deb/nginx/unassigned.inc $web_conf
        sed -i 's/directIP/'$ipaddr'/g' $web_conf
    fi

    if [ "$PROXY_SYSTEM" = "nginx" ]; then
        echo "(*) Adding unassigned hosts configuration to Nginx..."
        cat $WEBTPL/$PROXY_SYSTEM/proxy_ip.tpl |\
        sed -e "s/%ip%/$ipaddr/g" \
            -e "s/%web_port%/$WEB_PORT/g" \
            -e "s/%proxy_port%/$PROXY_PORT/g" \
        > /etc/$PROXY_SYSTEM/conf.d/$ipaddr.conf
    fi
done

# Cleanup php session files not changed in the last 7 days (60*24*7 minutes)
if [ ! -f /etc/cron.daily/php-session-cleanup ]; then
    echo '#!/bin/sh' > /etc/cron.daily/php-session-cleanup
    echo "find -O3 /home/*/tmp/ -ignore_readdir_race -depth -mindepth 1 -name 'sess_*' -type f -cmin '+10080' -delete > /dev/null 2>&1" >> /etc/cron.daily/php-session-cleanup
    echo "find -O3 $HESTIA/data/sessions/ -ignore_readdir_race -depth -mindepth 1 -name 'sess_*' -type f -cmin '+10080' -delete > /dev/null 2>&1" >> /etc/cron.daily/php-session-cleanup
fi
    chmod 755 /etc/cron.daily/php-session-cleanup

# Fix empty pool error message for MultiPHP
php_versions=$(ls /etc/php/*/fpm -d 2>/dev/null |wc -l)
if [ "$php_versions" -gt 1 ]; then
    for v in $(ls /etc/php/); do
        if [ ! -d "/etc/php/$v/fpm/pool.d/" ]; then
            continue
        fi
        echo "(*) Updating Multi-PHP configuration..."
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
WEBALIAZER_CHECK=$(cat $HESTIA/conf/hestia.conf | grep webalizer)
if [ ! -z "$WEBALIZER_CHECK" ]; then
    echo "(*) Removing Webalizer and setting AWStats as default web statistics backend..."
    apt purge webalizer -y > /dev/null 2>&1
    sed -i "s/STATS_SYSTEM='webalizer,awstats'/STATS_SYSTEM='awstats'/g" $HESTIA/conf/hestia.conf
fi

# Run sftp jail once
$HESTIA/bin/v-add-sys-sftp-jail

# Enable SFTP subsystem for SSH
sftp_subsys_enabled=$(grep -iE "^#?.*subsystem.+(sftp )?sftp-server" /etc/ssh/sshd_config)
if [ ! -z "$sftp_subsys_enabled" ]; then
    echo "(*) Updating SFTP subsystem configuration..."
    sed -i -E "s/^#?.*Subsystem.+(sftp )?sftp-server/Subsystem sftp internal-sftp/g" /etc/ssh/sshd_config
    systemctl restart ssh
fi

# Remove and migrate obsolete object keys
for user in `ls /usr/local/hestia/data/users/`; do
    USER_DATA=$HESTIA/data/users/$user

    # Web keys
    for domain in $($BIN/v-list-web-domains $user plain |cut -f 1); do
        obskey=$(get_object_value 'web' 'DOMAIN' "$domain" '$FORCESSL')
        if [ ! -z "$obskey" ]; then
            echo "(*) Fixing HTTP-to-HTTPS redirection for $domain"
            update_object_value 'web' 'DOMAIN' "$domain" '$FORCESSL' ''

            # copy value under new key name
            add_object_key "web" 'DOMAIN' "$domain" 'SSL_FORCE' 'SSL_HOME'
            update_object_value 'web' 'DOMAIN' "$domain" '$SSL_FORCE' "$obskey"
        fi
        unset FORCESSL
    done
    sed -i "s/\sFORCESSL=''//g" $USER_DATA/web.conf
done

# Rebuild user
for user in `ls /usr/local/hestia/data/users/`; do
    echo "(*) Rebuilding domains and account for user: $user..."
    if [ ! -z $WEB_SYSTEM ]; then
		v-rebuild-web-domains $user >/dev/null 2>&1
	fi
    if [ ! -z $DNS_SYSTEM ]; then
		v-rebuild-dns-domains $user >/dev/null 2>&1
    fi
	if [ ! -z $MAIL_SYSTEM ]; then 
		v-rebuild-mail-domains $user >/dev/null 2>&1
	fi
done

# Upgrade phpMyAdmin if applicable
if [ "$DB_SYSTEM" = 'mysql' ]; then
    if [ -e "/usr/share/phpmyadmin/RELEASE-DATE-$pma_v" ]; then
        echo "(*) phpMyAdmin $pma_v is already installed, skipping update..."
    else
        # Display upgrade information
        echo "(*) Upgrade phpMyAdmin to v$pma_v..."

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

        # Clear up
        rm -fr phpMyAdmin-$pma_v-all-languages
        rm -f phpMyAdmin-$pma_v-all-languages.tar.gz
    fi
fi

# Add upgrade notification to admin user's panel
$BIN/v-add-user-notification admin 'Upgrade complete' 'Your server has been updated to v0.10.0.<br>Please report any bugs on GitHub at<br>https://github.com/hestiacp/hestiacp/Issues<br><br>Have a great day!'

# Restart services for changes to take full effect
echo "(*) Restarting services..."
sleep 3
if [ ! -z $MAIL_SYSTEM ]; then
	$BIN/v-restart-mail $restart
fi
if [ ! -z $IMAP_SYSTEM ]; then
	$BIN/v-restart-service $IMAP_SYSTEM $restart
fi
if [ ! -z $WEB_SYSTEM ]; then
	$BIN/v-restart-web $restart
	$BIN/v-restart-proxy $restart
fi
if [ ! -z $DNS_SYSTEM ]; then
	$BIN/v-restart-dns $restart
fi

# restart Hestia services (nginx,php-fpm)
systemctl restart hestia

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
echo "    Made with love & pride by the open-source community around the world."
echo ""
echo ""
