#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://hestiacp.com/docs/server-administration/web-templates.html      #
#=========================================================================#

server {
	listen      %ip%:%web_port%;
	server_name %domain_idn% %alias_idn%;
	root        %docroot%/web;
	index       index.php index.html index.htm;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	access_log  /var/log/nginx/domains/%domain%.bytes bytes;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

	location = /favicon.ico {
		log_not_found off;
		access_log off;
	}

	location = /robots.txt {
		allow all;
		log_not_found off;
		access_log off;
	}

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	location / {
		# try to serve file directly, fallback to app.php
		try_files $uri /app.php$is_args$args;
	}

	# DEV
	# This rule should only be placed on your development environment
	# In production, don't include this and don't deploy app_dev.php or config.php
	location ~ ^/(app_dev|config)\.php(/|$) {
		include /etc/nginx/fastcgi_params;

		# When you are using symlinks to link the document root to the
		# current version of your application, you should pass the real
		# application path instead of the path to the symlink to PHP
		# FPM.
		# Otherwise, PHP's OPcache may not properly detect changes to
		# your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
		# for more information).
		fastcgi_param DOCUMENT_ROOT $realpath_root;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		fastcgi_split_path_info ^(.+\.php)(/.*)$;

		fastcgi_pass %backend_lsnr%;

		include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;
	}

	# PROD
	location ~ ^/app\.php(/|$) {
		include /etc/nginx/fastcgi_params;

		# When you are using symlinks to link the document root to the
		# current version of your application, you should pass the real
		# application path instead of the path to the symlink to PHP
		# FPM.
		# Otherwise, PHP's OPcache may not properly detect changes to
		# your PHP files (see https://github.com/zendtech/ZendOptimizerPlus/issues/126
		# for more information).
		fastcgi_param DOCUMENT_ROOT $realpath_root;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		fastcgi_split_path_info ^(.+\.php)(/.*)$;

		fastcgi_pass %backend_lsnr%;

		include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;

		# Prevents URIs that include the front controller. This will 404:
		# http://domain.tld/app.php/some-path
		# Remove the internal directive to allow URIs like this
		internal;
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
