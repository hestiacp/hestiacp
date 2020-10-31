#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Remove global options from nginx.conf to prevent conflicts with other web packages
# and remove OCSP SSL stapling from global configuration as it has moved to per-domain availability in this release.
if [ -e /etc/nginx/nginx.conf ]; then
    sed -i "/add_header          X-Frame-Options SAMEORIGIN;/d" /etc/nginx/nginx.conf
    sed -i "/add_header          X-Content-Type-Options nosniff;/d" /etc/nginx/nginx.conf
    sed -i "/ssl_stapling        on;/d" /etc/nginx/nginx.conf
    sed -i "/ssl_stapling_verify on;/d" /etc/nginx/nginx.conf
fi
