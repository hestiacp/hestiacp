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

# Upgrading Mail System
if [ "$MAIL_SYSTEM" == "exim4" ]; then
    if ! grep -q "send_via_smarthost" /etc/exim4/exim4.conf.template; then

        echo '[*] Installing smarthost feature'
        if grep -q "driver = plaintext" /etc/exim4/exim4.conf.template; then
            disable_smarthost=true
        else
            echo '[!] Smarthost install requires manual intervention:'
            echo '        Exim only supports one plaintext authenticator.'
            echo '        If you want to use the Hestia smarthost feature,'
            echo '        please review the /etc/exim4/exim4.conf.template'
            echo '        file and resolve any conflicts.'
            disable_smarthost=false
        fi

        # Add smarthost macros to exim config
        insert=$(cat << EOL
        SMARTHOST_FILE = \${if exists{/etc/exim4/domains/\${sender_address_domain}/smarthost.conf}{/etc/exim4/domains/\$sender_address_domain/smarthost.conf}{/etc/exim4/smarthost.conf}}\n\
        SMARTHOST_HOST=\${lookup{host}lsearch{SMARTHOST_FILE}}\n\
        SMARTHOST_PORT=\${lookup{port}lsearch{SMARTHOST_FILE}}\n\
        SMARTHOST_USER=\${lookup{user}lsearch{SMARTHOST_FILE}}\n\
        SMARTHOST_PASS=\${lookup{pass}lsearch{SMARTHOST_FILE}}\n
        EOL
        )

        if [ "$disable_smarthost" = true ]; then
            insert=$(sed 's/^/#/g; s/\\n/\\n#/g; s/.$//' <<< $insert)
        fi

        line=$(expr $(sed -n '/ACL CONFIGURATION/=' /etc/exim4/exim4.conf.template) - 1)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template

        # Add smarthost authenticator
        insert=$(cat << EOL
        smarthost_login:\n\
          driver = plaintext\n\
          public_name = LOGIN\n\
          hide client_send = : SMARTHOST_USER : SMARTHOST_PASS\n
        EOL
        )

        if [ "$disable_smarthost" = true ]; then
            insert=$(sed 's/^/#/g; s/\\n/\\n#/g; s/.$//' <<< $insert)
        fi

        line=$(expr $(sed -n '/begin authenticators/=' /etc/exim4/exim4.conf.template) + 2)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template

        # Add smarthost router
        insert=$(cat << EOL
        send_via_smarthost:\n\
          driver = manualroute\n\
          address_data = SMARTHOST_HOST:SMARTHOST_PORT\n\
          domains = !+local_domains\n\
          require_files = SMARTHOST_FILE : !/etc/exim4/domains/\$sender_address_domain/no_smarthost\n\
          transport = smarthost_smtp\n\
          route_list = * \${extract{1}{:}{\$address_data}}::\${extract{2}{:}{\$address_data}}\n\
          no_more\n\
          no_verify\n
        EOL
        )

        if [ "$disable_smarthost" = true ]; then
            insert=$(sed 's/^/#/g; s/\\n/\\n#/g; s/.$//' <<< $insert)
        fi

        line=$(expr $(sed -n '/begin routers/=' /etc/exim4/exim4.conf.template) + 2)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template

        # Add smarthost transport
        insert=$(cat << EOL
        smarthost_smtp:\n\
          driver = smtp\n\
          hosts_require_auth = \$host_address\n\
          hosts_require_tls = \$host_address\n
        EOL
        )

        if [ "$disable_smarthost" = true ]; then
            insert=$(sed 's/^/#/g; s/\\n/\\n#/g; s/.$//' <<< $insert)
        fi

        line=$(expr $(sed -n '/begin transports/=' /etc/exim4/exim4.conf.template) + 2)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template
    fi
fi