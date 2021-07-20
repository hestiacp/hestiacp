server {
    listen      %ip%:%proxy_port%;
    server_name %domain_idn% %alias_idn%;
    root        /var/www/html;
    index       index.php index.html index.htm;
    access_log /var/log/nginx/domains/%domain%.log combined;
    error_log  /var/log/nginx/domains/%domain%.error.log error;
    
    ssl_stapling on;
    ssl_stapling_verify on;
    
    location ~ /\.(?!well-known\/) {
        deny all;
        return 404;
    }
    
    location / {
        try_files $uri $uri/ =404;
        alias /var/www/html;
    }
    
    location /error/ {
        alias /var/www/document_errors/;
    }
    
    location @fallback {
        proxy_pass https://%ip%:%web_ssl_port%;
    }
    
    include %home%/%user%/conf/mail/%root_domain%/%proxy_system%.conf_*;
}
