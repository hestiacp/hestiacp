server {
	listen      %ip%:%proxy_port%;
	server_name %domain_idn% %alias_idn%;
	root        /var/lib/roundcube/public_html;
	index       index.php index.html index.htm;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/mail/%root_domain%/nginx.forcessl.conf*;

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	location ~ ^/(README.md|config|temp|logs|bin|SQL|INSTALL|LICENSE|CHANGELOG|UPGRADING)$ {
		deny all;
		return 404;
	}

	location / {
                proxy_set_header Host $host;
                proxy_set_header X-Real-IP $remote_addr;
                proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
                proxy_set_header X-Forwarded-Proto $scheme;
                proxy_set_header SCRIPT_NAME "";
                proxy_set_header PATH_INFO $request_uri;
		proxy_pass http://%ip%:%web_port%;
	}

	location @fallback {
		proxy_pass http://%ip%:%web_port%;
	}

	location /error/ {
		alias /var/www/document_errors/;
	}

	include %home%/%user%/conf/mail/%root_domain%/%proxy_system%.conf_*;
}
