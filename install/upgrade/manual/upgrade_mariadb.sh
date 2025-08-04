#!/bin/bash

# This script validates and upgrades the MariaDB version to 11.8

#----------------------------------------------------------#
#                   Variable & Function                    #
#----------------------------------------------------------#

# Set MariaDB Target Version
mariadb_v='11.8'

#----------------------------------------------------------#
#                      Verifications                       #
#----------------------------------------------------------#

# Detect installed MariaDB version
mysql_v="$(mysqld -V 2> /dev/null | awk '{print $3}' | cut -d: -f1)"

if [ "${mysql_v%.*}" = "$mariadb_v" ]; then
	echo "[ ! ] MariaDB version ($mariadb_v) is already up to date."
	exit 0
else
	echo "[ * ] Upgrading MariaDB version to ($mariadb_v)..."
fi

# Get OS details
os="$(grep "^ID=" /etc/os-release | cut -d= -f2 | tr -d '"')"
codename="$(lsb_release -s -c)"

# Validate supported OS
if [[ "$os" != "ubuntu" && "$os" != "debian" ]]; then
	echo "[ ! ] Unsupported OS: $os"
	exit 1
fi

# Validate codename
supported_codenames=("focal" "jammy" "noble" "bullseye" "bookworm")
if [[ ! " ${supported_codenames[*]} " =~ " $codename " ]]; then
	echo "[ ! ] Unsupported codename: $codename"
	exit 1
fi

# Detect architecture
case $(arch) in
	x86_64)
		arch="amd64"
		;;
	aarch64)
		arch="arm64"
		;;
	*)
		echo "[ ! ] Error: Unsupported architecture '$(arch)'"
		exit 1
		;;
esac

#----------------------------------------------------------#
#                         Action                           #
#----------------------------------------------------------#

# Install MariaDB repository
apt="/etc/apt/sources.list.d"
echo "[ * ] Installing MariaDB 11.8 repository..."
echo "deb [arch=$arch signed-by=/usr/share/keyrings/mariadb-keyring.gpg] https://deb.mariadb.org/${mariadb_v}/${os} $codename main" | sudo tee $apt/mariadb.list > /dev/null

# Add MariaDB signing key
echo "[ * ] Downloading MariaDB signing key..."
sudo mkdir -p /usr/share/keyrings
curl -s https://mariadb.org/mariadb_release_signing_key.pgp | gpg --dearmor | sudo tee /usr/share/keyrings/mariadb-keyring.gpg > /dev/null

# Update repository
echo "[ * ] Updating apt repository..."
sudo apt update -qq

# Stop and uninstall old MariaDB version
echo "[ * ] Stopping and removing old MariaDB Server (${mysql_v%.*})..."
sudo systemctl -q stop mariadb mysql 2> /dev/null || true
sudo apt remove -qq mariadb-server -y > /dev/null 2>&1

# Install new version
echo "[ * ] Installing new MariaDB Server ($mariadb_v)..."
sudo apt install -qq mariadb-server -y

# Enable and start service
echo "[ * ] Enabling and starting MariaDB service..."
sudo update-rc.d mariadb defaults > /dev/null 2>&1
sudo systemctl -q daemon-reload
sudo systemctl -q enable mariadb
sudo systemctl -q start mariadb

# Upgrade MariaDB databases
echo "[ * ] Running mariadb-upgrade..."
sudo mariadb-upgrade

echo "✅ MariaDB $mariadb_v installation and upgrade complete."
