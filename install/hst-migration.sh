#!/bin/bash
# Hestia installation wrapper
# https://www.hestiacp.com

#
# Currently Supported Operating Systems:
#
#   Debian 8, 9
#   Ubuntu 14.04, 16.04, 18.04
#

# Detect Codename
if [ "$type" = "debian" ]; then
    codename="$(cat /etc/os-release |grep VERSION= |cut -f 2 -d \(|cut -f 1 -d \))"
    release=$(cat /etc/debian_version|grep -o [0-9]|head -n1)
    VERSION='debian'
fi

if [ "$type" = "ubuntu" ]; then
    codename="$(lsb_release -s -c)"
    release="$(lsb_release -s -r)"
    VERSION='ubuntu'
fi

HESTIA="/usr/local/hestia"
RHOST='apt.hestiacp.com'
os=$(head -n1 /etc/issue | cut -f 1 -d ' ')
apt="/etc/apt/sources.list.d"
hestiacp="$HESTIA/install/$VERSION/$release"

# Am I root?
if [ "x$(id -u)" != 'x0' ]; then
    echo 'Error: this script can only be executed by root'
    exit 1
fi

# Detect OS
case $os in
    Debian)     type="debian" ;;
    Ubuntu)     type="ubuntu" ;;
    *)          type="NoSupport" ;;
esac

# Check if OS is supported
if [ "$type" = "NoSupport" ]; then
    echo "Your OS is currently not supported."
    exit 1;
fi

# Check if Vesta is installed
if [ -f /usr/local/vesta/conf/vesta.conf ]; then
    source /usr/local/vesta/conf/vesta.conf
else
    echo "Vesta not found, stopping here."
    exit 1
fi

# Check the Vesta Version
if [ ! "$VERSION" = "0.9.8" ]; then
    echo "Wrong Vesta Version, stopping here."
    exit 1
fi

# Inform abouot and ask to proceed migration.
loop=1
while [ "$loop" -eq 1 ]; do
    echo "Would you like to migrate to HestiaCP?"
    read -p "Please be warned, that we've removed and do not support softaculous and payed VestaCP extensions! [yes/no]: " sure
    if [ $sure == 'yes' ] || [ $sure == 'no' ]; then
        loop=0
        if [ $sure == 'no' ]; then
            echo "Canceling migration..."
            exit 1
        fi
    else
        echo "Please enter yes or no!"
    fi
done

# Update apt repository
echo "Updating system repository..."
apt-get -qq update

# Check if apt-transport-https is installed
if [ ! -e '/usr/lib/apt/methods/https' ]; then
    apt-get -y install apt-transport-https
    check_result $? "Can't install apt-transport-https"
fi

# Remove Vesta Repository if it exists.
echo "Removeing VestaCP Repository..."
if [ -f /usr/local/vesta/conf/vesta.conf ]; then
    rm /etc/apt/sources.list.d/vesta.list*
fi

if [ "$type" = "debian" ]; then
    # Installing sury php repo
    echo "deb https://packages.sury.org/php/ $codename main" > $apt/php.list
    wget https://packages.sury.org/php/apt.gpg -O /tmp/php_signing.key
    apt-key add /tmp/php_signing.key
fi

if [ "$type" = "ubuntu" ]; then
    # Check if apt-add-repository is installed
    if [ ! -e '/usr/bin/apt-add-repository' ]; then
        apt-get -y install python-software-properties
        check_result $? "Can't install python-software-properties"
    fi
    add-apt-repository -y ppa:ondrej/php > /dev/null 2>&1
fi

# Installing hestia repo
echo "deb https://$RHOST/ $codename main" > $apt/hestia.list
wget https://gpg.hestiacp.com/deb_signing.key -O deb_signing.key
apt-key add deb_signing.key

# Remove vesta packages
echo "Remove VestaCP packages..."
systemctl stop vesta
apt-get -qq remove vesta vesta-nginx vesta-php vesta-ioncube vesta-softaculous -y > /dev/null

# Clear up softaculous
rm -fr /usr/local/vesta/softaculous
sed -i '/SOFTACULOUS/d' /usr/local/vesta/conf/vesta.conf

# Move Vesta to Hestia Folder
mv /etc/profile.d/vesta.sh /etc/profile.d/hestia.sh
mv /usr/local/vesta $HESTIA
mv $HESTIA/conf/vesta.conf $HESTIA/conf/hestia.conf

# Add changed configuration files
echo "export HESTIA='$HESTIA'" >> /etc/profile.d/hestia.sh

rm /etc/sudoers.d/admin
cp -f $hestiacp/sudo/admin /etc/sudoers.d/
chmod 440 /etc/sudoers.d/admin

sed -i 's/vesta/hestia/g' /root/.bash_profile
sed -i 's/VESTA/HESTIA/g' /etc/profile.d/hestia.sh
sed -i 's/vesta/hestia/g' /etc/profile.d/hestia.sh

cp -rf $hestiacp/firewall $HESTIA/data/
rm -f /var/log/vesta

rm /usr/share/roundcube/plugins/password/drivers/vesta.php
cp -f $hestiacp/roundcube/hestia.php \
    /usr/share/roundcube/plugins/password/drivers/
sed -i 's/vesta/hestia/g' /etc/roundcube/config.inc.php

rm /etc/logrotate.d/vesta
cp -f $hestiacp/logrotate/vesta /etc/logrotate.d/hestia

# Install hestia packages
echo "Update System Repository and install HestiaCP Packages..."
apt-get -qq update
apt-get -qq upgrade -y
apt-get -qq install hestia hestia-nginx hestia-php -y

# Restart hestia service once
systemctl restart hestia

# Create compatiblity symlinks
ln -s $HESTIA /usr/local/vesta
ln -s $HESTIA/conf/hestia.conf /usr/local/vesta/conf/vesta.conf

# Update firewall rules
$HESTIA/bin/v-update-firewall

echo "Migration is finished, you're running now HestiaCP instead VestaCP."
echo "Please contact us if you've any troubles using our forum: https://forum.hestiacp.com"