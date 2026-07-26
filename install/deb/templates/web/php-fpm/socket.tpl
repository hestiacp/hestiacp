; origin-src: deb/templates/web/php-fpm/socket.tpl
;#=========================================================================#
;# Default Web Domain Template                                             #
;# DO NOT MODIFY THIS FILE! CHANGES WILL BE LOST WHEN REBUILDING DOMAINS   #
;# https://hestiacp.com/docs/server-administration/web-templates.html      #
;#=========================================================================#

[%backend%]
listen = /run/php/%backend%.sock
listen.owner = %user%
listen.group = www-data
listen.mode = 0660

user = %user%
group = %user%

pm = ondemand
pm.max_children = %max_children%
pm.max_requests = %pm_max_requests%
pm.process_idle_timeout = %pm_process_idle_timeout%
request_terminate_timeout = %pm_request_terminate_timeout%
pm.status_path = /status

php_admin_value[upload_tmp_dir] = /home/%user%/tmp
php_admin_value[session.save_path] = /home/%user%/tmp
php_admin_value[open_basedir] = /home/%user%/.composer:/home/%user%/web/%domain%/public_html:/home/%user%/web/%domain%/public_shtml:/home/%user%/tmp:/var/www/html:/tmp:/bin:/usr/bin:/usr/local/bin:/usr/share:/opt
php_admin_value[sendmail_path] = /usr/sbin/sendmail -t -i -f admin@%domain%

env[HOSTNAME] = $HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /home/%user%/tmp
env[TMPDIR] = /home/%user%/tmp
env[TEMP] = /home/%user%/tmp
