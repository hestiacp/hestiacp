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
		try_files $uri/index.html $uri $uri/ /index.php?$query_string;
	}

	# Craft-specific location handlers to ensure AdminCP requests route through index.php
	# If you change your `cpTrigger`, change it here as well
	location ^~ /admin {
		try_files $uri $uri/ /index.php?$query_string;
	}
	location ^~ /cpresources {
		try_files $uri $uri/ /index.php?$query_string;
	}

	# php-fpm configuration
	location ~ [^/]\.php(/|$) {
		try_files $uri $uri/ /index.php?$query_string;
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass %backend_lsnr%;
		fastcgi_index index.php;
		include fastcgi_params;
		fastcgi_param PATH_INFO $fastcgi_path_info;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		fastcgi_param DOCUMENT_ROOT $realpath_root;
		fastcgi_param HTTP_PROXY "";
		fastcgi_param HTTP_HOST %domain%;

		# Don't allow browser caching of dynamically generated content
		add_header Last-Modified $date_gmt;
		add_header Cache-Control "no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0";
		if_modified_since off;
		expires off;
		etag off;
		# Load security.conf from nginx-partials again, because add_header used in this location
		# block removes any already added headers https://nginx.org/en/docs/http/ngx_http_headers_module.html
		include /etc/nginx/snippets/security.conf;

		fastcgi_intercept_errors off;
		fastcgi_buffer_size 16k;
		fastcgi_buffers 4 16k;
		fastcgi_connect_timeout 300;
		fastcgi_send_timeout 300;
		fastcgi_read_timeout 300;
	}

	proxy_hide_header Upgrade;

	location /error/ {
		alias   %home%/%user%/web/%domain%/document_errors/;
	}

	location /vstats/ {
		alias   %home%/%user%/web/%domain%/stats/;
		include %home%/%user%/web/%domain%/stats/auth.conf*;
	}

	proxy_hide_header Upgrade;

	include /etc/nginx/conf.d/phpmyadmin.inc*;
	include /etc/nginx/conf.d/phppgadmin.inc*;
	include %home%/%user%/conf/web/%domain%/nginx.conf_*;
}
