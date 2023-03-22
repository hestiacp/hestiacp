location /phppgadmin {
    alias /usr/share/phppgadmin/;

    location ~ ^/phppgadmin/(.*\.php)$ {
        alias /usr/share/phppgadmin/$1;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
    }
}
