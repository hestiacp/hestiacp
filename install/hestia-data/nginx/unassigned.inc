server {
    listen directIP:80 default;
    server_name _;
    location / {
        access_log /dev/null;
        error_log /dev/null;
        root /var/www/html;

        location /phpmyadmin/ {
            alias /var/www/document_errors/;
        }

        location /phppgadmin/ {
            alias /var/www/document_errors/;
        }

        location /webmail {
            alias /var/www/document_errors/;
        }
        location /error/ {
            alias /var/www/document_errors/;
        }
    }
}