#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Ensure that users from previous releases are set to the correct stable release branch
if [ ! -z "$RELEASE_BRANCH" ] && [ "$RELEASE_BRANCH" = "master" ] || [ "$RELEASE_BRANCH" = "develop" ]; then
    echo "(*) Updating default release branch configuration..."
    sed -i "/RELEASE_BRANCH/d" $HESTIA/conf/hestia.conf
    echo "RELEASE_BRANCH='release'" >> $HESTIA/conf/hestia.conf
fi

# Back up old template files and install the latest versions
if [ -d $HESTIA/data/templates/ ]; then
    echo "(*) Updating web templates to enable per-domain HSTS/OCSP SSL support..."
    cp -rf $HESTIA/data/templates $HESTIA_BACKUP/templates/
    $HESTIA/bin/v-update-web-templates >/dev/null 2>&1
fi

# Remove global options from nginx.conf to prevent conflicts with other web packages
# and remove OCSP SSL stapling from global configuration as it has moved to per-domain availability in this release.
if [ -e /etc/nginx/nginx.conf ]; then
    sed -i "/add_header          X-Frame-Options SAMEORIGIN;/d" /etc/nginx/nginx.conf
    sed -i "/add_header          X-Content-Type-Options nosniff;/d" /etc/nginx/nginx.conf
    sed -i "/ssl_stapling        on;/d" /etc/nginx/nginx.conf
    sed -i "/ssl_stapling_verify on;/d" /etc/nginx/nginx.conf
fi
