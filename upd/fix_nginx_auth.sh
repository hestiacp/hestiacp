#!/bin/bash

source /etc/profile.d/vesta.sh
source /usr/local/vesta/conf/vesta.conf

sed -i "s|web/%domain%/stats/auth.*|conf/web/%domain%.auth;|" \
    $VESTA/data/templates/web/nginx/*/*tpl >/dev/null 2>&1

if [ "$WEB_SYSTEM" != 'nginx' ]; then
    exit
fi

check=`egrep "STATS_USER='([0-9]|[a-Z].*)'" $VESTA/data/users/*/web.conf`
if [ ! -z "$check" ]; then
    for user in $(echo $check |cut -f1 -d: |cut -f7 -d/); do
        $VESTA/bin/v-rebuild-web-domains $user no >/dev/null 2>&1
    done
    $VESTA/bin/v-restart-service nginx
fi
