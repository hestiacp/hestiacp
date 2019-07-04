location /webmail {
    alias /var/lib/roundcube/;

    location ~ /(README.md|config|temp|logs|bin|SQL|INSTALL|LICENSE|CHANGELOG|UPGRADING) {
        deny all;
        return 404;
    }

    location ~ ^/webmail/(.*\.php)$ {
        alias /var/lib/roundcube/$1;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
    }
}
