server {
	%<i4    listen      %web_ipv4%:%web_port%;i4>%
	%<i6    listen      %web_ipv6%:%web_port%;i6>%
	server_name %domain_idn% %alias_idn%;
	root        /var/lib/snappymail;
	index       index.php index.html index.htm;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/mail/%root_domain%/nginx.forcessl.conf*;

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	location ^~ /data {
		deny all;
		return 404;
	}

	location ~ ^/(README.md|config|temp|logs|bin|SQL|INSTALL|LICENSE|CHANGELOG|UPGRADING)$ {
		deny all;
		return 404;
	}

	location / {
		try_files $uri $uri/ =404;

		location ~* ^.+\.(ogg|ogv|svg|svgz|swf|eot|otf|woff|woff2|mov|mp3|mp4|webm|flv|ttf|rss|atom|jpg|jpeg|gif|png|webp|ico|bmp|mid|midi|wav|rtf|css|js|jar)$ {
			expires 7d;
			fastcgi_hide_header "Set-Cookie";
		}

		location ~ ^/(.*\.php)$ {
			include /etc/nginx/fastcgi_params;

			fastcgi_index index.php;
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;

			fastcgi_pass unix:/run/php/www.sock;
		}
	}

	location /error/ {
		alias /var/www/document_errors/;
	}

	include %home%/%user%/conf/mail/%root_domain%/%web_system%.conf_*;
}
