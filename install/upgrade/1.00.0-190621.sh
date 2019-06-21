#!/bin/bash

# Define global variables
if [ -z "$HESTIA" ] || [ ! -f "${HESTIA}/conf/hestia.conf" ]; then
    export HESTIA="/usr/local/hestia"
    export BIN="/usr/local/hestia/bin"
fi

# Set backup folder
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"

# Set installation source folder
hestiacp="$HESTIA/install/deb"

# Load hestia.conf
source /usr/local/hestia/conf/hestia.conf

# Compare version for upgrade routine
if [ "$VERSION" != "1.00.0-190618" ] && [ "$VERSION" != "0.10.0" ] then
    source $HESTIA/install/upgrade/1.00.0-190618.sh
fi

# Get hestia version
version=$(dpkg -l | awk '$2=="hestia" { print $3 }')

# To Do: Write version to hestia.conf.

# Place additional commands below.

# Remove global options from nginx.conf to prevent conflicts with other web packages
if [ -e /etc/nginx/nginx.conf ]; then
    echo "(*) Updating NGINX global configuration..."
    sed -i 's/add_header          X-Frame-Options SAMEORIGIN;/d' /etc/nginx/nginx.conf
    sed -i 's/add_header          X-Content-Type-Options nosniff;/d' /etc/nginx/nginx.conf
fi
