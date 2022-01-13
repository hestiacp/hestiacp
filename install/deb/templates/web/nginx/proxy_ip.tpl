#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://docs.hestiacp.com/admin_docs/web.html#how-do-web-templates-work #
#=========================================================================#

server {
    listen       %ip%:%proxy_port% default;
    server_name  _;
    #access_log  /var/log/nginx/%ip%.log main;
    location / {
        proxy_pass  http://%ip%:%web_port%;
   }
}

server {
    listen      %ip%:%proxy_ssl_port% ssl http2;
    server_name _;
    ssl_certificate      /usr/local/hestia/ssl/certificate.crt;
    ssl_certificate_key  /usr/local/hestia/ssl/certificate.key;

    return 301 http://$host$request_uri;

    location / {
        root /var/www/document_errors/;
    }

    location /error/ {
        alias /var/www/document_errors/;
    }
}
