location /phppgadmin {
    alias /usr/share/phpPgAdmin/;

    location ~ ^/phppgadmin/(.*\.php)$ {
        alias /usr/share/phpPgAdmin/$1;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
    }
}
