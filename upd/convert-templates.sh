#!/bin/bash

# Check version
source /usr/local/vesta/conf/vesta.conf
if [ "$VERSION" != '0.9.7' ]; then
    exit
fi

# Rename web system service
sed -i "s/apache/httpd/g" /usr/local/vesta/conf/vesta.conf

# Rename dns system service
sed -i "s/bind/named/g" /usr/local/vesta/conf/vesta.conf

# Rename nginx config
mv /etc/nginx/conf.d/vesta_users.conf /etc/nginx/conf.d/vesta.conf 2>/dev/null
rm -f /etc/nginx/conf.d/vesta_ip.conf 2>/dev/null

# Update user packages
PKG=/usr/local/vesta/data/packages
for package in $(ls $PKG); do
    default=$(grep "^TEMPLATE='" $PKG/$package | cut -f2 -d \')
    if [ ! -z "$default" ]; then
        tpl="WEB_TEMPLATE='$default'"
        tpl="$tpl\nPROXY_TEMPLATE='default'"
        tpl="$tpl\nDNS_TEMPLATE='default'"
        sed -i "s/^TEMPLATE=.*/$tpl/g" $PKG/$package
    fi
done

# Update users
USR=/usr/local/vesta/data/users
for user in $(ls $USR); do
    default=$(grep "^TEMPLATE='" $USR/$user/user.conf | cut -f2 -d \')
    if [ ! -z "$default" ]; then
        tpl="WEB_TEMPLATE='$default'"
        tpl="$tpl\nPROXY_TEMPLATE='default'"
        tpl="$tpl\nDNS_TEMPLATE='default'"
        sed -i "s/^TEMPLATE=.*/$tpl/g" $USR/$user/user.conf
    fi
done

# Rename NGINX to PROXY key
sed -i "s/NGINX/PROXY/g" /usr/local/vesta/data/users/*/web.conf

# Check template structure
TPL='/usr/local/vesta/data/templates/web'
if [ -e "$TPL/apache" ]; then
    mv $TPL/apache $TPL/httpd
fi

# Remove unused email template
if [ -e $TPL/email_reset_password.tpl ]; then
    rm -f $TPL/email_reset_password.tpl
fi

# Update httpd templates
if [ ! -z "$(ls $TPL | grep apache_)" ]; then
    mkdir -p $TPL/httpd
    mv $TPL/apache_* $TPL/httpd/
    for template in $(ls $TPL/httpd); do
        new_name=$(echo $template | sed -e "s/apache_//")
        mv -f $TPL/httpd/$template $TPL/httpd/$new_name
    done
fi
if [ -e "$TPL/httpd" ]; then
    sed -i -e "s/%elog%//g" \
        -e "s/%cgi%//g" \
        -e "s/%cgi_option%/+ExecCGI/g" $TPL/httpd/*
fi

# Update nginx templates
if [ ! -z "$(ls $TPL/| grep nginx_)" ];then
    mkdir -p $TPL/nginx
    mv $TPL/nginx_* $TPL/nginx/
    for template in $(ls $TPL/nginx/); do
        new_name=$(echo $template |sed -e "s/nginx_//")
        mv -f $TPL/nginx/$template $TPL/nginx/$new_name
    done
fi
if [ -e "$TPL/ngingx.ip.tpl" ]; then
    mv $TPL/ngingx.ip.tpl $TPL/nginx/proxy_ip.tpl
fi
if [ -e "$TPL/nginx/ip.tpl" ]; then
    mv $TPL/nginx/ip.tpl $TPL/nginx/proxy_ip.tpl
fi
if [ -e "$TPL/nginx" ]; then
    sed -i -e "s/%elog%//g" \
        -e "s/nginx_extentions/proxy_extentions/g" $TPL/nginx/*
fi

# Move Awstats templates
if [ -e "$TPL/awstats.tpl" ]; then
    mkdir -p $TPL/awstats
    mv $TPL/awstats.tpl $TPL/awstats
    mv $TPL/awstats_index.tpl $TPL/awstats/index.tpl
    mv $TPL/awstats_nav.tpl $TPL/awstats/nav.tpl
fi

# Move Webalizer templates
if [ -e "$TPL/webalizer.tpl" ]; then
    mkdir -p $TPL/webalizer
    mv $TPL/webalizer.tpl $TPL/webalizer
fi

# Update proxy ip configuration
for ip in $(ls /usr/local/vesta/data/ips); do
    cat $TPL/nginx/proxy_ip.tpl |\
        sed -e "s/%ip%/$ip/g" \
            -e "s/%web_port%/8080/g" \
            -e "s/%proxy_port%/80/g" \
        > /etc/nginx/conf.d/$ip.conf
done

# Remove broken symlink protection
sed -i '/Symlinks protection/d' /etc/nginx/nginx.conf
sed -i '/disable_symlinks.*/d' /etc/nginx/nginx.conf

# Add improved symlink protection
nginx_version=$(rpm -q nginx| grep nginx-1| cut -f 2 -d .)
if [ -e "/tmp/nginx" ] && [[ "$nginx_version" -gt '0' ]]; then
    for tpl in $(ls $TPL/nginx |grep -v proxy_ip.tpl); do
        check_symlink=$(grep disable_symlinks $TPL/nginx/$tpl)
        if [ -z "$check_symlink" ]; then
            insert='disable_symlinks if_not_owner from=%home%\/%user%;'
            sed -i "s/include %/$insert\n\n    include %/" $TPL/nginx/$tpl
            triggered='yes'
        fi
    done

    if [ "$triggered" = 'yes' ]; then
        # Rebuild web domains
        for user in $(ls $VESTA/data/users); do
            /usr/local/vesta/bin/v-rebuild-web-domains $user no
        done

        # Restart proxy
        /usr/local/vesta/bin/v-restart-proxy
    fi
fi

# Update version
sed -i 's/0.9.7/0.9.8/' /usr/local/vesta/conf/vesta.conf

exit
