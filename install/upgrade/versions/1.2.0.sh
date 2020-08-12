#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.2.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Check iptables paths and add symlinks when necessary

if [ ! -e "/sbin/iptables" ]; then
    if which iptables; then
        ln -s "$(which iptables)" /sbin/iptables
    elif [ -e "/usr/sbin/iptables" ]; then
        ln -s /usr/sbin/iptables /sbin/iptables
    elif whereis -B /bin /sbin /usr/bin /usr/sbin -f -b iptables; then
        autoiptables=$(whereis -B /bin /sbin /usr/bin /usr/sbin -f -b iptables | cut -d '' -f 2)
        if [ -x "$autoiptables" ]; then
            ln -s "$autoiptables" /sbin/iptables
        fi
    fi
fi

if [ ! -e "/sbin/iptables-save" ]; then
    if which iptables-save; then
        ln -s "$(which iptables-save)" /sbin/iptables-save
    elif [ -e "/usr/sbin/iptables-save" ]; then
        ln -s /usr/sbin/iptables-save /sbin/iptables-save
    elif whereis -B /bin /sbin /usr/bin /usr/sbin -f -b iptables-save; then
        autoiptables_save=$(whereis -B /bin /sbin /usr/bin /usr/sbin -f -b iptables-save | cut -d '' -f 2)
        if [ -x "$autoiptables_save" ]; then
            ln -s "$autoiptables_save" /sbin/iptables-save
        fi
    fi
fi

if [ ! -e "/sbin/iptables-restore" ]; then
    if which iptables-restore; then
        ln -s "$(which iptables-restore)" /sbin/iptables-restore
    elif [ -e "/usr/sbin/iptables-restore" ]; then
        ln -s /usr/sbin/iptables-restore /sbin/iptables-restore
    elif whereis -B /bin /sbin /usr/bin /usr/sbin -f -b iptables-restore; then
        autoiptables_restore=$(whereis -B /bin /sbin /usr/bin /usr/sbin -f -b iptables-restore | cut -d '' -f 2)
        if [ -x "$autoiptables_restore" ]; then
            ln -s "$autoiptables_restore" /sbin/iptables-restore
        fi
    fi
fi

if [ -e "/etc/apache2/mods-enabled/status.conf" ]; then
    sed -i '/Allow from all/d' /etc/apache2/mods-enabled/status.conf
fi

# Add sury apache2 repository
if [ "$WEB_SYSTEM" = "apache2" ] && [ ! -e "/etc/apt/sources.list.d/apache2.list" ]; then
    echo "[ * ] Configuring sury.org Apache2 repository..."

    # Check OS and install related repository
    if [ -e "/etc/os-release" ]; then
        type=$(grep "^ID=" /etc/os-release | cut -f 2 -d '=')
        if [ "$type" = "ubuntu" ]; then
            codename="$(lsb_release -s -c)"
            echo "deb http://ppa.launchpad.net/ondrej/apache2/ubuntu $codename main" > /etc/apt/sources.list.d/apache2.list
        elif [ "$type" = "debian" ]; then
            codename="$(cat /etc/os-release |grep VERSION= |cut -f 2 -d \(|cut -f 1 -d \))"
            echo "deb https://packages.sury.org/apache2/ $codename main" > /etc/apt/sources.list.d/apache2.list
            wget --quiet https://packages.sury.org/apache2/apt.gpg -O /tmp/apache2_signing.key
            APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key add /tmp/apache2_signing.key > /dev/null 2>&1
        fi
    fi
fi

