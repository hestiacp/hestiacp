#!/bin/bash

# Define version check function
function version_ge(){ test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1" -o ! -z "$1" -a "$1" = "$2"; }

# Set new version number
NEW_VERSION="1.0.1"

# Set phpMyAdmin version for upgrade
pma_v='4.9.0.1'

# Set backup folder
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"

# Set installation source folder
hestiacp="$HESTIA/install/deb"

# Load hestia.conf
source /usr/local/hestia/conf/hestia.conf

####### Place additional commands below. #######

# Back up old template files and install the latest versions
if [ -d $HESTIA/data/templates/ ]; then
    echo "(*) Updating and rebuild web templates..."
    cp -rf $HESTIA/data/templates $HESTIA_BACKUP/templates/
    $HESTIA/bin/v-update-web-templates
fi

# Update Apache and Nginx configuration to support new file structure
echo "(*) Updating web server configuration..."
if [ -f /etc/apache2/apache.conf ]; then
    mv  /etc/apache2/apache.conf $HESTIA_BACKUP/conf/
    cp -f $HESTIA/install/deb/apache2/apache.conf /etc/apache2/apache.conf
fi
if [ -f /etc/nginx/nginx.conf ]; then
    mv  /etc/nginx/nginx.conf $HESTIA_BACKUP/conf/
    cp -f $HESTIA/install/deb/nginx/nginx.conf /etc/nginx/nginx.conf
fi

# Generate dhparam
if [ ! -e /etc/ssl/dhparam.pem ]; then
    mv  /etc/nginx/nginx.conf $HESTIA_BACKUP/conf/
    cp -f $hestiacp/nginx/nginx.conf /etc/nginx/

    # Copy dhparam
    cp -f $hestiacp/ssl/dhparam.pem /etc/ssl/

    # Update DNS servers in nginx.conf
    dns_resolver=$(cat /etc/resolv.conf | grep -i '^nameserver' | cut -d ' ' -f2 | tr '\r\n' ' ' | xargs)
    for ip in $dns_resolver; do
        if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            resolver="$ip $resolver"
        fi
    done
    if [ ! -z "$resolver" ]; then
        sed -i "s/1.0.0.1 1.1.1.1/$resolver/g" /etc/nginx/nginx.conf
    fi

    # Remove global options from nginx.conf to prevent conflicts with other web packages
    if [ -e /etc/nginx/nginx.conf ]; then
        sed -i "/add_header          X-Frame-Options SAMEORIGIN;/d" /etc/nginx/nginx.conf
        sed -i "/add_header          X-Content-Type-Options nosniff;/d" /etc/nginx/nginx.conf
    fi

    # Restart Nginx service
    systemctl restart nginx >/dev/null 2>&1
fi



