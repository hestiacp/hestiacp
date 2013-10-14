#!/bin/bash
# Vesta installation wrapper
# http://vestacp.com


# Am I root?
if [ "x$(id -u)" != 'x0' ]; then
    echo 'Error: this script can only be executed by root'
    exit 1
fi

# Check admin user account
if [ ! -z "$(grep ^admin: /etc/passwd)" ] && [ "$force" != 'yes' ]; then
    echo "Error: user admin exists"
    echo
    echo 'Please remove admin user account before proceeding.'
    echo 'If you want to do it automatically run installer with -f option:'
    echo "Example: bash $0 --force"
    exit 1
fi

# Check admin user account
if [ ! -z "$(grep ^admin: /etc/group)" ] && [ "$force" != 'yes' ]; then
    echo "Error: user admin exists"
    echo
    echo 'Please remove admin user account before proceeding.'
    echo 'If you want to do it automatically run installer with -f option:'
    echo "Example: bash $0 --force"
    exit 1
fi

# Check OS type
if [ -e '/etc/redhat-release' ]; then
    type="rhel"
fi

if [ -e '/etc/lsb-release' ]; then
    type="ubuntu"
fi

if [ -z "$type" ]; then
    os=$(head -n1 /etc/issue | cut -f 1 -d ' ')
    if [ "$os" == 'Debian' ]; then
        type="debian"
    fi
fi

# Check type
if [ -z "$type" ]; then
    echo 'Error: only RHEL,CentOS, Ubuntu LTS and Debian 7 is supported'
    exit 1
fi


# Check wget
if [ -e '/usr/bin/wget' ]; then
    wget http://vestacp.com/pub/vst-install-$type.sh -O vst-install-$type.sh
    if [ "$?" -eq '0' ]; then
        bash vst-install-$type.sh $*
        exit
    else
        echo "Error: vst-install-$type.sh download failed."
        exit 1
    fi
fi

# Check curl
if [ -e '/usr/bin/curl' ]; then
    curl -O http://vestacp.com/pub/vst-install-$type.sh
    if [ "$?" -eq '0' ]; then
        bash vst-install-$type.sh $*
        exit
    else
        echo "Error: vst-install-$type.sh download failed."
        exit 1
    fi
fi

# Let's try to install wget automaticaly
if [ "$type" = 'rhel' ]; then
    yum -y install wget
    if [ $? -ne 0 ]; then
        echo "Error: can't install wget"
        exit 1
    fi
else
    apt-get -y install wget
    if [ $? -ne 0 ]; then
        echo "Error: can't install wget"
        exit 1
    fi
fi

# OK, last try
if [ -e '/usr/bin/wget' ]; then
    wget http://vestacp.com/pub/vst-install-$type.sh -O vst-install-$type.sh
    if [ "$?" -eq '0' ]; then
        bash vst-install-$type.sh $*
        exit
    else
        echo "Error: vst-install-$type.sh download failed."
        exit 1
    fi
else
    echo "Error: /usr/bin/wget not found"
    exit 1
fi

exit
