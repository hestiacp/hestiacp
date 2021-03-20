#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.4.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Allow Fast CGI Cache to be enabled for Nginx Standalone
if [ -e "/etc/nginx/nginx.conf" ]; then
    check=$(cat /etc/nginx/nginx.conf | grep 'fastcgi_cache_path');
    if [ -z "$check" ]; then 
        echo "[ * ] Enabling Nginx FastCGI cache support..."
        sed  -i 's/# Cache bypass/# FastCGI Cache settings\n    fastcgi_cache_path \/var\/cache\/nginx\/php-fpm levels=2\n    keys_zone=fcgi_cache:10m inactive=60m max_size=1024m;\n    fastcgi_cache_key \"$host$request_uri $cookie_user\";\n    fastcgi_temp_path  \/var\/cache\/nginx\/temp;\n    fastcgi_ignore_headers Expires Cache-Control;\n    fastcgi_cache_use_stale error timeout invalid_header;\n    fastcgi_cache_valid any 1d;\n\n    # Cache bypass/g' /etc/nginx/nginx.conf
    fi
fi

echo '[ * ] Updating System Administrator account permissions...'
$HESTIA/bin/v-change-user-role admin admin

# Populating HELO/SMTP Banner for existing ip's
if [ "$MAIL_SYSTEM" == "exim4" ]; then
    source $HESTIA/func/ip.sh

    echo "[ * ] Populating HELO/SMTP Banner param for existing IP's..."
    > /etc/exim4/mailhelo.conf

    for ip in $($BIN/v-list-sys-ips plain | cut -f1); do
        helo=$(is_ip_rdns_valid $ip)

        if [ ! -z "$helo" ]; then
            $BIN/v-change-sys-ip-helo $ip $helo
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

# Upgrading Mail System
if [ "$MAIL_SYSTEM" == "exim4" ]; then
    if ! grep -q "send_via_smtp_relay" /etc/exim4/exim4.conf.template; then

        echo '[ * ] Enabling SMTP relay support...'
        if grep -q "driver = plaintext" /etc/exim4/exim4.conf.template; then
            disable_smtp_relay=true
            echo '[ ! ] ERROR: Manual intervention required to enable SMTP Relay:'
            echo ''
            echo '      Exim only supports one plaintext authenticator.'
            echo '      If you want to use the Hestia smtp relay feature,'
            echo '      please review the /etc/exim4/exim4.conf.template'
            echo '      file and resolve any conflicts.'
        else
            disable_smtp_relay=false
        fi

        # Add smtp relay macros to exim config
        insert='SMTP_RELAY_FILE = ${if exists{/etc/exim4/domains/${sender_address_domain}/smtp_relay.conf}{/etc/exim4/domains/$sender_address_domain/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}\n\SMTP_RELAY_HOST=${lookup{host}lsearch{SMTP_RELAY_FILE}}\n\SMTP_RELAY_PORT=${lookup{port}lsearch{SMTP_RELAY_FILE}}\n\SMTP_RELAY_USER=${lookup{user}lsearch{SMTP_RELAY_FILE}}\n\SMTP_RELAY_PASS=${lookup{pass}lsearch{SMTP_RELAY_FILE}}\n'

        if [ "$disable_smtp_relay" = true ]; then
            insert=$(sed 's/^/#/g; s/\\n/\\n#/g; s/.$//' <<< $insert)
        fi

        line=$(expr $(sed -n '/ACL CONFIGURATION/=' /etc/exim4/exim4.conf.template) - 1)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template

        # Add smtp relay authenticator
        insert='smtp_relay_login:\n\  driver = plaintext\n\  public_name = LOGIN\n\  hide client_send = : SMTP_RELAY_USER : SMTP_RELAY_PASS\n'

        if [ "$disable_smtp_relay" = true ]; then
            insert=$(sed 's/^/#/g; s/\\n/\\n#/g; s/.$//' <<< $insert)
        fi

        line=$(expr $(sed -n '/begin authenticators/=' /etc/exim4/exim4.conf.template) + 2)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template

        # Add smtp relay router
        insert='send_via_smtp_relay:\n\  driver = manualroute\n\  address_data = SMTP_RELAY_HOST:SMTP_RELAY_PORT\n\  domains = !+local_domains\n\  require_files = SMTP_RELAY_FILE\n\  transport = smtp_relay_smtp\n\  route_list = * ${extract{1}{:}{$address_data}}::${extract{2}{:}{$address_data}}\n\  no_more\n\  no_verify\n'

        if [ "$disable_smtp_relay" = true ]; then
            insert=$(sed 's/^/#/g; s/\\n/\\n#/g; s/.$//' <<< $insert)
        fi

        line=$(expr $(sed -n '/begin routers/=' /etc/exim4/exim4.conf.template) + 2)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template

        # Add smtp relay transport
        insert='smtp_relay_smtp:\n\  driver = smtp\n\  hosts_require_auth = $host_address\n\  hosts_require_tls = $host_address\n'

        if [ "$disable_smtp_relay" = true ]; then
            insert=$(sed 's/^/#/g; s/\\n/\\n#/g; s/.$//' <<< $insert)
        fi

        line=$(expr $(sed -n '/begin transports/=' /etc/exim4/exim4.conf.template) + 2)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template
    fi
    
    $HESTIA/bin/v-restart-mail
fi

# Fix PostgreSQL repo
if [ -f /etc/apt/sources.list.d/postgresql.list ]; then
    echo "[ * ] Updating PostgreSQL repository..."
    sed -i 's|deb https://apt.postgresql.org/pub/repos/apt/|deb [arch=amd64] https://apt.postgresql.org/pub/repos/apt/|g' /etc/apt/sources.list.d/postgresql.list
fi


##### COMMANDS FOR V1.5.X

# Back up default package and install latest version
if [ -d $HESTIA/data/packages/ ]; then
    echo "[ * ] Migrating legacy default package for all users..."
    $HESTIA/bin/v-rename-user-package default custom >/dev/null 2>&1
    echo "[ * ] Replacing default package..."
    cp -f $HESTIA_INSTALL_DIR/packages/default.pkg $HESTIA/data/packages/
fi
