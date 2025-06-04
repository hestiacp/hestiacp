#!/bin/bash

# DevIT Control Panel upgrade script for target version 1.4.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Add support for nginx FastCGI cache (standalone)
if [ -e "/etc/nginx/nginx.conf" ]; then
	check=$(cat /etc/nginx/nginx.conf | grep 'fastcgi_cache_path')
	if [ -z "$check" ]; then
		echo "[ * ] Enabling nginx FastCGI cache support..."
		sed -i 's/# Cache bypass/# FastCGI cache\n    fastcgi_cache_path \/var\/cache\/nginx\/micro levels=1:2 keys_zone=microcache:10m max_size=1024m inactive=30m;\n    fastcgi_cache_key \"$scheme$request_method$host$request_uri\";\n    fastcgi_cache_methods GET HEAD;\n    fastcgi_cache_use_stale updating error timeout invalid_header http_500 http_503;\n    fastcgi_ignore_headers Cache-Control Expires Set-Cookie;\n    add_header X-FastCGI-Cache \$upstream_cache_status;\n\n    # Cache bypass/g' /etc/nginx/nginx.conf
		sed -i 's/    fastcgi_cache_lock_timeout      5s;/    fastcgi_cache_lock_timeout      5s;\n    fastcgi_cache_background_update on;\n    fastcgi_cache_revalidate        on;/g' /etc/nginx/nginx.conf
	fi
fi

if [ -e "/etc/nginx/nginx.conf" ]; then
	echo "[ * ] Updating nginx configuration with changes to Cloudflare IP addresses"
	sed -i 's/    set_real_ip_from 104.16.0.0\/12;/    set_real_ip_from 104.16.0.0\/13;\n    set_real_ip_from 104.24.0.0\/14;/g' /etc/nginx/nginx.conf
fi

# Populating HELO/SMTP Banner for existing IPs
if [ "$MAIL_SYSTEM" == "exim4" ]; then

	# Check if we've already done this upgrade before proceeding
	if ! grep -q ^smtp_active_hostname /etc/exim4/exim4.conf.template; then

		source $DevIT/func/ip.sh

		echo "[ * ] Populating HELO/SMTP Banner value for existing IP addresses..."
		> /etc/exim4/mailhelo.conf

		for ip in $($BIN/v-list-sys-ips plain | cut -f1); do
			helo=$(is_ip_rdns_valid $ip)

			if [ ! -z "$helo" ]; then
				$BIN/v-change-sys-ip-helo $ip $helo
			fi
		done

		# Update exim configuration
		echo "[ * ] Updating exim4 configuration..."

		# Add new smtp_active_hostname variable to exim config
		sed -i '/^smtp_banner = \$smtp_active_hostname$/a smtp_active_hostname = ${if exists {\/etc\/exim4\/mailhelo.conf}{${lookup{$interface_address}lsearch{\/etc\/exim4\/mailhelo.conf}{$value}{$primary_hostname}}}{$primary_hostname}}' /etc/exim4/exim4.conf.template

		# Lookup HELO address by sending ip instead of sending domain
		sed -i 's/helo_data = \${if exists {\/etc\/exim4\/mailhelo.conf}{${lookup{\$sender_address_domain}lsearch\*{\/etc\/exim4\/mailhelo.conf}{\$value}{\$primary_hostname}}}{\$primary_hostname}}/helo_data = ${if exists {\/etc\/exim4\/mailhelo.conf}{${lookup{$sending_ip_address}lsearch{\/etc\/exim4\/mailhelo.conf}{$value}{$primary_hostname}}}{$primary_hostname}}/' /etc/exim4/exim4.conf.template
	fi
fi

# Upgrading Mail System
if [ "$MAIL_SYSTEM" == "exim4" ]; then
	if ! grep -q "send_via_smtp_relay" /etc/exim4/exim4.conf.template; then

		echo '[ * ] Enabling SMTP relay support...'
		if grep -q "driver = plaintext" /etc/exim4/exim4.conf.template; then
			disable_smtp_relay=true
			echo '[ ! ] ERROR: SMTP Relay upgrade failed:'
			echo ''
			echo 'Because of the complexity of the SMTP Relay upgrade,'
			echo 'we were unable to safely modify your existing exim config file.'
			echo 'If you would like to use the new SMTP Relay features,'
			echo 'you will have to replace or modify your config with the one found'
			echo 'on GitHub at https://github.com/DevITcp/DevITcp/blob/release/install/deb/exim/exim4.conf.template.'
			echo 'Your exim config file will be found here: /etc/exim4/exim4.conf.template'
			$DevIT/bin/v-add-user-notification admin 'SMTP Relay upgrade failed' 'Because of the complexity of the SMTP Relay upgrade, we were unable to safely modify your existing exim config file.<br><br>If you would like to use the new SMTP Relay features, you will have to replace or modify your config with the one <a href="https://github.com/DevITcp/DevITcp/blob/release/install/deb/exim/exim4.conf.template" target="_blank">found on GitHub</a>.<br><br>Your exim config file will be found here:<br><br><code>/etc/exim4/exim4.conf.template</code>'
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
fi

