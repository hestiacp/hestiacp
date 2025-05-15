server {
	%<i4    listen      %proxy_ipv4%:%proxy_port%;i4>%
	%<i6    listen      %proxy_ipv6%:%proxy_port%;i6>%
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
		proxy_pass http://%web_ip%:%web_port%;
	}

	include %home%/%user%/conf/mail/%root_domain%/%proxy_system%.conf_*;
}
