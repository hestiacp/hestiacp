#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://hestiacp.com/docs/server-administration/web-templates.html      #
#=========================================================================#

server {
	listen      %ip%:%web_port%;
	server_name %domain_idn% %alias_idn%;

	client_max_body_size 128M;

	root        %docroot%/public;
	index       index.php index.html index.htm;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	access_log  /var/log/nginx/domains/%domain%.bytes bytes;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

	# Shopware install / update
	location /shopware-installer.phar.php {
		try_files $uri /shopware-installer.phar.php$is_args$args;
	}

	location ~ ^/shopware-installer\.phar\.php/.+\.(?:css|js|png|svg|woff)$ {
		try_files $uri /shopware-installer.phar.php$is_args$args;
	}

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	# Deny access to .php files in public directories
	location ~ ^/(media|thumbnail|theme|bundles|sitemap).*\.php$ {
		deny all;
	}

	location ~ ^/(theme|media|thumbnail|bundles|css|fonts|js|recovery|sitemap)/ {
		expires 1y;
		add_header Cache-Control "public, must-revalidate, proxy-revalidate";
		log_not_found off;
		tcp_nodelay off;
		open_file_cache max=3000 inactive=120s;
		open_file_cache_valid 45s;
		open_file_cache_min_uses 2;
		open_file_cache_errors off;

		location ~* ^.+\.svg {
			add_header Content-Security-Policy "script-src 'none'";
			add_header Cache-Control "public, must-revalidate, proxy-revalidate";
			log_not_found off;
		}
	}

	location ~* ^.+\.(?:css|cur|js|jpe?g|gif|ico|png|svg|webp|html|woff|woff2|xml)$ {
		expires 1y;
		add_header Cache-Control "public, must-revalidate, proxy-revalidate";
		fastcgi_hide_header "Set-Cookie";

		access_log off;

		# The directive enables or disables messages in error_log about files not found on disk.
		log_not_found off;

		tcp_nodelay off;

		## Set the OS file cache.
		open_file_cache max=3000 inactive=120s;
		open_file_cache_valid 45s;
		open_file_cache_min_uses 2;
		open_file_cache_errors off;

		try_files $uri /index.php$is_args$args;
	}

	location ~* ^.+\.svg$ {
		add_header Content-Security-Policy "script-src 'none'";
	}

	location / {
		try_files $uri /index.php$is_args$args;
	}

	location ~ [^/]\.php(/|$) {
		try_files $uri =404;
		fastcgi_split_path_info ^(.+\.php)(/.+)$;

		include /etc/nginx/fastcgi_params;

		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_param HTTP_PROXY "";
		fastcgi_buffers 8 16k;
		fastcgi_buffer_size 32k;
		proxy_connect_timeout 300s;
		proxy_send_timeout 300s;
		proxy_read_timeout 300s;
		send_timeout 300s;
		client_body_buffer_size 128k;

		fastcgi_pass %backend_lsnr%;

		include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;
	}

	location /error/ {
		alias %home%/%user%/web/%domain%/document_errors/;
	}

	location /vstats/ {
		alias   %home%/%user%/web/%domain%/stats/;
		include %home%/%user%/web/%domain%/stats/auth.conf*;
	}

	include /etc/nginx/conf.d/phpmyadmin.inc*;
	include /etc/nginx/conf.d/phppgadmin.inc*;
	include %home%/%user%/conf/web/%domain%/nginx.conf_*;
}