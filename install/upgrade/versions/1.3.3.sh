#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.3

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Allow Fast CGI Cache to be enabled for Nginx Standalone
if [ -e "/etc/nginx/nginx.conf" ]; then
    check=$(cat /etc/nginx/nginx.conf | grep 'fastcgi_cache_path');
    if [ -z "$check" ]; then 
        echo "[ * ] Updating Nginx to support fast cgi cache..."
        sed  -i 's/# Cache bypass/# FastCGI Cache settings\n    fastcgi_cache_path \/var\/cache\/nginx\/php-fpm levels=2\n    keys_zone=fcgi_cache:10m inactive=60m max_size=1024m;\n    fastcgi_cache_key \"$host$request_uri $cookie_user\";\n    fastcgi_temp_path  \/var\/cache\/nginx\/temp;\n    fastcgi_ignore_headers Expires Cache-Control;\n    fastcgi_cache_use_stale error timeout invalid_header;\n    fastcgi_cache_valid any 1d;\n\n    # Cache bypass/g' /etc/nginx/nginx.conf
    fi
fi

echo '[*] Set Role "Admin" to Administrator'
$HESTIA/bin/v-change-user-role admin admin

if [ "$MAIL_SYSTEM" == "exim4" ]; then
    source $HESTIA/func/ip.sh

    # Populating HELO/SMTP Banner for existing ip's
    echo "[ * ] Populating HELO/SMTP Banner param for existing ip's..."
    > /etc/exim4/mailhelo.conf

    for ip in $(v-list-sys-ips plain | cut -f1); do
        helo=$(is_ip_rdns_valid $ip)

        if [ ! -z "$helo" ]; then
            v-change-sys-ip-helo $ip $helo
        fi
    done


    # Update exim configuration
    echo "[ * ] Updating exim4 configuration..."

    # Check if smtp_active_hostname exists before adding it
    if [ ! 'grep -q ^smtp_active_hostname  /etc/exim4/exim4.conf.template' ]; then
        sed -i '/^smtp_banner = \$smtp_active_hostname$/a smtp_active_hostname = ${if exists {\/etc\/exim4\/mailhelo.conf}{${lookup{$interface_address}lsearch{\/etc\/exim4\/mailhelo.conf}{$value}{$primary_hostname}}}{$primary_hostname}}"' /etc/exim4/exim4.conf.template
    fi

    sed -i 's/helo_data = \${if exists {\/etc\/exim4\/mailhelo.conf}{${lookup{\$sender_address_domain}lsearch\*{\/etc\/exim4\/mailhelo.conf}{\$value}{\$primary_hostname}}}{\$primary_hostname}}/helo_data = ${if exists {\/etc\/exim4\/mailhelo.conf}{${lookup{$sending_ip_address}lsearch{\/etc\/exim4\/mailhelo.conf}{$value}{$primary_hostname}}}{$primary_hostname}}/' /etc/exim4/exim4.conf.template

fi
