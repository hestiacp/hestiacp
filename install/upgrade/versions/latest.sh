#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.1.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ -e "/etc/apache2/mods-enabled/status.conf" ]; then
    echo "(*) Disable Apache2 Server Status Module..."
    a2dismod status > /dev/null 2>&1
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
        elseif [ "$type" = "debian" ]; then
            echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/apache2.list
            wget --quiet https://packages.sury.org/apache2/apt.gpg -O /tmp/apache2_signing.key
            APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key add /tmp/apache2_signing.key > /dev/null 2>&1
        fi
    fi
fi