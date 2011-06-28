server {
    listen      %ip%:%proxy_port%;
    server_name %domain_idn% %alias_idn%;
    server_name_in_redirect off;
   %elog%error_log  /var/log/httpd/domains/%domain%.error.log error;

    location / {
        proxy_pass     http://%ip%:%web_port%;

        location ~* ^.+\.(%extentions%)$ {
            root           %docroot%;
            access_log     /var/log/httpd/domains/%domain%.log combined;
            access_log     /var/log/httpd/domains/%domain%.bytes bytes;
            expires        max;
            try_files      $uri @fallback;
        }
    }

    location = /error/ {
        root    %home%/%user%/domains/%domain%/document_errors/;
        try_files      $uri @fallback;
    }

    location @fallback {
        proxy_pass      http://%ip%:%web_port%;
    }

    location ~ /\.ht   {deny all;}
    location ~ /.svn/  {deny all;}

    include %home%/%user%/conf/%domain%.nginx.*;
}

