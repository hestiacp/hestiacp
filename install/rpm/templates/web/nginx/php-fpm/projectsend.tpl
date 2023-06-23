#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://hestiacp.com/docs/server-administration/web-templates.html      #
#=========================================================================#

server {
	listen      %ip%:%web_port%;
	server_name %domain_idn% %alias_idn%;
	root        %docroot%;
	index       index.php index.html index.htm;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	access_log  /var/log/nginx/domains/%domain%.bytes bytes;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

	# Add headers to serve security related headers
	add_header X-Content-Type-Options nosniff;
	add_header X-XSS-Protection "1; mode=block";
	add_header X-Robots-Tag none;
	add_header X-Download-Options noopen;
	add_header X-Permitted-Cross-Domain-Policies none;
	add_header Referrer-Policy no-referrer;

	location = /robots.txt {
		allow all;
		log_not_found off;
		access_log off;
	}

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	client_max_body_size 512M;

	# Disable gzip to avoid the removal of the ETag header
	gzip off;

	# Uncomment if your server is build with the ngx_pagespeed module
	# This module is currently not supported.
	#pagespeed off;

	error_page 403 /core/templates/403.php;
	error_page 404 /core/templates/404.php;

	location / {
		try_files $uri $uri/ /index.php;
	}

	location ~ \.php$ {
		try_files $fastcgi_script_name =404;

		include /etc/nginx/fastcgi_params;

		fastcgi_intercept_errors on;
		fastcgi_param front_controller_active true;
		# Avoid sending the security headers twice
		fastcgi_param modHeadersAvailable true;
		fastcgi_param PATH_INFO $fastcgi_path_info;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_request_buffering off;
		fastcgi_split_path_info ^(.+\.php)(/.*)$;

		fastcgi_pass unix:/run/php/php7.4-fpm.sock;

	}

	location ~* \.(?:svg|gif|png|webp|html|ttf|woff|ico|jpg|jpeg)$ {
		try_files $uri /index.php$uri$is_args$args;
		# Optional: Don't log access to other assets
		access_log off;
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
