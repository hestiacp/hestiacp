#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.1.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ -e "/etc/apache2/mods-enabled/status.conf" ]; then
    echo "(*) Hardening Apache2 Server Status Module..."
    sed -i '/Allow from all/d' /etc/apache2/mods-enabled/status.conf
fi

# Add sury apache2 repository
if [ "$WEB_SYSTEM" = "apache2" ] && [ ! -e "/etc/apt/sources.list.d/apache2.list" ]; then
    echo "(*) Install sury.org Apache2 repository..."

    # Check OS and install related repository
    if [ -e "/etc/os-release" ]; then
        type=$(grep "^ID=" /etc/os-release | cut -f 2 -d '=')
        if [ "$type" = "ubuntu" ]; then
            codename="$(lsb_release -s -c)"
            echo "deb http://ppa.launchpad.net/ondrej/apache2/ubuntu $codename main" > /etc/apt/sources.list.d/apache2.list
        elif [ "$type" = "debian" ]; then
            echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/apache2.list
            wget --quiet https://packages.sury.org/apache2/apt.gpg -O /tmp/apache2_signing.key
            APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key add /tmp/apache2_signing.key > /dev/null 2>&1
        fi
    fi
fi

# Roundcube fixes for PHP 7.4 compatibility
if [ -d /usr/share/roundcube ]; then
    echo "(*) Updating Roundcube configuration..."
    sed -i 's/\"\\n\", $identities/$identities, \"\\n\"/g' /usr/share/roundcube/plugins/enigma/lib/enigma_ui.php
    sed -i 's/(array_keys($post_search), \x27|\x27)/(\x27|\x27, array_keys($post_search))/g' /usr/share/roundcube/program/lib/Roundcube/rcube_contacts.php
    sed -i 's/implode($name, \x27.\x27)/implode(\x27.\x27, $name)/g' /usr/share/roundcube/program/lib/Roundcube/rcube_db.php
    sed -i 's/$fields, \x27,\x27/',', $fields/g' /usr/share/roundcube/program/steps/addressbook/search.inc
    sed -i 's/implode($fields, \x27,\x27)/implode(\x27,\x27, $fields)/g' /usr/share/roundcube/program/steps/addressbook/search.inc
    sed -i 's/implode($bstyle, \x27; \x27)/implode(\x27; \x27, $bstyle)/g' /usr/share/roundcube/program/steps/mail/sendmail.inc
fi

# Enable Roundcube plugins
if [ -d /usr/share/roundcube ]; then
    cp -f $HESTIA_INSTALL_DIR/roundcube/plugins/config_newmail_notifier.inc.php /etc/roundcube/plugins/newmail_notifier/config.inc.php
    cp -f $HESTIA_INSTALL_DIR/roundcube/plugins/config_zipdownload.inc.php /etc/roundcube/plugins/zipdownload/config.inc.php
    sed -i "s/array('password')/array('password','newmail_notifier','zipdownload')/g" /etc/roundcube/config.inc.php
fi

# HELO support for multiple domains and IPs
if [ -e "/etc/exim4/exim4.conf.template" ]; then
    echo "(*) Updating exim4 configuration..."
    sed -i 's|helo_data = ${primary_hostname}|helo_data = ${if exists {\/etc\/exim4\/mailhelo.conf}{${lookup{$sender_address_domain}lsearch*{\/etc\/exim4\/mailhelo.conf}{$value}{$primary_hostname}}}{$primary_hostname}}|g' /etc/exim4/exim4.conf.template
fi

# Add daily midnight cron
if [ -z "$($BIN/v-list-cron-jobs admin | grep 'v-update-sys-queue daily')" ]; then
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
