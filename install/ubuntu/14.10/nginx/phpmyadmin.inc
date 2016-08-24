location /phpmyadmin {
    alias /usr/share/phpmyadmin/;

    location ~ /(libraries|setup) {
        return 404;
    }

    location ~ ^/phpmyadmin/(.*\.php)$ {
        alias /usr/share/phpmyadmin/$1;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
    }
    location ~* ^/phpmyadmin/(.+\.(jpg|jpeg|gif|css|png|js|ico|html|xml|txt))$ {
        root /usr/share/;
    }
}