# Set default webmail system for mail domains
if [ -n "$WEBMAIL_SYSTEM" ]; then
	for user in $($BIN/v-list-users plain | cut -f1); do
		for domain in $($BIN/v-list-mail-domains $user plain | cut -f1); do
			$BIN/v-add-mail-domain-webmail $user $domain '' no
		done
	done
fi

# Fix PostgreSQL repo
if [ -f /etc/apt/sources.list.d/postgresql.list ]; then
	echo "[ * ] Updating PostgreSQL repository..."
	sed -i 's|deb https://apt.postgresql.org/pub/repos/apt/|deb [arch=amd64] https://apt.postgresql.org/pub/repos/apt/|g' /etc/apt/sources.list.d/postgresql.list
fi

# Remove API file if API is set to "no"
if [ "$API" = "no" ]; then
	if [ -f "$DevIT/web/api/index.php" ]; then
		echo "[ * ] Disabling API access..."
		$DevIT/bin/v-change-sys-api remove
	fi
fi

# Back up users existing configuration data to $DevIT/conf/defaults/DevIT.conf
if [ ! -f "$DevIT/conf/defaults/DevIT.conf" ]; then
	echo "[ * ] Creating known good configuration data for system recovery..."
	if [ ! -d "$DevIT/conf/defaults/" ]; then
		mkdir -p "$DevIT/conf/defaults/"
	fi
	cp -f $DevIT/conf/DevIT.conf $DevIT/conf/defaults/DevIT.conf
fi

if [ -f "/usr/lib/networkd-dispatcher/routable.d/50-ifup-hooks" ]; then
	echo "[ * ] Fix potenial issue with multiple network adapters and netplan..."
	rm "/usr/lib/networkd-dispatcher/routable.d/50-ifup-hooks"
	$BIN/v-update-firewall
fi

# Consolidate nginx (standalone) templates used by active websites
if [ "$WEB_SYSTEM" = "nginx" ]; then
	echo "[ * ] Consolidating nginx templates for Drupal & CodeIgniter..."
	sed -i "s|TPL='drupal6'|TPL='drupal'|g" $DevIT/data/users/*/web.conf
	sed -i "s|TPL='drupal7'|TPL='drupal'|g" $DevIT/data/users/*/web.conf
	sed -i "s|TPL='drupal8'|TPL='drupal'|g" $DevIT/data/users/*/web.conf
	sed -i "s|TPL='codeigniter2'|TPL='codeigniter'|g" $DevIT/data/users/*/web.conf
	sed -i "s|TPL='codeigniter3'|TPL='codeigniter'|g" $DevIT/data/users/*/web.conf
fi

# Remove outdated nginx templates
echo "[ * ] Removing outdated nginx templates..."
rm -rf $DevIT/data/templates/web/nginx/php-fpm/drupal6.*tpl
rm -rf $DevIT/data/templates/web/nginx/php-fpm/drupal7.*tpl
rm -rf $DevIT/data/templates/web/nginx/php-fpm/drupal8.*tpl
rm -rf $DevIT/data/templates/web/nginx/php-fpm/codeigniter2.*tpl
rm -rf $DevIT/data/templates/web/nginx/php-fpm/codeigniter3.*tpl

# Clean up old DevIT controlled webapps
if [ -d "$DevIT/web/images/webapps/" ]; then
	echo "[ * ] Clean up old web apps code..."
	rm -rf $DevIT/web/images/webapps/
	rm -rf $DevIT/web/src/app/WebApp/Installers/LaravelSetup.php
	rm -rf $DevIT/web/src/app/WebApp/Installers/OpencartSetup.php
	rm -rf $DevIT/web/src/app/WebApp/Installers/PrestashopSetup.php
	rm -rf $DevIT/web/src/app/WebApp/Installers/SymfonySetup.php
	rm -rf $DevIT/web/src/app/WebApp/Installers/WordpressSetup.php
	rm -rf $DevIT/web/src/app/WebApp/Installers/Joomla
fi

# Update ClamAV configuration file
if [ -f "/etc/clamav/clamd.conf" ]; then
	cp -f $DevIT_INSTALL_DIR/clamav/clamd.conf /etc/clamav/
	$DevIT/bin/v-add-user-notification admin 'ClamAV config has been overwritten' 'Warning: If you have manualy changed /etc/clamav/clamd.conf and any changes you made will be lost an backup has been created in the /root/hst_backups folder with the original config. If you have not changed the config file you can ignore this message'
fi

##### COMMANDS FOR V1.5.X

# Back up default package and install latest version
if [ -d $DevIT/data/packages/ ]; then
	echo "[ * ] Migrating legacy default package for all users..."
	$DevIT/bin/v-rename-user-package default custom > /dev/null 2>&1
	echo "[ * ] Replacing default package..."
	cp -f $DevIT_INSTALL_DIR/packages/default.pkg $DevIT/data/packages/
fi
