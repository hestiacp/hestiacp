#!/bin/bash
# Adding php wrapper
user="$1"
domain="$2"
ip="$3"
#/home
home_dir="$4"
#Full route to /public_html
docroot="$5"


workingfolder="/home/$user/web/$domain"

cd $workingfolder

# Create the virtual environment with Python 3
virtualenv -p python3 venv

# Activate the virtual environment
source venv/bin/activate

# Install Django and Gunicorn
pip install django==5.2 gunicorn psycopg2-binary

# Create the Django project
django-admin startproject djangoapp

# Django does not have a requirements.txt file
# Install requirements.txt in case one is given by the user in
# the working folder
if [ -f "$workingfolder/djangoapp/requirements.txt" ]; then

     pip install -r /home/$user/web/$domain/djangoapp/requirements.txt

fi

# Make Django migration and  change ownership of the created SQLite database
cd djangoapp
./manage.py makemigrations && ./manage.py migrate
chown $user:$user db.sqlite3
# fix error for os
sed -i '/from pathlib import Path/a import os' $workingfolder/djangoapp/djangoapp/settings.py
# Add static folder and run collectstatic
echo "
STATIC_ROOT = os.path.join(BASE_DIR, 'static/')" >> $workingfolder/djangoapp/djangoapp/settings.py

./manage.py collectstatic

# fix file authorization
chown -R $user:$user /home/$user/web/$domain/djangoapp/

# file anpassen um host hinzuzufügen
sed -i 's/ALLOWED_HOSTS = \[\]/ALLOWED_HOSTS = ["'$domain'"]/g' $workingfolder/djangoapp/djangoapp/settings.py



# At this stage you can test that it works executing:
# gunicorn -b 0.0.0.0:8000 djangoapp.wsgi:application
# *after* adding your domain to ALLOWED_HOSTS

# This following part adds Gunicorn socket and service,
# and needs to be improved, particularly to allow multiple
# Django applications running in the same server.

# This is intended for Ubuntu. It will require some testing to check how this works
# in other distros.


if [ ! -f "/etc/systemd/system/$domain-gunicorn.socket" ]; then

echo "[Unit]
Description=gunicorn socket

[Socket]
ListenStream=/run/$domain-gunicorn.sock

[Install]
WantedBy=sockets.target" > /etc/systemd/system/$domain-gunicorn.socket

fi

if [ ! -f "/etc/systemd/system/$domain-gunicorn.service" ]; then

    echo "[Unit]
Description=Gunicorn daemon for $domain
Requires=$domain-gunicorn.socket
After=network.target

[Service]
User=$user
Group=$user
WorkingDirectory=$workingfolder/djangoapp

ExecStart=$workingfolder/venv/bin/gunicorn --access-logfile - --workers 3 --bind unix:/run/$domain-gunicorn.sock -m 007 djangoapp.wsgi:application

[Install]
WantedBy=multi-user.target" > /etc/systemd/system/$domain-gunicorn.service

fi

systemctl restart $domain-gunicorn.socket

systemctl start $domain-gunicorn.socket

systemctl enable $domain-gunicorn.socket

# Start the socket
curl --unix-socket /run/$domain-gunicorn.sock localhost

sudo systemctl daemon-reload

sudo systemctl restart gunicorn

exit 0
