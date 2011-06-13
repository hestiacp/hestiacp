server {
    listen      %ip%:80;
    server_name %domain_idn% %alias%;
    server_name_in_redirect off;
    #error_log  /var/log/httpd/domains/%domain%.error.log error;

    location / {
        proxy_pass     http://%ip%:%port%;

        location ~* ^.+\.(%extentions%)$ {
            root           %docroot%;
            access_log     /var/log/httpd/domains/%domain%.log combined;
            access_log     /var/log/httpd/domains/%domain%.bytes bytes;
            expires        max;
            try_files      $uri @fallback;
        }
    }

    location = /error/ {
        root    %error_docroot%;
        try_files      $uri @fallback;
    }

    location @fallback {
        proxy_pass      http://%ip%:%port%;
    }

    location ~ /\.ht   {deny all;}
    location ~ /.svn/  {deny all;}
}
