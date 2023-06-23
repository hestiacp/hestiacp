server {
	listen      %ip%:%proxy_port%;
	server_name %domain_idn% %alias_idn%;
	index       index.php index.html index.htm;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/mail/%root_domain%/nginx.forcessl.conf*;

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	location / {
		proxy_pass http://%ip%:%web_port%;
	}

	include %home%/%user%/conf/mail/%root_domain%/%proxy_system%.conf_*;
}
