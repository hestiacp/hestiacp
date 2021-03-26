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
#   if you need to rewrite www to non-www uncomment bellow
#   if ($host != '%domain%' ) {
#       rewrite      ^/(.*)$  http://%domain%/$1  permanent;
#    }
    location = /favicon.ico {
        log_not_found off;
        access_log off;
    }

    location = /robots.txt {
        allow all;
        log_not_found off;
        access_log off;
    }

    location / {
        try_files $uri $uri/ @rewrite;
        location ~* ^.+\.(jpeg|jpg|png|gif|bmp|ico|svg|css|js)$ {
            expires     max;
            fastcgi_hide_header "Set-Cookie";
        }
    }
    location @rewrite {
        rewrite ^/(.*)$ /index.php?q=$1;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass %backend_lsnr%;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $request_filename;
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
