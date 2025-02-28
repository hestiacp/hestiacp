#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.8.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### upgrade_config_set_value only accepts true or false.                    #######
#######                                                                         #######
####### Pass through information to the end user in case of a issue or problem  #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### in the upgrade notification email. Example:                             #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

os_release="$(lsb_release -s -i | tr "[:upper:]" "[:lower:]")-$(lsb_release -s -r)"

if [ "$IMAP_SYSTEM" = "dovecot" ]; then
	if ! grep -qw "^extra_groups = mail$" /etc/dovecot/conf.d/10-master.conf 2> /dev/null; then
		sed -i "s/^service auth {/service auth {\n  extra_groups = mail\n/g" /etc/dovecot/conf.d/10-master.conf
	fi

	if [ -f /etc/dovecot/conf.d/90-sieve.conf ]; then
		if ! grep -q "sieve_vacation_send_from_recipient" /etc/dovecot/conf.d/90-sieve.conf 2> /dev/null; then
			sed -i "s/^plugin {/plugin {\n  # This setting determines whether vacation messages are sent with the SMTP MAIL FROM envelope address set to the recipient address of the Sieve script owner.\n  sieve_vacation_send_from_recipient = yes\n/g" /etc/dovecot/conf.d/90-sieve.conf
		fi
	fi
fi

if [ -f /etc/fail2ban/jail.local ]; then
	# Add phpmyadmin rule
	if ! grep -qw "phpmyadmin-auth" /etc/fail2ban/jail.local 2> /dev/null; then
		sed -i '/\[recidive\]/i [phpmyadmin-auth]\nenabled  = true\nfilter   = phpmyadmin-syslog\naction   = hestia[name=WEB]\nlogpath  = /var/log/auth.log\nmaxretry = 5\n' /etc/fail2ban/jail.local
	fi
fi

if [ "$MAIL_SYSTEM" = "exim4" ]; then
	if [ "$os_release" != "debian-10" ]; then
		# Exclude Debian 10...
		echo "[ * ] Disable SMTPUTF8 for Exim for now"
		if ! grep -qw "^smtputf8_advertise_hosts =" /etc/exim4/exim4.conf.template 2> /dev/null; then
			sed -i "/^domainlist local_domains = dsearch;\/etc\/exim4\/domains/i smtputf8_advertise_hosts =" /etc/exim4/exim4.conf.template
		fi
	fi
fi

# Apply the update for existing users to enable the "Enhanced and Optimized TLS" feature
echo '[ * ] Enable the "Enhanced and Optimized TLS" feature...'

# Configuring global OpenSSL options
tls13_ciphers="TLS_AES_128_GCM_SHA256:TLS_CHACHA20_POLY1305_SHA256:TLS_AES_256_GCM_SHA384"

