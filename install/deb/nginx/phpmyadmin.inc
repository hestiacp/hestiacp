location /%pma_alias% {
	alias /usr/share/phpmyadmin/;

	location ~ /(libraries|setup|templates|locale) {
		deny   all;
		return 404;
	}

	location ~ /(.+\.(json|lock|md)) {
		deny   all;
		return 404;
	}

	location ~ ^/%pma_alias%/(.*\.php)$ {
		alias         /usr/share/phpmyadmin/$1;
		include       /etc/nginx/fastcgi_params;
		fastcgi_index index.php;
		fastcgi_param HTTP_EARLY_DATA $rfc_early_data if_not_empty;
		fastcgi_param SCRIPT_FILENAME $request_filename;
		fastcgi_pass  127.0.0.1:9000;
	}

	location /%pma_alias%/(.+\.(jpg|jpeg|gif|css|png|webp|js|ico|html|xml|txt))$ {
		root /usr/share/phpmyadmin/;
	}
}