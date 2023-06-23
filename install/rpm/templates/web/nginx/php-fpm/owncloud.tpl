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

	rewrite ^/caldav(.*)$ /remote.php/caldav$1 redirect;
	rewrite ^/carddav(.*)$ /remote.php/carddav$1 redirect;
	rewrite ^/webdav(.*)$ /remote.php/webdav$1 redirect;

	error_page 403 = /core/templates/403.php;
	error_page 404 = /core/templates/404.php;
	error_page 500 502 503 504 /error/50x.html;

	location ~ ^/(?:\data|config|db_structure\.xml|README){
		deny all;
	}

	location ~ /\.(?!well-known\/|file) {
		deny all;
		return 404;
	}

	location / {
		# The following 2 rules are only needed with webfinger
		rewrite ^/.well-known/host-meta /public.php?service=host-meta last;
		rewrite ^/.well-known/host-meta.json /public.php?service=host-meta-json last;
		rewrite ^/.well-known/carddav /remote.php/carddav/ redirect;
		rewrite ^/.well-known/caldav /remote.php/caldav/ redirect;
		rewrite ^(/core/doc/[^\/]+/)$ $1/index.html;

		try_files $uri $uri/ /index.php;

		location ~ \.php(?:$|/) {
			include /etc/nginx/fastcgi_params;

			#fastcgi_param HTTPS on;
			fastcgi_param PATH_INFO $fastcgi_path_info;
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			fastcgi_split_path_info ^(.+\.php)(/.+)$;

			fastcgi_pass %backend_lsnr%;

			include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;
		}
	}

	location ~* ^.+\.(jpeg|jpg|png|webp|gif|bmp|ico|svg|css|js)$ {
		expires max;
		fastcgi_hide_header "Set-Cookie";

		# Some basic cache-control for static files to be sent to the browser
		add_header Pragma public;
		add_header Cache-Control "public, must-revalidate, proxy-revalidate";
	}

	location /error/ {
		alias %home%/%user%/web/%domain%/document_errors/;
	}

	location ^~ /vstats/ {
		alias   %home%/%user%/web/%domain%/stats/;
		include %home%/%user%/web/%domain%/stats/auth.conf*;
	}

	include /etc/nginx/conf.d/phpmyadmin.inc*;
	include /etc/nginx/conf.d/phppgadmin.inc*;
	include %home%/%user%/conf/web/%domain%/nginx.conf_*;
}
