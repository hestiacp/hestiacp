location /%pga_alias% {
	alias /usr/share/phppgadmin/;

	location ~ ^/%pga_alias%/(.*\.php)$ {
		alias         /usr/share/phppgadmin/$1;
		include       /etc/nginx/fastcgi_params;
		fastcgi_index index.php;
		fastcgi_param HTTP_EARLY_DATA $rfc_early_data if_not_empty;
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_pass  unix:/run/php/www.sock;
	}
}