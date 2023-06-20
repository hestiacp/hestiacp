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

	location ~ /(changelog.txt|copyright.txt|install.mysql.txt|install.pgsql.txt|install.sqlite.txt|install.txt|license.txt|maintainers.txt|license|license.txt|readme.txt|readme.md|upgrade.txt) {
		deny all;
		return 404;
	}

	location ~ ^/sites/.*/private/ {
		deny all;
		return 404;
	}

	location ~ /vendor/.*\.php$ {
		deny all;
		return 404;
	}

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	location / {
		try_files $uri $uri/ /index.php?$query_string;

		location ~* ^.+\.(ogg|ogv|svg|svgz|swf|eot|otf|woff|woff2|mov|mp3|mp4|webm|flv|ttf|rss|atom|jpg|jpeg|gif|png|webp|ico|bmp|mid|midi|wav|rtf|css|js|jar)$ {
			try_files $uri @rewrite;

			expires 30d;
			fastcgi_hide_header "Set-Cookie";
		}

		location ~ \..*/.*\.php$ {
			deny all;
			return 404;
		}

		location ~ ^/sites/[^/]+/files/.*\.php$ {
			deny all;
			return 404;
		}

		location ~ [^/]\.php(/|$)|^/update.php {
			try_files $uri =404;

			include /etc/nginx/fastcgi_params;

			fastcgi_index index.php;
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			fastcgi_param SCRIPT_FILENAME $request_filename;
			fastcgi_split_path_info ^(.+?\.php)(|/.*)$;

			fastcgi_pass %backend_lsnr%;

			include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;

			set $no_cache 0;

			if ($request_uri ~* "/user/|/admin/|index.php") {
				set $no_cache 1;
			}

			if ($http_cookie ~ SESS) {
				set $no_cache 1;
			}
		}

		location ~ ^/sites/.*/files/styles/ {
			try_files $uri @rewrite;
		}
	}

	location @rewrite {
		rewrite ^/(.*)$ /index.php?q=$1;
	}

	rewrite ^/index.php/(.*) /$1 permanent;

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
