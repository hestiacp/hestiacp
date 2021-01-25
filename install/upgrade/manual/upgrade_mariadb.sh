#!/bin/bash

# This script validates and upgrades the MariaDB version to 10.5

# Set MariaDB Target Version
mariadb_v='10.5'

# Load OS informations
source /etc/os-release

# Detect installed mariadb version
IFS=' ' read -r -a mysql_v <<< $(mysqld -V)
mysql_v=$(echo "${mysql_v[2]}" | cut -c1-4)

if [ "$mysql_v" = "$mariadb_v" ]; then
    echo "Version is already up to date, cancelling."
    exit 0
fi

# Detect operating system and load codename
if [ "$ID" = "ubuntu" ]; then
    codename="$(lsb_release -s -c)"
elif [ "$ID" = "debian" ]; then
    codename="$(cat /etc/os-release |grep VERSION= |cut -f 2 -d \(|cut -f 1 -d \))"
else
    echo "Can't detect the os version, cancelling."
    exit 1
fi

# Installing MariaDB repo
echo "Add new MariaDB repository..."
apt="/etc/apt/sources.list.d/"
if [ "$id" = "ubuntu" ]; then
    echo "deb [arch=amd64] https://mirror.mva-n.net/mariadb/repo/$mariadb_v/$ID $codename main" > $apt/mariadb.list
    APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0xF1656F24C74CD1D8 > /dev/null 2>&1
else
    echo "deb [arch=amd64] https://mirror.mva-n.net/mariadb/repo/$mariadb_v/$ID $codename main" > $apt/mariadb.list
    if [ "$id" = "jessie" ]; then
        APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key adv --recv-keys --keyserver keyserver.ubuntu.com CBCB082A1BB943DB > /dev/null 2>&1
    else
        APT_KEY_DONT_WARN_ON_DANGEROUS_USAGE=1 apt-key adv --recv-keys --keyserver keyserver.ubuntu.com F1656F24C74CD1D8 > /dev/null 2>&1
    fi
fi

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