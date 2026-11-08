#!/bin/bash

# This script validates and upgrades the MariaDB version

#----------------------------------------------------------#
#                   Variable & Function                    #
#----------------------------------------------------------#

# Set MariaDB Target Version
mariadb_v='11.8'

#----------------------------------------------------------#
#                      Verifications                       #
#----------------------------------------------------------#

# Detect installed MariaDB version
mysql_v="$(mysqld -V 2> /dev/null || mariadbd -V 2> /dev/null)"
mysql_v="$(echo "$mysql_v" | awk '{print $3}' | cut -d- -f1 | cut -d: -f1)"

if [ "${mysql_v%.*}" = "$mariadb_v" ] || dpkg --compare-versions "${mysql_v%.*}" ge "$mariadb_v" 2> /dev/null; then
	echo "[ ! ] MariaDB version ($mariadb_v) is already up to date."
	exit 0
else
	echo "[ * ] Upgrading MariaDB version to ($mariadb_v)..."
fi

# Get OS details
os="$(grep "^ID=" /etc/os-release | cut -d= -f2)"
codename="$(lsb_release -s -c)"

case $(arch) in
	x86_64)
		arch="amd64"
		;;
	aarch64)
		arch="arm64"
		;;
	*)
		echo "[ ! ] Error: $(arch) is currently not supported!"
		exit 1
		;;
esac

#----------------------------------------------------------#
#                         Action                           #
#----------------------------------------------------------#

# Installing MariaDB repository
apt="/etc/apt/sources.list.d"
echo "[ * ] Installing MariaDB repository..."
echo "deb [arch=$arch signed-by=/usr/share/keyrings/mariadb-keyring.gpg] https://dlm.mariadb.com/repo/mariadb-server/$mariadb_v/repo/$os $codename main" > $apt/mariadb.list
curl -s https://mariadb.org/mariadb_release_signing_key.asc | gpg --dearmor | tee /usr/share/keyrings/mariadb-keyring.gpg > /dev/null 2>&1

cat > /etc/apt/apt.conf.d/60unattended-upgrades-hestia-mariadb << 'EOF'
Unattended-Upgrade::Origins-Pattern {
	"origin=MariaDB";
};
EOF

# Update repository
echo "[ * ] Update apt repository..."
apt update -qq > /dev/null 2>&1

# Stop and uninstall old version
echo "[ * ] Stop and remove old MariaDB Server (${mysql_v%.*})..."
systemctl -q stop mariadb mysql 2> /dev/null
apt remove -qq mariadb-server -y > /dev/null 2>&1

# Install new version and run upgrade
echo "[ * ] Installing new MariaDB Server, start and run upgrade..."
apt install -qq mariadb-server -y
update-rc.d mariadb defaults > /dev/null 2>&1
systemctl -q daemon-reload
systemctl -q enable mariadb
systemctl -q start mariadb
mariadb-upgrade
