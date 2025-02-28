server {
	listen %ip%:%proxy_port% default_server;
	server_name _;
	access_log off;
	error_log /dev/null;

	location / {
		proxy_pass http://%ip%:%web_port%;
   }
}

server {
	listen %ip%:%proxy_ssl_port% default_server ssl;
	server_name _;
	access_log off;
	error_log /dev/null;

	ssl_certificate     /usr/local/hestia/ssl/certificate.crt;
	ssl_certificate_key /usr/local/hestia/ssl/certificate.key;

	return 301 http://$host$request_uri;

	location / {
		root /var/www/document_errors/;
	}

	location /error/ {
		alias /var/www/document_errors/;
	}
}
