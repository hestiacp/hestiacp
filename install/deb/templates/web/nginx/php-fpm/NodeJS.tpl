server {
    listen      %ip%:%web_port%;
    server_name %domain_idn% %alias_idn%;
    
    include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

    error_log  /var/log/%web_system%/domains/%domain%.error.log error;

    location / {
        #single node
        #proxy_pass http://127.0.0.1:3000;

        #unix socket mode
        proxy_pass      http://unix:%home%/%user%/web/%domain%/nodeapp/app.sock:$request_uri;

        
        location ~* ^.+\.(%proxy_extensions%)$ {
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
        #single node
        #proxy_pass     http://127.0.0.1:3000;

        #unix socket mode
        proxy_pass      http://unix:%home%/%user%/web/%domain%/nodeapp/app.sock:/$1;
    }

    location ~ /\.ht    {return 404;}
    location ~ /\.svn/  {return 404;}
    location ~ /\.git/  {return 404;}
    location ~ /\.hg/   {return 404;}
    location ~ /\.bzr/  {return 404;}
    location ~ /\.(?!well-known\/|file) {
       deny all;
       return 404;
    }

    include %home%/%user%/conf/web/%domain%/nginx.conf_*;
    
}
