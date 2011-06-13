server {
    listen       %ip%:%proxy_port% default;
    server_name  _;
    server_name_in_redirect  off;
    #access_log /var/log/nginx/%ip%.log main;

    location / {
        proxy_pass http://%ip%:%web_port%;
   }
}

