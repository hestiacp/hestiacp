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

	location ~ ^/%pma_alias%/(?<pma_php>.*\.php)$ {
		alias         /usr/share/phpmyadmin/$pma_php;
		include       /etc/nginx/fastcgi_params;
		fastcgi_index index.php;
		fastcgi_param HTTP_EARLY_DATA $rfc_early_data if_not_empty;
		fastcgi_param SCRIPT_FILENAME $request_filename;
		fastcgi_pass  unix:/run/php/www.sock;
	}
}
