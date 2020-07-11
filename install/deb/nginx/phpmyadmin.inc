location /%pma_alias% {
    alias /usr/share/phpmyadmin/;

    location ~ /(libraries|setup) {
        deny all;
        return 404;
    }

    location ~ ^/%pma_alias%/(.*\.php)$ {
        alias /usr/share/phpmyadmin/$1;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
    }
    location /%pma_alias%/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
        root /usr/share/phpmyadmin/;
    }
}
