server {
    listen      %ip%:%proxy_port%;
    server_name %domain% %alias%;
    root        /var/lib/roundcube;
    index       index.php index.html index.htm;

    error_log /var/log/nginx/domains/%domain%.error.log;
    access_log /var/log/nginx/domains/%domain%.access.log;

    include %home%/%user%/conf/mail/%root_domain%/nginx.forcessl.conf*;

    location / {
        proxy_pass http://%ip%:%web_port%;
        try_files $uri $uri/ /index.php?q=$uri&$args;
        alias /var/lib/roundcube/;
        location ~* ^.+\.(ogg|ogv|svg|svgz|swf|eot|otf|woff|mov|mp3|mp4|webm|flv|ttf|rss|atom|jpg|jpeg|gif|png|ico|bmp|mid|midi|wav|rtf|css|js|jar)$ {
            expires 7d;
            fastcgi_hide_header "Set-Cookie";
        }
    }

    location ~ /(config|temp|logs) {
        deny all;
        return 404;
    }
    
    location ~ /\.(?!well-known\/) {
        deny all;
        return 404;
    }

    location ~ ^/(README.md|INSTALL|LICENSE|CHANGELOG|UPGRADING)$ {
        deny all;
        return 404;
    }

    location ~ ^/(bin|SQL)/ {
        deny all;
        return 404;
    }

    location ~ /\. {
        return 404;
        deny all;
        access_log off;
        log_not_found off;
    }

    location ~ ^/(.*\.php)$ {
        alias /var/lib/roundcube/$1;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $request_filename;
    }
    
    error_page 403 /error/404.html;
    error_page 404 /error/404.html;
    error_page 500 502 503 504 505 /error/50x.html;

    location /error/ {
        root        /var/www/document_errors/;
        try_files   $uri $uri/;
    }

    location @fallback {
        proxy_pass http://%ip%:%web_port%;
    }

    include %home%/%user%/conf/mail/%root_domain%/%proxy_system%.conf_*;
}
