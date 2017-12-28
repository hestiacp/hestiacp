[%backend%]
listen = /var/run/php-%backend%.sock
listen.allowed_clients = 127.0.0.1

user = %user%
group = %user%

listen.owner = %user%
listen.group = nginx

pm = ondemand
pm.max_children = 4
pm.max_requests = 4000
pm.process_idle_timeout = 10s
pm.status_path = /status

php_admin_value[upload_tmp_dir] = /home/%user%/tmp
php_admin_value[session.save_path] = /home/%user%/tmp

env[HOSTNAME] = $HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /home/%user%/tmp
env[TMPDIR] = /home/%user%/tmp
env[TEMP] = /home/%user%/tmp
