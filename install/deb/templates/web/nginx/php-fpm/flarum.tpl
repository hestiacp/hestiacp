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

	# Pass requests that don't refer directly to files in the filesystem to index.php
	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		try_files $uri =404;

		include /etc/nginx/fastcgi_params;

		fastcgi_index index.php;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

		fastcgi_pass %backend_lsnr%;

		include %home%/%user%/conf/web/%domain%/nginx.fastcgi_cache.conf*;
	}

	# Uncomment the following lines if you are not using a "public" directory
	# to prevent sensitive resources from being exposed.
	location ~* ^/(\.git|composer\.(json|lock)|auth\.json|config\.php|flarum|storage|vendor) {
		deny all;
		return 404;
	}

	# The following directives are based on best practices from H5BP Nginx Server Configs
	# https://github.com/h5bp/server-configs-nginx

	# Expire rules for static content
	location ~* \.(?:manifest|appcache|html?|xml|json)$ {
		add_header Cache-Control "max-age=0";
	}

	location ~* \.(?:rss|atom)$ {
		add_header Cache-Control "max-age=3600";
	}

	location ~* \.(?:jpg|jpeg|gif|png|ico|cur|gz|svg|mp4|ogg|ogv|webm|htc)$ {
		add_header Cache-Control "max-age=2592000";
		access_log off;
	}

	location ~* \.(?:css|js)$ {
		add_header Cache-Control "max-age=31536000";
		access_log off;
	}

	location ~* \.(?:ttf|ttc|otf|eot|woff|woff2)$ {
		add_header Cache-Control "max-age=2592000";
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
