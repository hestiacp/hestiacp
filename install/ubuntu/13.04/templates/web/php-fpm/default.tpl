[%backend%]
listen = 127.0.0.1:%backend_port%
listen.allowed_clients = 127.0.0.1

user = %user%
group = %user%

pm = dynamic
pm.max_children = 50
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 10

php_admin_value[upload_tmp_dir] = /home/%user%/tmp
php_admin_value[session.save_path] = /home/%user%/tmp

env[HOSTNAME] = $HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /home/%user%/tmp
env[TMPDIR] = /home/%user%/tmp
env[TEMP] = /home/%user%/tmp
