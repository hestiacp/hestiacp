server {
%<i4    listen      %web_ipv4%:%web_port%;i4>%
%<i6    listen      %web_ipv6%:%web_port%;i6>%
    server_name %domain_idn% %alias_idn%;
    root        /var/www/html;
    index       index.php index.html index.htm;
    access_log /var/log/nginx/domains/%domain%.log combined;
    error_log  /var/log/nginx/domains/%domain%.error.log error;

    include %home%/%user%/conf/mail/%root_domain%/nginx.forcessl.conf*;

    location ~ /\.(?!well-known\/) {
        deny all;
        return 404;
    }


    location / {
        try_files $uri $uri/ =404;
    }

    location /error/ {
        alias /var/www/document_errors/;
    }

    include %home%/%user%/conf/mail/%root_domain%/%web_system%.conf_*;
}
