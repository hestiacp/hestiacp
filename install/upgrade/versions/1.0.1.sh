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

# Remove global options from nginx.conf to prevent conflicts with other web packages
if [ -e /etc/nginx/nginx.conf ]; then
    echo "(*) Updating nginx configuration.."
    sed -i "/add_header          X-Frame-Options SAMEORIGIN;/d" /etc/nginx/nginx.conf
    sed -i "/add_header          X-Content-Type-Options nosniff;/d" /etc/nginx/nginx.conf
fi
