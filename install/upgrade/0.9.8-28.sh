#!/bin/bash
HESTIA="/usr/local/hestia"
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"
spinner="/-\|"

function version_ge(){ test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1" -o ! -z "$1" -a "$1" = "$2"; }

# Set version(s)
pma_v='4.8.5'

# Upgrade phpMyAdmin
if [ "$DB_SYSTEM" = 'mysql' ]; then
    # Display upgrade information
    echo "(*) Upgrading phpMyAdmin to v$pma_v..."

    pma_release_file=$(ls /usr/share/phpmyadmin/RELEASE-DATE-* 2>/dev/null |tail -n 1)
    if version_ge "${pma_release_file##*-}" "$pma_v"; then
        echo "(*) phpMyAdmin $pma_v or newer is already installed: ${pma_release_file##*-}, skipping update..."
    else
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

        # Clean up
        rm -fr phpMyAdmin-$pma_v-all-languages
        rm -f phpMyAdmin-$pma_v-all-languages.tar.gz
    fi
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

# Move clamav to proper location - https://goo.gl/zNuM11
if [ ! -d /usr/local/hestia/web/edit/server/clamav-daemon ]; then
    mv /usr/local/hestia/web/edit/server/clamd /usr/local/web/edit/server/clamav-daemon
fi