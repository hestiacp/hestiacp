#=========================================================================#
# Web Domain Application Template                                         #
# Proxies the domain to the application managed by v-add-web-domain-app  #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://hestiacp.com/docs/server-administration/web-templates.html      #
#=========================================================================#

# The proxy keeps connections to the application open and reuses them
upstream hestia_app_%domain_idn% {
	server    127.0.0.1:%app_port%;
	keepalive 32;
}

server {
	listen      %ip%:%web_port%;
	server_name %domain_idn% %alias_idn%;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	access_log  /var/log/nginx/domains/%domain%.bytes bytes;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

	location ~ /\.(?!well-known\/) {
		deny all;
		return 404;
	}

	location / {
		proxy_pass         http://hestia_app_%domain_idn%;
		proxy_http_version 1.1;

		# Any proxy_set_header here replaces the global ones from nginx.conf
		proxy_set_header Host              $host;
		proxy_set_header Early-Data        $rfc_early_data;
		proxy_set_header X-Real-IP         $remote_addr;
		proxy_set_header X-Forwarded-For   $proxy_add_x_forwarded_for;
		proxy_set_header X-Forwarded-Proto $scheme;
		proxy_set_header X-Forwarded-Host  $host;

		# WebSocket support
		proxy_set_header Upgrade           $http_upgrade;
		proxy_set_header Connection        $http_connection;

		proxy_read_timeout 300s;
		proxy_send_timeout 300s;
	}

	location /error/ {
		alias %home%/%user%/web/%domain%/document_errors/;
	}

	location /vstats/ {
		alias   %home%/%user%/web/%domain%/stats/;
		include %home%/%user%/web/%domain%/stats/auth.conf*;
	}

	include %home%/%user%/conf/web/%domain%/nginx.conf_*;
}
