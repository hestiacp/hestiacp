#=======================================================================#
# Default Web Domain Template                                           #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS #
#=======================================================================#

server {
    listen      %ip%:%web_port%;
    server_name %domain_idn% %alias_idn%;
    root        %docroot%;
    index       index.php index.html index.htm;
    access_log  /var/log/nginx/domains/%domain%.log combined;
    access_log  /var/log/nginx/domains/%domain%.bytes bytes;
    error_log   /var/log/nginx/domains/%domain%.error.log error;
        
    include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }

    location ~* \.(txt|log)$ {
        allow 192.168.0.0/16;
        deny all;
    }

    location ~ \..*/.*\.php$ {
        return 403;
        }

    location ~ ^/sites/.*/private/ {
        return 403;
    }

    location ~ ^/sites/[^/]+/files/.*\.php$ {
        deny all;
    }

    location / {
        try_files $uri @rewrite;
    }

    location @rewrite {
        rewrite ^/(.*)$ /index.php?q=$1;
    }
    
    location ~ /vendor/.*\.php$ {
        deny all;
        return 404;
    }    

    location ~* ^.+\.(jpeg|jpg|png|gif|bmp|ico|svg|css|js)$ {
        try_files $uri @rewrite;
        expires max;
        log_not_found off;
    }

    location ~ ^/sites/.*/files/imagecache/ {
        try_files $uri @rewrite;
    }
        
    location ~ '\.php$|^/update.php' {
        fastcgi_split_path_info ^(.+?\.php)(|/.*)$;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass %backend_lsnr%;             
        include /etc/nginx/fastcgi_params;
    }

    location /error/ {
        alias   %home%/%user%/web/%domain%/document_errors/;
    }

    location ~* "/\.(htaccess|htpasswd)$" {
        deny    all;
        return  404;
    }

    location /vstats/ {
        alias   %home%/%user%/web/%domain%/stats/;
        include %home%/%user%/web/%domain%/stats/auth.conf*;
    }

    include     /etc/nginx/conf.d/phpmyadmin.inc*;
    include     /etc/nginx/conf.d/phppgadmin.inc*;
    include     %home%/%user%/conf/web/%domain%/nginx.conf_*;
}
