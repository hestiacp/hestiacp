server {
    listen directIP:80 default;
    server_name _;
    location / {
        access_log /dev/null;
        error_log /dev/null;
        root /var/www/html;

        location /phpmyadmin/ {
        proxy_pass  http://directIP:8080;
        }

        location /phppgadmin/ {
        proxy_pass  http://directIP:8080;
        }

        location /error/ {
        alias /var/www/document_errors/;
        }
    }
}