#!/bin/bash

# ======================================================== #
#
# DevIT Control Panel Installation Routine
# Automatic OS detection wrapper
# https://www.DevIT.com/
#
# Currently Supported Operating Systems:
#
# Debian 11, 12
# Ubuntu 20.04, 22.04, 24.04 LTS
#
# ======================================================== #

# Am I root?
if [ "x$(id -u)" != 'x0' ]; then
	echo 'Error: this script can only be executed by root'
	exit 1
fi

# Check admin user account
if [ ! -z "$(grep ^admin: /etc/passwd)" ] && [ -z "$1" ]; then
	echo "Error: user admin exists"
	echo
	echo 'Please remove admin user before proceeding.'
	echo 'If you want to do it automatically run installer with -f option:'
	echo "Example: bash $0 --force"
	exit 1
fi

# Check admin group
if [ ! -z "$(grep ^admin: /etc/group)" ] && [ -z "$1" ]; then
	echo "Error: group admin exists"
	echo
	echo 'Please remove admin group before proceeding.'
	echo 'If you want to do it automatically run installer with -f option:'
	echo "Example: bash $0 --force"
	exit 1
fi

# Detect OS
if [ -e "/etc/os-release" ] && [ ! -e "/etc/redhat-release" ]; then
	type=$(grep "^ID=" /etc/os-release | cut -f 2 -d '=')
	if [ "$type" = "ubuntu" ]; then
		# Check if lsb_release is installed
		if [ -e '/usr/bin/lsb_release' ]; then
			release="$(lsb_release -s -r)"
			VERSION='ubuntu'
		else
			echo "lsb_release is currently not installed, please install it:"
			echo "apt-get update && apt-get install lsb-release"
			exit 1
		fi
	elif [ "$type" = "debian" ]; then
		release=$(cat /etc/debian_version | grep -o "[0-9]\{1,2\}" | head -n1)
		VERSION='debian'
	else
		type="NoSupport"
	fi
else
	type="NoSupport"
fi

no_support_message() {
	echo "****************************************************"
	echo "Your operating system (OS) is not supported by"
	echo "Hestia Control Panel. Officially supported releases:"
	echo "****************************************************"
	echo "  Debian 11, 12"
	echo "  Ubuntu 20.04, 22.04, 24.04 LTS"
	echo ""
	exit 1
}

if [ "$type" = "NoSupport" ]; then
	no_support_message
fi

check_wget_curl() {
	# Check wget
	if [ -e '/usr/bin/wget' ]; then
		wget -q https://raw.githubusercontent.com/Ghost-Dev9/DevIT/release/install/devit-install.sh
		if [ "$?" -eq '0' ]; then
			bash hst-install-$type.sh $*
			exit
		else
			echo "Error: hst-install-$type.sh download failed."
			exit 1
		fi
		# fi
	fi

	# Check curl
	if [ -e '/usr/bin/curl' ]; then
		curl -s -O https://raw.githubusercontent.com/Ghost-Dev9/DevIT/release/install/devit-install.sh
		if [ "$?" -eq '0' ]; then
			bash hst-install-$type.sh $*
			exit
		else
			echo "Error: hst-install-$type.sh download failed."
			exit 1
		fi
		# fi
	fi
}

# Check for supported operating system before proceeding with download
# of OS-specific installer, and throw error message if unsupported OS detected.
if [[ "$release" =~ ^(11|12|20.04|22.04|24.04)$ ]]; then
	check_wget_curl $*
else
	no_support_message
fi

exit
