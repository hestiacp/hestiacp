#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.2.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Enhance Vsftpd security
if [ "$FTP_SYSTEM" = "vsftpd" ]; then
    echo "[ ! ] Hardening Vsftpd TLS configuration..."
    if [ -e /etc/vsftpd.conf ]; then
        rm -f /etc/vsftpd.conf
    fi
    cp -f $HESTIA_INSTALL_DIR/vsftpd/vsftpd.conf /etc/
    chmod 644 /etc/vsftpd.conf
fi

# Rework apt repositories
apt="/etc/apt/sources.list.d"
echo "[ * ] Hardening APT repositories..."
if [ -f "$apt/nginx.list" ]; then
    if grep -q "http://nginx.org/packages/mainline/" $apt/nginx.list; then
        echo "  ----- NGINX"
        sed -i "s/http\:\/\/nginx.org/https\:\/\/nginx.org/g" $apt/nginx.list
    fi
fi

if [ -f "$apt/php.list" ]; then
    if grep -q "http://packages.sury.org/" $apt/php.list; then
        echo "  ----- PHP"
        sed -i "s/http\:\/\/packages.sury.org/https\:\/\/packages.sury.org/g" $apt/php.list
    fi
fi

if [ -f "$apt/mariadb.list" ]; then
    if grep -q "http://ams2.mirrors.digitalocean.com" $apt/mariadb.list; then
        echo "  ----- MariaDB"
        sed -i "s/http\:\/\/ams2.mirrors.digitalocean.com/https\:\/\/mirror.mva-n.net/g" $apt/mariadb.list
    fi
fi

if [ -f "$apt/postgresql.list" ]; then
    if grep -q "http://apt.postgresql.org" $apt/postgresql.list; then
        echo "  ----- PostgreSQL"
        sed -i "s/http\:\/\/apt.postgresql.org/https\:\/\/apt.postgresql.org/g" $apt/postgresql.list
    fi
fi