if ! grep -qw "^[hestia_openssl_sect]$" /etc/ssl/openssl.cnf 2> /dev/null; then
	if [ "$os_release" = "debian-10" ] || [ "$os_release" = "debian-11" ]; then
		sed -i '/^system_default = system_default_sect$/a system_default = hestia_openssl_sect\n\n[hestia_openssl_sect]\nCiphersuites = '"$tls13_ciphers"'\nOptions = PrioritizeChaCha' /etc/ssl/openssl.cnf
	elif [ "$os_release" = "debian-12" ]; then
		if ! grep -qw "^ssl_conf = ssl_sect$" /etc/ssl/openssl.cnf 2> /dev/null; then
			sed -i '/providers = provider_sect$/a ssl_conf = ssl_sect' /etc/ssl/openssl.cnf
		fi

		if ! grep -qw "^[ssl_sect]$" /etc/ssl/openssl.cnf 2> /dev/null; then
			sed -i '$a \\n[ssl_sect]\nsystem_default = hestia_openssl_sect\n\n[hestia_openssl_sect]\nCiphersuites = '"$tls13_ciphers"'\nOptions = PrioritizeChaCha' /etc/ssl/openssl.cnf
		elif grep -qw "^system_default = system_default_sect$" /etc/ssl/openssl.cnf 2> /dev/null; then
			sed -i '/^system_default = system_default_sect$/a system_default = hestia_openssl_sect\n\n[hestia_openssl_sect]\nCiphersuites = '"$tls13_ciphers"'\nOptions = PrioritizeChaCha' /etc/ssl/openssl.cnf
		fi
	elif [ "$os_release" = "ubuntu-20.04" ]; then
		if ! grep -qw "^openssl_conf = default_conf$" /etc/ssl/openssl.cnf 2> /dev/null; then
			sed -i '/^oid_section		= new_oids$/a \\n# System default\nopenssl_conf = default_conf' /etc/ssl/openssl.cnf
		fi

		if ! grep -qw "^[default_conf]$" /etc/ssl/openssl.cnf 2> /dev/null; then
			sed -i '$a [default_conf]\nssl_conf = ssl_sect\n\n[ssl_sect]\nsystem_default = hestia_openssl_sect\n\n[hestia_openssl_sect]\nCiphersuites = '"$tls13_ciphers"'\nOptions = PrioritizeChaCha' /etc/ssl/openssl.cnf
		elif grep -qw "^system_default = system_default_sect$" /etc/ssl/openssl.cnf 2> /dev/null; then
			sed -i '/^system_default = system_default_sect$/a system_default = hestia_openssl_sect\n\n[hestia_openssl_sect]\nCiphersuites = '"$tls13_ciphers"'\nOptions = PrioritizeChaCha' /etc/ssl/openssl.cnf
		fi
	elif [ "$os_release" = "ubuntu-22.04" ]; then
		sed -i '/^system_default = system_default_sect$/a system_default = hestia_openssl_sect\n\n[hestia_openssl_sect]\nCiphersuites = '"$tls13_ciphers"'\nOptions = PrioritizeChaCha' /etc/ssl/openssl.cnf
	fi
fi

# Update server configuration files
tls12_ciphers="ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384:DHE-RSA-AES256-SHA256"

if [ "$IMAP_SYSTEM" = "dovecot" ]; then
	if grep -qw "^ssl_min_protocol = TLSv1.2$" /etc/dovecot/conf.d/10-ssl.conf 2> /dev/null; then
		sed -i '/^# See #2012 for TLSv1.1 to 1.2 upgrade$/d;/^ssl_cipher_list = .\+$/d;s/^ssl_min_protocol = TLSv1.2/ssl_cipher_list = '"$tls12_ciphers"'\nssl_min_protocol = TLSv1.2/' /etc/dovecot/conf.d/10-ssl.conf
	elif grep -qw "^ssl_protocols = \!SSLv3 \!TLSv1 \!TLSv1.1$" /etc/dovecot/conf.d/10-ssl.conf 2> /dev/null; then
		sed -i '/^# See #2012 for TLSv1.1 to 1.2 upgrade$/d;/^ssl_cipher_list = .\+$/d;s/^ssl_protocols = !SSLv3 !TLSv1 !TLSv1.1/ssl_cipher_list = '"$tls12_ciphers"'\nssl_protocols = !SSLv3 !TLSv1 !TLSv1.1/' /etc/dovecot/conf.d/10-ssl.conf
	fi
fi

if [ "$MAIL_SYSTEM" = "exim4" ]; then
	if grep -qw "^tls_on_connect_ports = 465$" /etc/exim4/exim4.conf.template 2> /dev/null; then
		sed -i '/^tls_require_ciphers = .\+$/d;s/^tls_on_connect_ports = 465/tls_on_connect_ports = 465\ntls_require_ciphers = PERFORMANCE:-RSA:-VERS-ALL:+VERS-TLS1.2:+VERS-TLS1.3:%SERVER_PRECEDENCE/' /etc/exim4/exim4.conf.template
	fi
fi

