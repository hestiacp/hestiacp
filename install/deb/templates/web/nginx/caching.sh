#!/bin/bash

user=$1
domain=$2
ip=$3
home=$4
docroot=$5

str="proxy_cache_path /var/cache/nginx/$domain levels=1:2" 
str="$str keys_zone=$domain:10m max_size=256m inactive=30m use_temp_path=off;" 
conf='/etc/nginx/conf.d/01_caching_pool.conf'
if [ -e "$conf" ]; then
    if [ -n "$(grep "=${domain}:" $conf)" ]; then
        sed -i "/=${domain}:/d" $conf
    fi
fi
echo "$str" >> $conf
