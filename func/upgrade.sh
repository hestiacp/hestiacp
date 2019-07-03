#!/bin/bash

# Hestia Control Panel - Upgrade Control Script

#####################################################################
#######                Functions & Initialization             #######
#####################################################################

function upgrade_welcome_message() {
    echo
    echo '     _   _           _   _        ____ ____        '
    echo '    | | | | ___  ___| |_(_) __ _ / ___|  _ \       '
    echo '    | |_| |/ _ \/ __| __| |/ _` | |   | |_) |      '
    echo '    |  _  |  __/\__ \ |_| | (_| | |___|  __/       '
    echo '    |_| |_|\___||___/\__|_|\__,_|\____|_|          '
    echo ""
    echo "       Hestia Control Panel Upgrade Script"
    echo "                 Version: $new_version             "
    echo "==================================================="
    echo ""
    echo "Please note that some configuration and template"
    echo "files may be replaced during the upgrade process."
    echo ""
    echo "Backups of these files will be available under:"
    echo "$HESTIA_BACKUP/"
    echo ""
    echo "This process may take a few minutes, please wait..."
    echo ""
}

function upgrade_complete_message() {
    # Add notification to panel
    $HESTIA/bin/v-add-user-notification admin 'Upgrade complete' 'Your server has been updated to Hestia Control Panel version '$new_version'.<br>Please report any bugs on GitHub at<br><a href="https://github.com/hestiacp/hestiacp/Issues" target="_new">https://github.com/hestiacp/hestiacp/Issues</a><br><br>Have a great day!'

    # Echo message to console output
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
}

function upgrade_init_backup() {
    # Ensure that backup directories are created
    mkdir -p $HESTIA_BACKUP/conf/
    mkdir -p $HESTIA_BACKUP/packages/
    mkdir -p $HESTIA_BACKUP/templates/
}

function upgrade_start_routine() {

    #####################################################################
    #######         Start upgrade for pre-release builds          #######
    #####################################################################

    if [ $VERSION = "0.9.8-25" ] || [ $VERSION = "0.9.8-26" ] || [ $VERSION = "0.9.8-27" ] || [ $VERSION = "0.9.8-28" ] || [ $VERSION = "0.9.8-29" ] || [ $VERSION = "0.10.0" ] || [ $VERSION = "1.00.0-190618" ] || [ $VERSION = "1.00.0-190621" ]; then
        source $HESTIA_INSTALL_DIR/upgrade/versions/previous/0.9.8-29.sh
        source $HESTIA_INSTALL_DIR/upgrade/versions/previous/1.00.0-190618.sh
        source $HESTIA_INSTALL_DIR/upgrade/versions/previous/1.0.1.sh
        VERSION="1.0.1"
    fi

    #####################################################################
    #######             Start standard upgrade process            #######
    #####################################################################

    # Upgrade to Version 1.0.2
    if [ $VERSION = "1.0.1" ]; then
        source $HESTIA_INSTALL_DIR/upgrade/versions/previous/1.0.2.sh
        VERSION="1.0.2"
    fi

    # Upgrade to Version 1.0.3
    if [ $VERSION = "1.0.2" ]; then
        source $HESTIA_INSTALL_DIR/upgrade/versions/latest.sh
        VERSION="$new_version"
    fi

    # Ensure that latest upgrade commands are processed if version is the same
    if [ $VERSION = "$new_version" ]; then
        echo "(!) The latest version of Hestia Control Panel ($new_version) is already installed."
        echo "    Verifying configuration..."
        echo ""
        source $HESTIA_INSTALL_DIR/upgrade/versions/latest.sh
        VERSION="$new_version"
    fi

    #####################################################################
    #######                 End upgrade process                   #######
    #####################################################################
}

function upgrade_phpmyadmin() {
    # Define version check function
    function version_ge(){ test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1" -o ! -z "$1" -a "$1" = "$2"; }

    pma_release_file=$(ls /usr/share/phpmyadmin/RELEASE-DATE-* 2>/dev/null |tail -n 1)
    if version_ge "${pma_release_file##*-}" "$pma_v"; then
        echo "(*) phpMyAdmin $pma_v or newer is already installed: ${pma_release_file##*-}, skipping update..."
    else
        # Display upgrade information
        echo "(*) Upgrade phpMyAdmin to v$pma_v..."
        [ -d /usr/share/phpmyadmin ] || mkdir -p /usr/share/phpmyadmin

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

        # Clean up source files
        rm -fr phpMyAdmin-$pma_v-all-languages
        rm -f phpMyAdmin-$pma_v-all-languages.tar.gz
    fi
}

function upgrade_set_version() {
    # Set new version number in hestia.conf
    sed -i "/VERSION/d" $HESTIA/conf/hestia.conf
    echo "VERSION='$new_version'" >> $HESTIA/conf/hestia.conf
}

function upgrade_rebuild_users() {
    for user in `ls /usr/local/hestia/data/users/`; do
        echo "(*) Rebuilding domains and account for user: $user..."
        if [ ! -z $WEB_SYSTEM ]; then
            $BIN/v-rebuild-web-domains $user >/dev/null 2>&1
        fi
        if [ ! -z $DNS_SYSTEM ]; then
            $BIN/v-rebuild-dns-domains $user >/dev/null 2>&1
        fi
        if [ ! -z $MAIL_SYSTEM ]; then 
            $BIN/v-rebuild-mail-domains $user >/dev/null 2>&1
        fi
    done
}

function upgrade_restart_services() {
    echo "(*) Restarting services..."
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
    for v in `ls /etc/php/`; do
        if [ -e /etc/php/$v/fpm ]; then
            $BIN/v-restart-service php$v-fpm $restart
        fi
    done
    if [ ! -z $FTP_SYSTEM ]; then
        $BIN/v-restart-ftp $restart
    fi

    # Restart SSH daemon and Hestia Control Panel service
    $BIN/v-restart-service ssh $restart
    $BIN/v-restart-service hestia $restart
}