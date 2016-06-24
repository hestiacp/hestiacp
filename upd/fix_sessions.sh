#!/bin/bash
# Fix vesta session save path

# Defining vars
VESTA='/usr/local/vesta'
cmd1="$VESTA/upd/fix_sessions.sh"
cmd2="$VESTA/bin/fix_sessions.sh"
cron="$VESTA/data/users/admin/cron.conf"
sessions="$VESTA/data/sessions"
conf="$VESTA/php/etc/php-fpm.conf"
settings="
php_admin_value[memory_limit] = 256M
php_admin_value[post_max_size] = 512M
php_admin_value[upload_max_filesize] = 512M
php_admin_value[max_execution_time] = 600
php_admin_value[max_input_time] = 600
php_admin_value[session.save_path] = $sessions"
user='admin'

# Adding cron job
if [ ! -z "$1" ]; then
    cp $cmd1 $cmd2
    str="JOB='777' MIN='*' HOUR='*' DAY='*' MONTH='*' WDAY='*' SUSPENDED='no'"
    str="$str CMD='sudo $cmd2' TIME='04:39:26' DATE='2016-06-24'"
    if [ -z "$(grep $cmd2 $cron)" ]; then
        echo "$str" >> $cron
        source $VESTA/func/main.sh
        sync_cron_jobs
        $BIN/v-restart-cron
    fi
    exit
fi

if [ ! -d  "$sessions" ]; then
    # Creating new session dir
    mkdir $sessions
    chown admin:admin $sessions
    chmod 770 $sessions

    # Updating php.ini
    if [ -z "$(grep $sessions $conf)" ]; then
        echo "$settings" >> $conf
    fi

    # Moving old sessions to new dir
    for session in $(grep WEB_SYSTEM /tmp/sess_* 2>/dev/null|cut -f1 -d :); do
        mv $session $sessions
    done

    # Reloading php-fpm server
    fpm_pid=$(ps auxf |grep "$conf" |grep -v grep |awk '{print $2}')
    kill -12 $fpm_pid
fi

# Cleaning up cron jobs
if [ ! -z "$(grep $cmd2 $cron)" ]; then
    source $VESTA/func/main.sh
    sed -i "/JOB='777' /d" $cron
    sync_cron_jobs
    $BIN/v-restart-cron
    rm -f $VESTA/bin/fix_sessions.sh
fi

exit