if [ "$FTP_SYSTEM" = "proftpd" ]; then
	if grep -qw "^TLSProtocol                             TLSv1.2$" /etc/proftpd/tls.conf 2> /dev/null; then
		sed -i '/^TLSCipherSuite .\+$/d;/^TLSServerCipherPreference .\+$/d;s/^TLSProtocol                             TLSv1.2/TLSCipherSuite                          '"$tls12_ciphers"'\nTLSProtocol                             TLSv1.2 TLSv1.3\nTLSServerCipherPreference               on/;s/^#TLSOptions                                                     AllowClientRenegotiations/#TLSOptions                      AllowClientRenegotiations/;s/^TLSOptions                       NoSessionReuseRequired AllowClientRenegotiations/TLSOptions                      NoSessionReuseRequired AllowClientRenegotiations/' /etc/proftpd/tls.conf
	fi
fi

if [ "$FTP_SYSTEM" = "vsftpd" ]; then
	if grep -q "^ssl_ciphers=.\+$" /etc/vsftpd/vsftpd.conf 2> /dev/null; then
		sed -i 's/^ssl_ciphers=.\+$/ssl_ciphers='"$tls12_ciphers"'/' /etc/vsftpd/vsftpd.conf
	fi
fi

if [ "$WEB_SYSTEM" = "nginx" ] || [ "$PROXY_SYSTEM" = "nginx" ]; then
	# Little trick to bypass on my private fork :)
	if ! grep -q "quic_bpf" /etc/nginx/nginx.conf && ! grep -q "spdy_headers_comp" /etc/nginx/nginx.conf; then
		# Syncing "/etc/nginx/nginx.conf" with mainline, to fix the **** caused by formatter or forgetting to apply updates
		echo "[ * ] Syncing NGINX configuration with mainline..."

		trap 'rm -fr "$dir_for_compare" /etc/nginx/nginx.conf-staging' EXIT
		dir_for_compare="$(mktemp -d)"
		nginx_conf_local="$dir_for_compare"/nginx.conf-local
		nginx_conf_commit="$dir_for_compare"/nginx.conf-commit

		sed 's|https://www.cloudflare.com/||;/^[ \t]\+resolver .\+;$/d;/^[ \t]\+# Cache settings$/d;/^[ \t]\+# Proxy cache$/d' /etc/nginx/nginx.conf | sed ':l;N;$!bl;s/[ \n\t]*//g' > "$nginx_conf_local"

		# For installations before v1.6.8 (from commit 9b544be to commit b2ad154)
		curl -fsLm5 --retry 2 https://raw.githubusercontent.com/hestiacp/hestiacp/b2ad1549a21655837056e4b7883970d51a4b324f/install/deb/nginx/nginx.conf \
			| sed 's/fastcgi_buffers                 4 256k;/fastcgi_buffers                 8 256k;/g;s|/var/run/|/run/|g;/set_real_ip_from/d;/real_ip_header/d;s|# Cloudflare https://www.cloudflare.com/ips|# Cloudflare https://www.cloudflare.com/ips\n    include /etc/nginx/conf.d/cloudflare.inc;|g' \
			| sed 's|https://www.cloudflare.com/||;/^[ \t]\+resolver .\+;$/d;/^[ \t]\+# Cache settings$/d;/^[ \t]\+# Proxy cache$/d' | sed ':l;N;$!bl;s/[ \n\t]*//g' > "$nginx_conf_commit"-b2ad154

		# For installations after v1.6.8 but before v1.7.0 (from commit b2ad154 to commit 015b20a)
		curl -fsLm5 --retry 2 https://raw.githubusercontent.com/hestiacp/hestiacp/015b20ae1ffb82faaf58b41a5dc9ad1b078b785f/install/deb/nginx/nginx.conf \
			| sed 's|/var/run/|/run/|g;/set_real_ip_from/d;/real_ip_header/d;s|# Cloudflare https://www.cloudflare.com/ips|# Cloudflare https://www.cloudflare.com/ips\n    include /etc/nginx/conf.d/cloudflare.inc;|g' \
			| sed 's|https://www.cloudflare.com/||;/^[ \t]\+resolver .\+;$/d;/^[ \t]\+# Cache settings$/d;/^[ \t]\+# Proxy cache$/d' | sed ':l;N;$!bl;s/[ \n\t]*//g' > "$nginx_conf_commit"-015b20a

		# For installations after v1.7.0 (commit 555f892)
		curl -fsLm5 --retry 2 https://raw.githubusercontent.com/hestiacp/hestiacp/555f89243e54e02458586ae4f7999458cc9d33e9/install/deb/nginx/nginx.conf \
			| sed 's|https://www.cloudflare.com/||;/^[ \t]\+resolver .\+;$/d;/^[ \t]\+# Cache settings$/d;/^[ \t]\+# Proxy cache$/d' | sed ':l;N;$!bl;s/[ \n\t]*//g' > "$nginx_conf_commit"-555f892

		for commit in b2ad154 015b20a 555f892; do
			if cmp -s "$nginx_conf_local" "$nginx_conf_commit"-"$commit" 2> /dev/null; then
				nginx_conf_compare="same"
				cp -f "$HESTIA_INSTALL_DIR"/nginx/nginx.conf /etc/nginx
				break
			fi
		done

		if [ "$nginx_conf_compare" != "same" ]; then
			echo -e "[ ! ] Manual action required, please view:\n[ - ] $HESTIA_BACKUP/message.log"
			add_upgrade_message "Manual Action Required [IMPORTANT]\n\nTo enable the \"Enhanced and Optimized TLS\" feature, we must update the NGINX configuration file (/etc/nginx/nginx.conf).\n\nBut for unknown reason or you edited it, may not be fully apply all the changes in this upgrade.\n\nPlease follow the default configuration file to sync it:\n$HESTIA_INSTALL_DIR/nginx/nginx.conf\n\nBacked up configuration file:\n$HESTIA_BACKUP/conf/nginx/nginx.conf\n\nLearn more:\nhttps://github.com/hestiacp/hestiacp/pull/3555\n\n"
			"$BIN"/v-add-user-notification admin "IMPORTANT: Manual Action Required" '<p>To enable the "Enhanced and Optimized TLS" feature, we must update the NGINX configuration file at <code>/etc/nginx/nginx.conf</code>.</p><p>But for unknown reason or you edited it, may not be fully apply all the changes in this upgrade.</p><p>Please follow the default configuration file to sync it:<br><code>'"$HESTIA_INSTALL_DIR"'/nginx/nginx.conf</code></p><p>Backed up configuration file:<br><code>'"$HESTIA_BACKUP"'/conf/nginx/nginx.conf</code></p><p>Visit PR <a href="https://github.com/hestiacp/hestiacp/pull/3555" target="_blank">#3555</a> on GitHub to learn more.</p>'

			sed -i "s/""$(grep -m 1 "IMPORTANT: Manual Action Required" "$HESTIA"/data/users/admin/notifications.conf | awk '{print $1}')""/NID='1'/" "$HESTIA"/data/users/admin/notifications.conf

			cp -f /etc/nginx/nginx.conf /etc/nginx/nginx.conf-staging

			# Apply previously missed updates
			sed -i 's/fastcgi_buffers                 4 256k;/fastcgi_buffers                 8 256k;/;s|https://www.cloudflare.com/||;s/# Cache settings/# Proxy cache/' /etc/nginx/nginx.conf-staging

			# Formatting
			echo "" >> /etc/nginx/nginx.conf-staging
			sed -i '/^[ \t]*$/d;s/^        worker_connections  1024;/\tworker_connections 1024;/;s/^        use                 epoll;/\tuse                epoll;/;s/^        multi_accept        on;/\tmulti_accept       on;/;s/^        /\t\t/g;s/^    /\t/g;s/^# Worker config/\n# Worker config/;s/^http {/\nhttp {/;s/^\t# Cache bypass/\n\t# Cache bypass/;s/^\t# File cache (static assets)/\n\t# File cache (static assets)/;s/^user                    www-data;/user                 www-data;/;s/^worker_processes        auto;/worker_processes     auto;/;s/^worker_rlimit_nofile    65535;/worker_rlimit_nofile 65535;/;s|^error_log               /var/log/nginx/error.log;|error_log            /var/log/nginx/error.log;|;s|^pid                     /run/nginx.pid;|pid                  /run/nginx.pid;|;s|^include /etc/nginx/modules-enabled/\*.conf;|include              /etc/nginx/modules-enabled/\*.conf;|;s/log_not_found off;/log_not_found                   off;/;s/access_log off;/access_log                      off;/;s|include             /etc/nginx/mime.types;|include                         /etc/nginx/mime.types;|;s|default_type        application/octet-stream;|default_type                    application/octet-stream;|;s/default 0;/default              0;/;s/~SESS 1;/~SESS                1;/;s|include /etc/nginx/conf.d/|include                         /etc/nginx/conf.d/|g' /etc/nginx/nginx.conf-staging

			# Prepare for update
			sed -i '/proxy_bu/d;/proxy_temp/d;/log_format/d;/body_bytes_sent/d;/http_user_agent/d;/gzip/d;/application\/j/d;/application\/x/d;/ssl_/d;/resolver/d;/error_page/d;/\/var\/cache\/nginx/d;/max_size=/d;/_cache_key/d;/_ignore_headers/d;/_cache_use_stale/d;/_cache_valid/d;/_cache_methods/d;/add_header/d;/open_file_cache/d' /etc/nginx/nginx.conf-staging

			# Apply the update
			sed -i 's/client_max_body_size            256m;/client_max_body_size            1024m;/;s/keepalive_requests              100000;/keepalive_requests              10000;/;s/fastcgi_buffers                 8 256k;/fastcgi_buffers                 512 4k;/;s/proxy_pass_header               Set-Cookie;/proxy_pass_header               Set-Cookie;\n\tproxy_buffers                   256 4k;\n\tproxy_buffer_size               32k;\n\tproxy_busy_buffers_size         32k;\n\tproxy_temp_file_write_size      256k;/;s/# Log format/# Log format\n\tlog_format                      main '"'"'$remote_addr - $remote_user [$time_local] $request "$status" $body_bytes_sent "$http_referer" "$http_user_agent" "$http_x_forwarded_for"'"'"';\n\tlog_format                      bytes '"'"'$body_bytes_sent'"'"';/;s|# Compression|# Compression\n\tgzip                            on;\n\tgzip_vary                       on;\n\tgzip_static                     on;\n\tgzip_comp_level                 6;\n\tgzip_min_length                 1024;\n\tgzip_buffers                    128 4k;\n\tgzip_http_version               1.1;\n\tgzip_types                      text/css text/javascript text/js text/plain text/richtext text/shtml text/x-component text/x-java-source text/x-markdown text/x-script text/xml image/bmp image/svg+xml image/vnd.microsoft.icon image/x-icon font/otf font/ttf font/x-woff multipart/bag multipart/mixed application/eot application/font application/font-sfnt application/font-woff application/javascript application/javascript-binast application/json application/ld+json application/manifest+json application/opentype application/otf application/rss+xml application/ttf application/truetype application/vnd.api+json application/vnd.ms-fontobject application/wasm application/xhtml+xml application/xml application/xml+rss application/x-httpd-cgi application/x-javascript application/x-opentype application/x-otf application/x-perl application/x-protobuf application/x-ttf;\n\tgzip_proxied                    any;|;s/# Cloudflare ips/# Cloudflare IPs/;s|# SSL PCI compliance|# SSL PCI compliance\n\tssl_buffer_size                 1369;\n\tssl_ciphers                     "'"$tls12_ciphers"'";\n\tssl_dhparam                     /etc/ssl/dhparam.pem;\n\tssl_early_data                  on;\n\tssl_ecdh_curve                  auto;\n\tssl_prefer_server_ciphers       on;\n\tssl_protocols                   TLSv1.2 TLSv1.3;\n\tssl_session_cache               shared:SSL:20m;\n\tssl_session_tickets             on;\n\tssl_session_timeout             7d;\n\tresolver                        1.0.0.1 8.8.4.4 1.1.1.1 8.8.8.8 valid=300s ipv6=off;\n\tresolver_timeout                5s;|;s|# Error pages|# Error pages\n\terror_page                      403 /error/404.html;\n\terror_page                      404 /error/404.html;\n\terror_page                      410 /error/410.html;\n\terror_page                      500 501 502 503 504 505 /error/50x.html;|;s|# Proxy cache|# Proxy cache\n\tproxy_cache_path                /var/cache/nginx levels=2 keys_zone=cache:10m inactive=60m max_size=1024m;\n\tproxy_cache_key                 "$scheme$request_method$host$request_uri";\n\tproxy_temp_path                 /var/cache/nginx/temp;\n\tproxy_ignore_headers            Cache-Control Expires;\n\tproxy_cache_use_stale           error timeout invalid_header updating http_502;\n\tproxy_cache_valid               any 1d;|;s|# FastCGI cache|# FastCGI cache\n\tfastcgi_cache_path              /var/cache/nginx/micro levels=1:2 keys_zone=microcache:10m inactive=30m max_size=1024m;\n\tfastcgi_cache_key               "$scheme$request_method$host$request_uri";\n\tfastcgi_ignore_headers          Cache-Control Expires Set-Cookie;\n\tfastcgi_cache_use_stale         error timeout invalid_header updating http_500 http_503;\n\tadd_header                      X-FastCGI-Cache $upstream_cache_status;|;s/# File cache (static assets)/# File cache (static assets)\n\topen_file_cache                 max=10000 inactive=30s;\n\topen_file_cache_valid           60s;\n\topen_file_cache_min_uses        2;\n\topen_file_cache_errors          off;/' /etc/nginx/nginx.conf-staging

			# Apply the update for implement TLS 1.3 0-RTT anti-replay and upcoming HTTP/3 support
			sed -i '/\/etc\/nginx\/conf\.d\/main\/\*\.conf;/d;/pid                  \/run\/nginx.pid;/a include              /etc/nginx/conf.d/main/*.conf;' /etc/nginx/nginx.conf-staging
			sed -i '/Early-Data/d;/proxy_set_header                Host $host;/a \\tproxy_set_header                Early-Data $rfc_early_data;' /etc/nginx/nginx.conf-staging

			# Verify new configuration file
			if nginx -c /etc/nginx/nginx.conf-staging -t > /dev/null 2>&1; then
				mv -f /etc/nginx/nginx.conf-staging /etc/nginx/nginx.conf
			fi
		fi

		# Implement TLS 1.3 0-RTT anti-replay
		echo -e "[ * ] TLS 1.3 0-RTT anti-replay for NGINX, please view:\n[ - ] $HESTIA_BACKUP/message.log"
		add_upgrade_message "About TLS 1.3 0-RTT anti-replay for NGINX\n\nIf you use custom templates, please update them (*.stpl) to apply this protection.\n\nFollow the usage or other default templates:\n/etc/nginx/conf.d/0rtt-anti-replay.conf\n\nLearn more:\nhttps://github.com/hestiacp/hestiacp/pull/3692"
		"$BIN"/v-add-user-notification admin "About TLS 1.3 0-RTT anti-replay for NGINX" '<p>If you use custom templates, please update them (*.stpl) to apply this protection.</p><p>Follow the usage or other default templates:<br><code>/etc/nginx/conf.d/0rtt-anti-replay.conf</code></p><p>Visit PR <a href="https://github.com/hestiacp/hestiacp/pull/3692" target="_blank">#3692</a> on GitHub to learn more.</p>'

		if grep -qw "IMPORTANT: Manual Action Required" "$HESTIA"/data/users/admin/notifications.conf 2> /dev/null; then
			sed -i "s/""$(grep -m 1 "About TLS 1.3 0-RTT anti-replay for NGINX" "$HESTIA"/data/users/admin/notifications.conf | awk '{print $1}')""/NID='2'/" "$HESTIA"/data/users/admin/notifications.conf
		else
			sed -i "s/""$(grep -m 1 "About TLS 1.3 0-RTT anti-replay for NGINX" "$HESTIA"/data/users/admin/notifications.conf | awk '{print $1}')""/NID='1'/" "$HESTIA"/data/users/admin/notifications.conf
		fi

		cp -f "$HESTIA_INSTALL_DIR"/nginx/0rtt-anti-replay.conf /etc/nginx/conf.d

		# Update resolver for NGINX
		for nameserver in $(grep -is '^nameserver' /etc/resolv.conf | cut -d' ' -f2 | tr '\r\n' ' ' | xargs); do
			if [[ "$nameserver" =~ ^([0-9]{1,3}\.){3}[0-9]{1,3}$ ]]; then
				if [ -z "$resolver" ]; then
					resolver="$nameserver"
				else
					resolver="$resolver $nameserver"
				fi
			fi
		done

		if [ -n "$resolver" ]; then
			sed -i "s/1.0.0.1 8.8.4.4 1.1.1.1 8.8.8.8/$resolver/g" /etc/nginx/nginx.conf
		fi

		# Update some configuration files
		cp -f "$HESTIA_INSTALL_DIR"/nginx/phpmyadmin.inc "$HESTIA_INSTALL_DIR"/nginx/phppgadmin.inc "$HESTIA_INSTALL_DIR"/nginx/status.conf /etc/nginx/conf.d
		[ -n "$DB_PMA_ALIAS" ] && sed -i "s|%pma_alias%|$DB_PMA_ALIAS|g" /etc/nginx/conf.d/phpmyadmin.inc
		[ -n "$DB_PGA_ALIAS" ] && sed -i "s|%pga_alias%|$DB_PGA_ALIAS|g" /etc/nginx/conf.d/phppgadmin.inc

		# Prepare for upcoming HTTP/3 support, also convenient for users to add directive to "main" context
		mkdir -p /etc/nginx/conf.d/main
	fi
