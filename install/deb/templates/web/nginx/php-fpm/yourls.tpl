#=======================================================================#
# Default Web Domain Template                                           #
# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS #
#=======================================================================#

server {
	listen      %ip%:%web_port%;
	server_name %domain_idn% %alias_idn%;
	root        %docroot%;
	index       index.php index.html index.htm;
	access_log  /var/log/nginx/domains/%domain%.log combined;
	access_log  /var/log/nginx/domains/%domain%.bytes bytes;
	error_log   /var/log/nginx/domains/%domain%.error.log error;

	include %home%/%user%/conf/web/%domain%/nginx.forcessl.conf*;

	# Rewrites
	location / {
		try_files $uri $uri/ /yourls-loader.php$is_args$args;
	}

	location ~ [^/]\.php(/|$) {
		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		if (!-f $document_root$fastcgi_script_name) {
			return  404;
		}

		fastcgi_pass    %backend_lsnr%;
		fastcgi_index   index.php;
		include         /etc/nginx/fastcgi_params;
	}

	include     %home%/%user%/conf/web/%domain%/nginx.conf_*;
}
