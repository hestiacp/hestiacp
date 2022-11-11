#!/bin/bash

# This script validates and upgrades the MariaDB version to 10.5

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Set MariaDB Target Version
mariadb_v='10.6'

# Load OS informations
source /etc/os-release

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

# Detect installed mariadb version
IFS=' ' read -r -a mysql_v <<< $(mysqld -V)
mysql_v=$(echo "${mysql_v[2]}" | cut -c1-4)

if [ "$mysql_v" = "$mariadb_v" ]; then
    echo "Version is already up to date, cancelling."
    exit 0
fi

#Get OS details
os=$(grep "^ID=" /etc/os-release | cut -f 2 -d '=')
codename="$(lsb_release -s -c)"
release="$(lsb_release -s -r)"
RHOST='apt.hestiacp.com'

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

# Installing MariaDB repo
apt="/etc/apt/sources.list.d/"
echo "[ * ] MariaDB"
   echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/mariadb-keyring.gpg] https://dlm.mariadb.com/repo/mariadb-server/$mariadb_v/repo/$VERSION $codename main" > $apt/mariadb.list
    curl -s https://mariadb.org/mariadb_release_signing_key.asc | gpg --dearmor | tee /usr/share/keyrings/mariadb-keyring.gpg >/dev/null 2>&1

# Update repository
echo "Update apt repository..."
apt update -qq  > /dev/null 2>&1

# Stop and uninstall mysql server
echo "Stop and remove old MariaDB server..."
systemctl stop mysql > /dev/null 2>&1
apt remove -qq mariadb-server -y  > /dev/null 2>&1

# Install new version and run upgrader
echo "Installing new MariaDB Server, start and run upgrade..."
apt install -qq mariadb-server -y
systemctl start mysql > /dev/null 2>&1
mysql_upgrade
