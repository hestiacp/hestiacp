[%backend%]
listen = /var/run/php5-%backend%.sock
listen.allowed_clients = 127.0.0.1

user = %user%
group = %user%

listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 3
pm.min_spare_servers = 2
pm.max_spare_servers = 10

env[HOSTNAME] = $HOSTNAME
env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /tmp
env[TMPDIR] = /tmp
env[TEMP] = /tmp
