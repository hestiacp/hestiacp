#=======================================================================#
# Default Web Domain Template                                           #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS #
#=======================================================================#

server {
    listen       %ip%:%proxy_port% default;
    server_name  _;
    #access_log  /var/log/nginx/%ip%.log main;
    location / {
        proxy_pass  http://%ip%:%web_port%;
   }
}