fi

unset commit nameserver nginx_conf_commit nginx_conf_compare nginx_conf_local os_release tls12_ciphers tls13_ciphers resolver
# Finish configuring the "Enhanced and Optimized TLS" feature

# Update IPs configuration file
# shellcheck source=/usr/local/hestia/func/domain.sh
source $HESTIA/func/domain.sh

if [ "$WEB_SYSTEM" = "nginx" ]; then
	while IFS= read -r IP; do
		ip_conf="/etc/nginx/conf.d/$IP.conf"
		cp -f "$HESTIA_INSTALL_DIR"/nginx/unassigned.inc "$ip_conf"
		sed -i "s/directIP/$IP/g" "$ip_conf"
		process_http2_directive "$ip_conf"
	done < <(ls "$HESTIA"/data/ips/ 2> /dev/null)
elif [ "$PROXY_SYSTEM" = "nginx" ]; then
	while IFS= read -r IP; do
		cat "$WEBTPL"/nginx/proxy_ip.tpl \
			| sed -e "s/%ip%/$IP/g" \
				-e "s/%web_port%/$WEB_PORT/g" \
				-e "s/%proxy_port%/$PROXY_PORT/g" \
				-e "s/%proxy_ssl_port%/$PROXY_SSL_PORT/g" \
				> "/etc/nginx/conf.d/$IP.conf"
		process_http2_directive "/etc/nginx/conf.d/$IP.conf"
	done < <(ls "$HESTIA"/data/ips/ 2> /dev/null)
