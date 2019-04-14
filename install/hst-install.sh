#!/bin/bash
# Hestia installation wrapper
# https://www.hestiacp.com

#
# Currently Supported Operating Systems:
#
#   Debian 8, 9
#   Ubuntu 14.04, 16.04, 18.04
#

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
case $(head -n1 /etc/issue | cut -f 1 -d ' ') in
    Debian)     type="debian" ;;
    Ubuntu)     type="ubuntu" ;;
    *)          type="NoSupport" ;;
esac

no_support_message() {
    echo "Your OS is currently not supported, please consider to use:"
    echo "  Debian:  8, 9"
    echo "  Ubuntu:  16.04, 18.04"
    exit 1;
}

# Check if OS is supported
if [ "$type" = "NoSupport" ]; then
    no_support_message
fi

check_wget_curl(){
    # Check wget
    if [ -e '/usr/bin/wget' ]; then
        wget -q https://raw.githubusercontent.com/hestiacp/hestiacp/master/install/hst-install-$type.sh -O hst-install-$type.sh
        if [ "$?" -eq '0' ]; then
            bash hst-install-$type.sh $*
            exit
        else
            echo "Error: hst-install-$type.sh download failed."
            exit 1
        fi
    fi

    # Check curl
    if [ -e '/usr/bin/curl' ]; then
        curl -s -O https://raw.githubusercontent.com/hestiacp/hestiacp/master/install/hst-install-$type.sh
        if [ "$?" -eq '0' ]; then
            bash hst-install-$type.sh $*
            exit
        else
            echo "Error: hst-install-$type.sh download failed."
            exit 1
        fi
    fi
}


# Detect codename for debian
if [ "$type" = "debian" ]; then
    release=$(cat /etc/debian_version|grep -o [0-9]|head -n1)
    VERSION='debian'
fi

# Detect codename for ubuntu
if [ "$type" = "ubuntu" ]; then
    release="$(lsb_release -s -r)"
    VERSION='ubuntu'
fi

# Check Ubuntu Version Are Acceptable to install
if [[ "$release" =~ ^(8|9|16.04|18.04)$ ]]; then
    check_wget_curl
else
    no_support_message
fi

exit