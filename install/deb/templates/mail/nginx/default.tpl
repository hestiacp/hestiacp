server {
    listen      %ip%:%proxy_port%;
    server_name %domain% %alias%;
    root        /var/lib/roundcube;
    index       index.php;

    include %home%/%user%/conf/mail/%root_domain%/nginx.forcessl.conf*;
    
    location ~ /(config|temp|logs) {
        deny all;
        return 404;
    }
    
    location ~ /\.(?!well-known\/) {
        deny all;
        return 404;
    }

    location / {
        proxy_pass http://%ip%:%web_port%;
        location ~* ^.+\.(ogg|ogv|svg|svgz|swf|eot|otf|woff|mov|mp3|mp4|webm|flv|ttf|rss|atom|jpg|jpeg|gif|png|ico|bmp|mid|midi|wav|rtf|css|js|jar)$ {
            alias /var/lib/roundcube/;
            expires 1h;
            try_files $uri @fallback;
        }
    }

    location ~ ^/(.*\.php)$ {
        alias /var/lib/roundcube/$1;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
    }
    
    location @fallback {
        proxy_pass http://%ip%:%web_port%;
    }

    error_page 403 /error/404.html;
    error_page 404 /error/404.html;
    error_page 500 502 503 504 /error/50x.html;
    
    location /error/ {
        alias   %home%/%user%/web/%root_domain%/document_errors/;
    }

    include %home%/%user%/conf/mail/%root_domain%/%proxy_system%.conf_*;
}
