server {
    listen directIP:80 default;
    server_name _;
    location / {
        access_log /dev/null;
        error_log /dev/null;
        root /var/www/html;

        location /phpmyadmin/ {
            alias /var/www/document_errors/;
            return 404;
        }

        location /phppgadmin/ {
            alias /var/www/document_errors/;
            return 404;
        }

        location /webmail {
            alias /var/www/document_errors/;
            return 404;
        }
        
        location /webmail/ {
            alias /var/www/document_errors/;
            return 404;
        }

        location /error/ {
            alias /var/www/document_errors/;
        }
    }
}

server {
    listen directIP:443 ssl http2 default;
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