# Roundcube fixes for PHP 7.4 compatibility
if [ -d /usr/share/roundcube ]; then
    echo "[ * ] Updating Roundcube configuration..."
    [ -f "/usr/share/roundcube/plugins/enigma/lib/enigma_ui.php" ] && sed -i 's/$identities, "\\n"/"\\n", $identities/g' /usr/share/roundcube/plugins/enigma/lib/enigma_ui.php
    [ -f "/usr/share/roundcube/program/lib/Roundcube/rcube_contacts.php" ] && sed -i 's/(array_keys($post_search), \x27|\x27)/(\x27|\x27, array_keys($post_search))/g' /usr/share/roundcube/program/lib/Roundcube/rcube_contacts.php
    [ -f "/usr/share/roundcube/program/lib/Roundcube/rcube_db.php" ] && sed -i 's/implode($name, \x27.\x27)/implode(\x27.\x27, $name)/g' /usr/share/roundcube/program/lib/Roundcube/rcube_db.php
    [ -f "/usr/share/roundcube/program/steps/addressbook/search.inc" ] && sed -i 's/$fields, \x27,\x27/\x27,\x27, $fields/g' /usr/share/roundcube/program/steps/addressbook/search.inc
    [ -f "/usr/share/roundcube/program/steps/addressbook/search.inc" ] && sed -i 's/implode($fields, \x27,\x27)/implode(\x27,\x27, $fields)/g' /usr/share/roundcube/program/steps/addressbook/search.inc
    [ -f "/usr/share/roundcube/program/steps/mail/sendmail.inc" ] && sed -i 's/implode($bstyle, \x27; \x27)/implode(\x27; \x27, $bstyle)/g' /usr/share/roundcube/program/steps/mail/sendmail.inc
fi

# Enable Roundcube plugins
if [ -d /usr/share/roundcube ]; then
    cp -f $HESTIA_INSTALL_DIR/roundcube/plugins/config_newmail_notifier.inc.php /etc/roundcube/plugins/newmail_notifier/config.inc.php
    cp -f $HESTIA_INSTALL_DIR/roundcube/plugins/config_zipdownload.inc.php /etc/roundcube/plugins/zipdownload/config.inc.php
    [ -f "/etc/roundcube/config.inc.php" ] && sed -i "s/array('password')/array('password','newmail_notifier','zipdownload')/g" /etc/roundcube/config.inc.php
fi

# HELO support for multiple domains and IPs
if [ -e "/etc/exim4/exim4.conf.template" ]; then
    echo "[ * ] Updating exim4 configuration..."
    sed -i 's|helo_data = ${primary_hostname}|helo_data = ${if exists {\/etc\/exim4\/mailhelo.conf}{${lookup{$sender_address_domain}lsearch*{\/etc\/exim4\/mailhelo.conf}{$value}{$primary_hostname}}}{$primary_hostname}}|g' /etc/exim4/exim4.conf.template
fi

# Add daily midnight cron
if [ -z "$($BIN/v-list-cron-jobs admin | grep 'v-update-sys-queue daily')" ]; then
    echo "[ * ] Updating cron jobs..."
    command="sudo $BIN/v-update-sys-queue daily"
    $BIN/v-add-cron-job 'admin' '01' '00' '*' '*' '*' "$command"
fi
[ ! -f "touch $HESTIA/data/queue/daily.pipe" ] && touch $HESTIA/data/queue/daily.pipe

# Remove existing network-up hooks so they get regenerated when updating the firewall
# - network hook will also restore ipset config during start-up
if [ -f "/usr/lib/networkd-dispatcher/routable.d/50-ifup-hooks" ]; then
    rm "/usr/lib/networkd-dispatcher/routable.d/50-ifup-hooks"
    $BIN/v-update-firewall
fi
if [ -f "/etc/network/if-pre-up.d/iptables" ];then
    rm "/etc/network/if-pre-up.d/iptables"
    $BIN/v-update-firewall
fi

# Add hestia-event.conf, if the server is running apache2
if [ "$WEB_SYSTEM" = "apache2" ]; then
    echo "[ * ] Updating Apache2 configuration..."
    # Cleanup
    rm --force /etc/apache2/mods-available/hestia-event.conf
    rm --force /etc/apache2/mods-enabled/hestia-event.conf
    rm --force /etc/apache2/conf-available/hestia-event.conf
    rm --force /etc/apache2/conf-enabled/hestia-event.conf

    if [ $(a2query -M) = 'event' ] && [ ! -e "/etc/apache2/conf.d/hestia-event.conf" ]; then
        cp -f $HESTIA_INSTALL_DIR/apache2/hestia-event.conf /etc/apache2/conf.d/
    fi

    # Move apache mod_status config to /mods-available and rename it to prevent losing changes on upgrade
    cp -f $HESTIA_INSTALL_DIR/apache2/status.conf /etc/apache2/mods-available/hestia-status.conf
    cp -f /etc/apache2/mods-available/status.load /etc/apache2/mods-available/hestia-status.load
    a2dismod --quiet status > /dev/null 2>&1
    a2enmod --quiet hestia-status > /dev/null 2>&1
    rm --force /etc/apache2/mods-enabled/status.conf # a2dismod will not remove the file if it isn't a symlink
