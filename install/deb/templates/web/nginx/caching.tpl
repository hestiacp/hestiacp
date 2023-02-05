#=========================================================================#
# Default Web Domain Template                                             #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
# https://docs.hestiacp.com/admin_docs/web.html#how-do-web-templates-work #
#=========================================================================#

server {
    listen      %ip%:%proxy_port%;
    server_name %domain_idn% %alias_idn%;

    include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

    location / {
        proxy_pass      http://%ip%:%web_port%;

        proxy_cache %domain%;
        proxy_cache_valid 200 5m;
        proxy_cache_valid 301 302 10m;
        proxy_cache_valid 404 10m;
        proxy_cache_bypass $no_cache $cookie_session $http_x_update;
        proxy_no_cache $no_cache;

        set $no_cache 0;
            if ($request_uri ~* "/wp-admin/|/wp-json/|wp-.*.php|xmlrpc.php|/store.*|/cart.*|/my-account.*|/checkout.*|/user/|/admin/|/administrator/|/manager/|index.php") {
                set $no_cache 1;
            }
            if ($http_cookie ~* "comment_author|wordpress_[a-f0-9]+|wp-postpass|wordpress_no_cache|wordpress_logged_in|woocommerce_items_in_cart|woocommerce_cart_hash|PHPSESSID") {
                set $no_cache 1;
            }
            if ($http_cookie ~ SESS) {
                set $no_cache 1;
            }

        location ~* ^.+\.(%proxy_extensions%)$ {
            proxy_cache    off;
            root           %docroot%;
            access_log     /var/log/%web_system%/domains/%domain%.log combined;
            access_log     /var/log/%web_system%/domains/%domain%.bytes bytes;
            expires        max;
            try_files      $uri @fallback;
        }
    }

    location /error/ {
        alias   %home%/%user%/web/%domain%/document_errors/;
    }

    location @fallback {
        proxy_pass      http://%ip%:%web_port%;
    }

    location ~ /\.(?!well-known\/|file) {
       deny all;
       return 404;
    }

    include %home%/%user%/conf/web/%domain%/nginx.conf_*;
}
