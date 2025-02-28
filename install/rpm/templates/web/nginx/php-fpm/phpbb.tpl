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

	# Based on: https://github.com/phpbb/phpbb/blob/master/phpBB/docs/nginx.sample.conf
	location / {
		try_files $uri $uri/ @rewriteapp;

		location ~* ^.+\.(ogg|ogv|svg|svgz|swf|eot|otf|woff|woff2|mov|mp3|mp4|webm|flv|ttf|rss|atom|jpg|jpeg|gif|png|webp|ico|bmp|mid|midi|wav|rtf|css|js|jar)$ {
			fastcgi_hide_header "Set-Cookie";
		}

		# Pass the php scripts to FastCGI server specified in upstream declaration.
		location ~ \.php(/|$) {
			include         /etc/nginx/fastcgi_params;

			fastcgi_index index.php;
			fastcgi_split_path_info ^(.+\.php)(/.*)$;
			fastcgi_param PATH_INFO $fastcgi_path_info;
			fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;

			fastcgi_pass %backend_lsnr%;
			include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;

			try_files $uri $uri/ /app.php$is_args$args;
		}

		# Deny access to internal phpbb files.
		location ~ /(config\.php|common\.php|cache|files|images/avatars/upload|includes|(?<!ext/)phpbb(?!\w+)|store|vendor) {
			deny all;
			return  404;
		}
	}

	location @rewriteapp {
		rewrite ^(.*)$ /app.php/$1 last;
	}

	# Correctly pass scripts for installer
	location /install/ {
		try_files $uri $uri/ @rewrite_installapp =404;

		# Pass the php scripts to fastcgi server specified in upstream declaration.
		location ~ \.php(/|$) {
			include         /etc/nginx/fastcgi_params;

			fastcgi_index index.php;
			fastcgi_split_path_info ^(.+\.php)(/.*)$;
			fastcgi_param PATH_INFO $fastcgi_path_info;
			fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;

			fastcgi_pass %backend_lsnr%;
			include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;

			try_files $uri $uri/ /install/app.php$is_args$args =404;
		}
	}

	location @rewrite_installapp {
		rewrite ^(.*)$ /install/app.php/$1 last;
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
