#!/bin/bash

# Define data path
TPL='/usr/local/vesta/data/templates/web'

# Check for new template structure
if [ -e "$TPL/apache" ]; then
    exit
fi

# Remove unused email template
rm -f $TPL/email_reset_password.tpl

# Apache
mkdir -p $TPL/apache
if [ ! -z "$(ls $TPL/| grep apache_)" ];then
    mv $TPL/apache_* $TPL/apache/
    for template in $(ls $TPL/apache/); do
        new_name=$(echo $template |sed -e "s/apache_//")
        mv -f $TPL/apache/$template $TPL/apache/$new_name
    done
fi

# Nginx
mkdir -p $TPL/nginx
if [ ! -z "$(ls $TPL/| grep nginx_)" ];then
    mv $TPL/nginx_* $TPL/nginx/
    for template in $(ls $TPL/nginx/); do
        new_name=$(echo $template |sed -e "s/nginx_//")
        mv -f $TPL/nginx/$template $TPL/nginx/$new_name
    done
fi
if [ -e "$TPL/ngingx.ip.tpl" ]; then
    mv $TPL/ngingx.ip.tpl $TPL/nginx/ip.tpl
fi

# Awstats
mkdir -p $TPL/awstats
if [ -e "$TPL/awstats.tpl" ]; then
    mv $TPL/awstats.tpl $TPL/awstats
    mv $TPL/awstats_index.tpl $TPL/awstats/index.tpl
    mv $TPL/awstats_nav.tpl $TPL/awstats/nav.tpl
fi

# Webalizer
mkdir -p $TPL/webalizer
if [ -e "$TPL/webalizer.tpl" ]; then
    mv $TPL/webalizer.tpl $TPL/webalizer
fi

exit
