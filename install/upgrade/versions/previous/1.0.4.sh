#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.4

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Add php-imagick package to existing version...
#php_versions=$(ls /etc/php/*/fpm -d 2>/dev/null |wc -l)
#if [ "$php_versions" -gt 1 ]; then
#    echo "[ * ] Install PHP Imageqick..."
#    software="php-imagick"
#    for v in $(ls /etc/php/); do
#        if [ ! -d "/etc/php/$v/fpm/pool.d/" ]; then
#            continue
#        fi
#        software="$software php$v-imagick"
#    done
#fi
#apt -qq update
#apt -qq install $software -y