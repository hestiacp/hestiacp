#!/bin/bash

# Define version check function
function version_ge(){ test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1" -o ! -z "$1" -a "$1" = "$2"; }

# Set phpMyAdmin version for upgrade
pma_v='4.9.0.1'

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