fi

if [ "$MAIL_SYSTEM" = "exim4" ]; then
	exim_version=$(exim4 --version | head -1 | awk '{print $3}' | cut -f -2 -d .)
	# if Exim version > 4.95 or greater!
	if version_ge "$exim_version" "4.95"; then
		if ! grep -q 'SRS_SECRET' /etc/exim4/exim4.conf.template; then
			srs=$(generate_password)
			echo $srs > /etc/exim4/srs.conf
			chmod 640 /etc/exim4/srs.conf
			chown root:Debian-exim /etc/exim4/srs.conf
			cp /etc/exim4/exim4.conf.template /etc/exim4/exim4.conf.template.staging
			patch /etc/exim4/exim4.conf.template.staging $HESTIA/install/upgrade/patch/3661-exim-srs-support.patch 2>&1
			exim -C /etc/exim4/exim4.conf.template.staging 2>&1
			if [ "$?" -ne 0 ]; then
				add_upgrade_message "Unable to successfully aply the SRS update patch for Exim.\n If you use SMTP relay with the SRS feature use the exim config found in /usr/local/hestia/install/deb/exim/exim4.conf.4.95.template"
				"$BIN"/v-add-user-notification admin "Unable to apply patch to Exim config" 'Unable to successfully apply the SRS update patch for Exim.<br /> If you use SMTP relay with the SRS feature use the exim config found in /usr/local/hestia/install/deb/exim/exim4.conf.4.95.template'
				if grep -qw "IMPORTANT: Manual Action Required" "$HESTIA"/data/users/admin/notifications.conf 2> /dev/null; then
					sed -i "s/""$(grep -m 1 "Unable to apply patch to Exim config" "$HESTIA"/data/users/admin/notifications.conf | awk '{print $1}')""/NID='3'/" "$HESTIA"/data/users/admin/notifications.conf
				else
					sed -i "s/""$(grep -m 1 "Unable to apply patch to Exim config" "$HESTIA"/data/users/admin/notifications.conf | awk '{print $1}')""/NID='2'/" "$HESTIA"/data/users/admin/notifications.conf
				fi
				echo "[ ! ] Unable to apply SRS update patch for SMTP relay"
			else
				echo "[ * ] Update exim4.conf.template ..."
				cp /etc/exim4/exim4.conf.template.staging /etc/exim4/exim4.conf.template
			fi
			rm /etc/exim4/exim4.conf.template.staging*
		else
			sed -i "s/SRS_SECRET = readfile{\/etc\/exim4\/srs.conf}/SRS_SECRET = \${readfile{\/etc\/exim4\/srs.conf}}/g" /etc/exim4/exim4.conf.template
			chown root:Debian-exim /etc/exim4/srs.conf
		fi
	fi

	exim_version=$(exim4 --version | head -1 | awk '{print $3}' | cut -f -2 -d .)
	# if Exim version > 4.95 or greater!
	if version_ge "$exim_version" "4.95"; then
		if ! grep -q 'condition = ${lookup{$local_part@$domain}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/aliases}{false}{true}}' /etc/exim4/exim4.conf.template; then
			for line in $(sed -n '/redirect_router = dnslookup/=' /etc/exim4/exim4.conf.template); do
				testline=$((line - 1))
				newline=$((line + 1))
				if [ "$(awk NR==$testline /etc/exim4/exim4.conf.template)" = "  file_transport = local_delivery" ]; then
					# Add new line
					sed -i "$newline i \ \ condition = \${lookup{\$local_part@\$domain}lsearch{/etc/exim4/domains/\${lookup{\$domain}dsearch{/etc/exim4/domains/}}/aliases}{false}{true}}" /etc/exim4/exim4.conf.template
				fi
			done
		fi
	fi

fi
