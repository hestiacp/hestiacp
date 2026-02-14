#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://hestiacp.com/docs/server-administration/web-templates.html      #
#=========================================================================#

server {
	listen      %ip%:%web_port%;
	server_name %domain_idn% %alias_idn%;
	root        %docroot%/public;
	index       index.php index.html index.htm;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	access_log  /var/log/nginx/domains/%domain%.bytes bytes;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

	set $skip_cache 0;
	set $cache_uri $request_uri;

	if ($query_string != "") {
		set $skip_cache 1;
		set $cache_uri null;
		set $no_cache 1;
	}

	# Don't cache uris containing the following segments
	if ($request_uri ~* "/admin/|/user/|/cart/|/feed/|index.php|sitemap(_index)?.xml") {
		set $skip_cache 1;
		set $cache_uri null;
		set $no_cache 1;
	}  

	# Don't use the cache for logged in users or if products in cart
	if ($http_cookie ~* "nocache|user|cart") {
		set $skip_cache 1;
		set $cache_uri null;
		set $no_cache 1;
	}

	# POST requests and urls with a query string should always go to PHP
	if ($request_method = POST) {
		set $skip_cache 1;
		set $cache_uri null;
		set $no_cache 1;
	}

	location = /favicon.ico {
		log_not_found off;
		access_log off;
	}

	location = /robots.txt {
		try_files $uri $uri/ /index.php?$args;
		log_not_found off;
		access_log off;
	}

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	location / {
		default_type  text/html;
		index index.php index.html index.htm;
		try_files /page-cache/$host$cache_uri /page-cache/$host$cache_uri/index.html $uri $uri/ /index.php$is_args$args;

		location ~* ^.+\.(ogg|ogv|svg|svgz|swf|eot|otf|woff|woff2|mov|mp3|mp4|webm|flv|ttf|rss|atom|jpg|jpeg|gif|png|webp|ico|bmp|mid|midi|wav|rtf|css|js|jar)$ {
			expires 30d;
			fastcgi_hide_header "Set-Cookie";
		}

		location ~ /media/.+?\.php$ {
			deny all;
			return 404;
		}

		location ~ [^/]\.php(/|$) {
			try_files $uri =404;

			include /etc/nginx/fastcgi_params;

			fastcgi_index index.php;
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

			fastcgi_pass %backend_lsnr%;
		}
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
