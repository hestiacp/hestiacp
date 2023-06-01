#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://hestiacp.com/docs/server-administration/web-templates.html      #
#=========================================================================#

server {
	listen      %ip%:%web_port%;
	server_name %domain_idn% %alias_idn%;
	root        %docroot%;
	index       index.html;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	access_log  /var/log/nginx/domains/%domain%.bytes bytes;
	error_log   /var/log/nginx/domains/%domain%.error.log error;
	include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

	location / {
		location ~* ^.+\.(jpeg|jpg|png|webp|gif|bmp|ico|svg|css|js)$ {
			expires     max;
			fastcgi_hide_header "Set-Cookie";
		}

		try_files $uri /index.html;
	}

	location /error/ {
		alias   %home%/%user%/web/%domain%/document_errors/;
	}

	location ~ /\.(?!well-known\/|file) {
	   deny all;
	   return 404;
	}

	include %home%/%user%/conf/web/%domain%/nginx.conf_*;
}
