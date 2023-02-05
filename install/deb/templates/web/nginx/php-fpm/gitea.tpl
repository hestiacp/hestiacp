#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://docs.hestiacp.com/admin_docs/web.html#how-do-web-templates-work #
#=========================================================================#

server {
    listen      %ip%:%web_port%;
    server_name %domain_idn% %alias_idn%;
    root        %docroot%;
    index       index.php index.html index.htm;
    access_log  /var/log/nginx/domains/%domain%.log combined;
    access_log  /var/log/nginx/domains/%domain%.bytes bytes;
    error_log   /var/log/nginx/domains/%domain%.error.log error;

    include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

    location / {
        proxy_pass http://localhost:3000;
    }

    location /error/ {
        alias   %home%/%user%/web/%domain%/document_errors/;
    }

    location ~ /\.(?!well-known\/) {
       deny all;
       return 404;
    }

    location /vstats/ {
        alias   %home%/%user%/web/%domain%/stats/;
        include %home%/%user%/web/%domain%/stats/auth.conf*;
    }

    include     /etc/nginx/conf.d/phpmyadmin.inc*;
    include     /etc/nginx/conf.d/phppgadmin.inc*;
    include     %home%/%user%/conf/web/%domain%/nginx.conf_*;
}