fi

# Install File Manager during upgrade if environment variable oesn't already exist and isn't set to false
# so that we don't override preference
FILE_MANAGER_CHECK=$(cat $HESTIA/conf/hestia.conf | grep "FILE_MANAGER='false'")
if [ -z "$FILE_MANAGER_CHECK" ]; then
    if [ ! -e "$HESTIA/web/fm/configuration.php" ]; then
        echo "[ ! ] Installing File Manager..."
        # Install the File Manager
        $HESTIA/bin/v-add-sys-filemanager quiet
    else 
        echo "[ * ] Updating File Manager configuration..."
        # Update configuration.php
        cp -f $HESTIA_INSTALL_DIR/filemanager/filegator/configuration.php $HESTIA/web/fm/configuration.php
        # Set environment variable for interface
        $HESTIA/bin/v-change-sys-config-value 'FILE_MANAGER' 'true'
    fi
fi

# Enable nginx module loading
if [ -f "/etc/nginx/nginx.conf" ]; then
    echo "[ * ] Updating NGINX configuration..."
    if [ ! -d "/etc/nginx/modules-enabled" ]; then
        mkdir -p "/etc/nginx/modules-enabled"
    fi

    if ! grep --silent "include /etc/nginx/modules-enabled" /etc/nginx/nginx.conf; then
        sed -i '/^pid/ a include /etc/nginx/modules-enabled/*.conf;' /etc/nginx/nginx.conf
    fi
fi

# Fix public_(s)html group ownership
echo "[ * ] Updating public_(s)html ownership..."
for user in $($HESTIA/bin/v-list-sys-users plain); do
    # skip users with missing home folder
    [[ -d /home/${user}/ ]] || continue

    # skip users without web domains
    ls /home/${user}/web/*/public_*html >/dev/null 2>&1 || continue

    chown --silent --no-dereference :www-data /home/$user/web/*/public_*html
done

# Fix phpMyAdmin blowfish_secret error message due to incorrect permissions
if [ -e /var/lib/phpmyadmin/blowfish_secret.inc.php ]; then
    echo "[ * ] Updating phpMyAdmin permissions..."
    chmod 0644 /var/lib/phpmyadmin/blowfish_secret.inc.php
fi

# Ensure that backup compression level is correctly set
GZIP_LVL_CHECK=$(cat $HESTIA/conf/hestia.conf | grep BACKUP_GZIP)
if [ -z "$GZIP_LVL_CHECK" ]; then
    echo "[ * ] Updating backup compression level variable..."
    $BIN/v-change-sys-config-value "BACKUP_GZIP" '9'
fi

# Update phpMyAdmin/phpPgAdmin templates and set missing alias variables if necessary
if [ -e "/var/lib/phpmyadmin" ]; then
    PMA_ALIAS_CHECK=$(cat $HESTIA/conf/hestia.conf | grep DB_PMA_ALIAS)
    if [ -z "$PMA_ALIAS_CHECK" ]; then
        echo "[ * ] Updating phpMyAdmin alias..."
        $HESTIA/bin/v-change-sys-db-alias "pma" "phpMyAdmin"
    else
        echo "[ * ] Updating phpMyAdmin configuration..."
        $HESTIA/bin/v-change-sys-db-alias "pma" "$DB_PMA_ALIAS"
    fi
fi

if [ -e "/var/lib/phppgadmin" ]; then
    PGA_ALIAS_CHECK=$(cat $HESTIA/conf/hestia.conf | grep DB_PGA_ALIAS)
    if [ -z "$PGA_ALIAS_CHECK" ]; then
        echo "[ * ] Updating phpPgAdmin alias..."
        $HESTIA/bin/v-change-sys-db-alias "pga" "phpPgAdmin"
    else
        echo "[ * ] Updating phpPgAdmin configuration..."
        $HESTIA/bin/v-change-sys-db-alias "pga" "$DB_PGA_ALIAS"
    fi
fi

# Ensure that backup compression level is correctly set
GZIP_LVL_CHECK=$(cat $HESTIA/conf/hestia.conf | grep BACKUP_GZIP)
if [ -z "$GZIP_LVL_CHECK" ]; then
    echo "[ * ] Updating backup compression level variable..."
    $BIN/v-change-sys-config-value "BACKUP_GZIP" '9'
fi
