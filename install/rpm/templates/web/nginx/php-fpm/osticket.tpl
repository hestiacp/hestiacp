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

	set $path_info "";

	location ~ /include {
		deny all;
		return 403;
	}

	if ($request_uri ~ "^/api(/[^\?]+)") {
		set $path_info $1;
	}

	location ~ ^/api/(?:tickets|tasks).*$ {
		try_files $uri $uri/ /api/http.php?$query_string;
	}

	if ($request_uri ~ "^/scp/.*\.php(/[^\?]+)") {
		set $path_info $1;
	}

	if ($request_uri ~ "^/.*\.php(/[^\?]+)") {
		set $path_info $1;
	}

	location ~ ^/scp/ajax.php/.*$ {
		try_files $uri $uri/ /scp/ajax.php?$query_string;
	}

	location ~ ^/ajax.php/.*$ {
		try_files $uri $uri/ /ajax.php?$query_string;
	}

	location / {
		try_files $uri $uri/ index.php;
	}

	location ~ \.php$ {
		include /etc/nginx/fastcgi_params;

		fastcgi_index index.php;
		fastcgi_param PATH_INFO $path_info;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

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
