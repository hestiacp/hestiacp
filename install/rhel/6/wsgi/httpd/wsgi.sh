#!/bin/bash
# Adding php wrapper
user="$1"
domain="$2"
ip="$3"
home_dir="$4"
docroot="$5"

echo "# Wsgi template
AddHandler wsgi-script .wsgi

RewriteEngine On

RewriteCond %{HTTP_HOST} ^www.$2\.ru\$ [NC]
RewriteRule ^(.*)\$ http://$2/\$1 [R=301,L]

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)\$ /django.wsgi/\$1 [QSA,PT,L]
" > $docroot/.htaccess
chown $user:$user $docroot/.htaccess


echo "import os, sys
sys.path.insert(0, '$home_dir/$user/web/$domain/private/django/$domain/env/lib/python2.6/site-packages')
sys.path.insert(0, '$home_dir/$user/web/$domain/private/django/$domain/project/src/shared/')
sys.path.insert(0, '$home_dir/$user/web/$domain/private/django/$domain/project/src/')

os.environ['DJANGO_SETTINGS_MODULE'] = 'main.settings'
import django.core.handlers.wsgi
application = django.core.handlers.wsgi.WSGIHandler()" > $docroot/django.wsgi
chown $user:$user $docroot/django.wsgi

exit 0
