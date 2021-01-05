#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.3

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Allow Fast CGI Cache to be enabled for Nginx Standalone
if [ -e "/etc/nginx/nginx.conf" ]; then
    echo "[*] Update Nginx to support fast cgi cache"
    
    check=$(cat /etc/nginx/nginx.conf | grep 'fastcgi_cache_path');
    if [ -z "$check" ]; then 
        echo '    [*] Install fast cgi cache support'
        
        sed  -i 's/# Cache bypass/# FastCGI Cache settings\n    fastcgi_cache_path \/var\/cache\/nginx\/php-fpm levels=2\n    keys_zone=fcgi_cache:10m inactive=60m max_size=1024m;\n    fastcgi_cache_key \"$host$request_uri $cookie_user\";\n    fastcgi_temp_path  \/var\/cache\/nginx\/temp;\n    fastcgi_ignore_headers Expires Cache-Control;\n    fastcgi_cache_use_stale error timeout invalid_header;\n    fastcgi_cache_valid any 1d;\n\n    # Cache bypass/g' /etc/nginx/nginx.conf
        
    else 
        echo '    [!] fastcgi_cache_path found skipping install of fast cgi cache support!'
    fi
fi

echo '[*] Set Role "Admin" to Administrator'
$HESTIA/bin/v-change-user-role admin admin