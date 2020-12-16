#!/bin/bash
# Adding php pool conf
user="$1"
domain="$2"
ip="$3"
home_dir="$4"
docroot="$5"

pool_conf="[$2]

listen = /var/run/php-fpm/php7.1-fpm-$2.sock
listen.owner = $1
listen.group = apache
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

pool_file_56="/etc/opt/remi/php56/php-fpm.d/$2.conf"
pool_file_70="/etc/opt/remi/php70/php-fpm.d/$2.conf"
pool_file_71="/etc/opt/remi/php71/php-fpm.d/$2.conf"
pool_file_72="/etc/opt/remi/php72/php-fpm.d/$2.conf"
pool_file_73="/etc/opt/remi/php73/php-fpm.d/$2.conf"
pool_file_74="/etc/opt/remi/php74/php-fpm.d/$2.conf"
pool_file_80="/etc/opt/remi/php80/php-fpm.d/$2.conf"

if [ -f "$pool_file_56" ]; then
    rm $pool_file_56
    systemctl restart php56-php-fpm
fi

if [ -f "$pool_file_70" ]; then
    rm $pool_file_70
    systemctl restart php70-php-fpm
fi

if [ -f "$pool_file_71" ]; then
    echo "$pool_conf" > $pool_file_71
    systemctl restart php71-php-fpm
fi

if [ -f "$pool_file_72" ]; then
    rm $pool_file_72
    systemctl restart php72-php-fpm
fi

if [ -f "$pool_file_73" ]; then
    rm $pool_file_73
    systemctl restart php73-php-fpm
fi

if [ ! -f "$pool_file_74" ]; then
    rm $pool_file_74
    systemctl restart php74-php-fpm
fi

if [ -f "$pool_file_80" ]; then
    rm $pool_file_80
    systemctl restart php80-php-fpm
fi

exit 0