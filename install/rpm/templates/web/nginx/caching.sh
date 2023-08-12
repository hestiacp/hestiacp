#!/bin/bash

user="$1"
domain="$2"
ip="$3"
home="$4"
docroot="$5"

str="proxy_cache_path /var/cache/nginx/$domain levels=1:2 use_temp_path=off keys_zone=$domain:10m inactive=60m max_size=256m;"
conf="/etc/nginx/conf.d/01_caching_pool.conf"

if grep -q "=${domain}:" "$conf" 2> /dev/null; then
	sed -i "/=${domain}:/d" "$conf"
fi

echo "$str" >> $conf
