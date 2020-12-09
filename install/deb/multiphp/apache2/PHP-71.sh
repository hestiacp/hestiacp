#!/bin/bash
# Adding php pool conf
user="$1"
domain="$2"
ip="$3"
home_dir="$4"
docroot="$5"

pool_conf="[$2]

listen = /run/php/php7.1-fpm-$2.sock
listen.owner = $1
listen.group = www-data
listen.mode = 0660

user = $1
group = $1

pm = ondemand
pm.max_children = 8
pm.max_requests = 4000
pm.process_idle_timeout = 10s
pm.status_path = /status

php_admin_value[upload_tmp_dir] = /home/$1/tmp
php_admin_value[session.save_path] = /home/$1/tmp
php_admin_value[open_basedir] = $5:/home/$1/tmp:/bin:/usr/bin:/usr/local/bin:/var/www/html:/tmp:/usr/share:/etc/phpMyAdmin:/etc/phpmyadmin:/var/lib/phpmyadmin:/etc/roundcubemail
php_admin_value[upload_max_filesize] = 80M
php_admin_value[max_execution_time] = 20
php_admin_value[post_max_size] = 80M
php_admin_value[memory_limit] = 256M
php_admin_value[sendmail_path] = /usr/sbin/sendmail -t -i -f info@$2
php_admin_flag[mysql.allow_persistent] = off
php_admin_flag[safe_mode] = off

env[PATH] = /usr/local/bin:/usr/bin:/bin
env[TMP] = /home/$1/tmp
env[TMPDIR] = /home/$1/tmp
env[TEMP] = /home/$1/tmp
"

pool_file_56="/etc/php/5.6/fpm/pool.d/$2.conf"
pool_file_70="/etc/php/7.0/fpm/pool.d/$2.conf"
pool_file_71="/etc/php/7.1/fpm/pool.d/$2.conf"
pool_file_72="/etc/php/7.2/fpm/pool.d/$2.conf"
pool_file_73="/etc/php/7.3/fpm/pool.d/$2.conf"
pool_file_74="/etc/php/7.4/fpm/pool.d/$2.conf"
pool_file_80="/etc/php/8.0/fpm/pool.d/$2.conf"

if [ -f "$pool_file_56" ]; then
    rm $pool_file_56
    service php5.6-fpm restart
fi

if [ -f "$pool_file_70" ]; then
    rm $pool_file_70
    service php7.0-fpm restart
fi

if [ ! -f "$pool_file_71" ]; then
    echo "$pool_conf" > $pool_file_71
    service php7.1-fpm restart
fi

if [ -f "$pool_file_72" ]; then
    rm $pool_file_72
    service php7.2-fpm restart
fi

if [ -f "$pool_file_73" ]; then
    rm $pool_file_73
    service php7.3-fpm restart
fi

if [ -f "$pool_file_74" ]; then
    rm $pool_file_74
    service php7.4-fpm restart
fi

if [ -f "$pool_file_80" ]; then
    rm $pool_file_80
    service php8.0-fpm restart
fi

exit 0