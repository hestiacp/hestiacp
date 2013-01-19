#!/bin/bash
# Vesta installer

# Define Variables
RHOST='r.vestacp.com'
CHOST='c.vestacp.com'

REPO='cmmnt'
VERSION='0.9.7'
YUM_REPO='/etc/yum.repos.d/vesta.repo'
arch=$(uname -i)

tools="screen mc libpng libjpeg curl libmcrypt mhash zip unzip freetype ntp
    openssl flex libxml2 ImageMagick sqlite pcre sudo bc jwhois mailx lsof
    tar telnet rsync"

rpms="nginx httpd mod_ssl mod_ruid2 mod_extract_forwarded mod_fcgid ftp
    webalizer awstats mysql mysql-server php php-bcmath php-cli php-common
    php-gd php-imap php-mbstring php-mcrypt php-mysql php-pdo php-soap php-tidy
    php-xml php-xmlrpc phpMyAdmin exim dovecot clamd spamassassin roundcubemail
    bind bind-utils bind-libs vsftpd rrdtool GeoIP vesta vesta-nginx vesta-php"

# Am I root?
if [ "x$(id -u)" != 'x0' ]; then
    echo 'Error: this script can only be executed by root'
    exit 1
fi

# Check supported version
if [ ! -e '/etc/redhat-release' ]; then
    echo 'Error: sorry, we currently support RHEL and CentOS only'
    exit 1
fi

os=$(cut -f 1 -d ' ' /etc/redhat-release)
if [ $os !=  'CentOS' ] && [ $os != 'Red' ]; then
    echo 'Error: sorry, we currently support RHEL and CentOS only'
fi
release=$(grep -o "[0-9]" /etc/redhat-release |head -n1)

help() {
    echo "usage: $0 [OPTIONS]
   -d, --disable-remi         Disable remi
   -e, --email                Define email address
   -h, --help                 Print this help and exit
   -f, --force                Force installation"
    exit 1
}

# Translating argument to --gnu-long-options
for arg; do
    delim=""
    case "$arg" in
        --help)         args="${args}-h " ;;
        --disable-remi) args="${args}-d " ;;
        --force)        args="${args}-f " ;;
        --email)        args="${args}-e " ;;
        *)              [[ "${arg:0:1}" == "-" ]] || delim="\""
                        args="${args}${delim}${arg}${delim} ";;
    esac
done
eval set -- "$args"

# Getopt
while getopts "dhfe:" Option; do
    case $Option in
        d) disable_remi='yes' ;;          # Disable remi repo
        h) help ;;                        # Help
        e) email=$OPTARG ;;               # Contact email
        f) force=yes ;;                   # Force install
        *) help ;;                        # Default
    esac
done

# Are you sure ?
if [ -z $email ]; then
    echo
    echo
    echo
    echo
    echo
    echo '         ***********************************************************'
    echo
    echo '             _|      _|  _|_|_|_|    _|_|_|  _|_|_|_|_|    _|_|     '
    echo '             _|      _|  _|        _|            _|      _|    _|   '
    echo '             _|      _|  _|_|_|      _|_|        _|      _|_|_|_|   '
    echo '               _|  _|    _|              _|      _|      _|    _|   '
    echo '                 _|      _|_|_|_|  _|_|_|        _|      _|    _|   '
    echo
    echo
    echo
    echo
    echo
    echo
    echo
    echo
    echo '         ***********************************************************'
    echo
    echo
    read -n 1 -p 'Do you want to install Vesta Control Panel? [y/n]): ' answer
    if [ "$answer" != 'y'  ] && [ "$answer" != 'Y'  ]; then
        echo 'Goodbye'
        exit 1
    fi
    echo

    # Check email
    read -p 'Please enter valid email address: ' email
fi

# Validate email
local_part=$(echo $email | cut  -s -f1 -d\@)
remote_host=$(echo $email | cut -s -f2 -d\@)
mx_failed=1
if [ ! -z "$remote_host" ] && [ ! -z "$local_part" ]; then
    /usr/bin/host -t mx "$remote_host" &> /dev/null
    mx_failed="$?"
fi

if [ "$mx_failed" -eq 1 ]; then
    echo "Error: email $email is not valid"
    exit 1
fi

echo
echo
echo
echo
echo 'Installation will take about 15 minutes ...'
echo
sleep 2

# Check wget
if [ ! -e '/usr/bin/wget' ]; then
    yum -y install wget
    if [ $? -ne 0 ]; then
        echo "Error: can't install wget"
        exit 1
    fi
fi

# Check repo availability
wget "$RHOST/$REPO/vesta.conf" -O /dev/null
if [ $? -ne 0 ]; then
    echo "Error: no access to $REPO repository"
    exit 1
fi

# Check installed packages
tmpfile=$(mktemp -p /tmp)
rpm -qa > $tmpfile
for rpm in $rpms; do 
    if [ ! -z "$(grep ^$rpm. $tmpfile)" ]; then
        conflicts="$rpm $conflicts"
    fi
done
rm -f $tmpfile

if [ ! -z "$conflicts" ] && [ -z "$force" ]; then
    echo
    echo '!!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!!'
    echo
    echo 'Following rpm packages aleady installed:'
    echo "$conflicts"
    echo
    echo 'It is highly recommended to remove them before proceeding.'
    echo
    echo 'If you want to force installation run this script with -f option:'
    echo "Example: bash $0 --force"
    echo
    echo '!!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!!'
    echo
    exit 1
fi

# Password generator
gen_pass() {
    MATRIX='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
    LENGTH=10
    while [ ${n:=1} -le $LENGTH ]; do
        PASS="$PASS${MATRIX:$(($RANDOM%${#MATRIX})):1}"
        let n+=1
    done
    echo "$PASS"
}

# Update system
yum -y update
if [ $? -ne 0 ]; then
    echo 'Error: yum update failed'
    exit 1
fi

# Install additional packages
yum -y install $tools
if [ $? -ne 0 ]; then
    echo 'Error: yum install failed'
    exit 1
fi

# Install EPEL repo
if [ ! -e '/etc/yum.repos.d/epel.repo' ]; then
    if [ "$release" -eq '5' ]; then
        epel="5/$arch/epel-release-5-4.noarch.rpm"
    fi

    if [ "$release" -eq '6' ]; then
        epel="6/$arch/epel-release-6-8.noarch.rpm"
    fi

    rpm -ivh http://dl.fedoraproject.org/pub/epel/$epel
    if [ $? -ne 0 ]; then
        echo "Error: can't install EPEL repository"
        exit 1
    fi
fi

# Install remi repo
if [ ! -e '/etc/yum.repos.d/remi.repo' ]; then
    if [ "$release" -eq '5' ]; then
        remi="remi-release-5.rpm"
    fi

    if [ "$release" -eq '6' ]; then
        remi="remi-release-6.rpm"
    fi

    rpm -ivh http://rpms.famillecollet.com/enterprise/$remi
    if [ $? -ne 0 ]; then
        echo "Error: can't install remi repository"
        exit 1
    fi
fi

# Install vesta repo
echo "[vesta]
name=Vesta - $REPO
baseurl=http://$RHOST/$REPO/$release/\$basearch/
enabled=1
gpgcheck=1
gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-VESTA" > $YUM_REPO
wget $CHOST/GPG.txt -O /etc/pki/rpm-gpg/RPM-GPG-KEY-VESTA

# Checking if old MySQL stuff exists
if [ -e '/var/lib/mysql' ]; then
    mv -f /var/lib/mysql /var/lib/mysql_old
fi
if [ -e '/etc/my.cnf' ]; then 
    mv -f /etc/my.cnf /etc/my.cnf_old
fi
if [ -e '/root/.my.cnf' ]; then
    mv -f /root/.my.cnf
fi

# Install Vesta packages
if [ -z "$disable_remi" ]; then 
    yum -y --enablerepo=remi install $rpms
else
    yum -y install $rpms
fi
if [ $? -ne 0 ]; then
    echo 'Error: yum install failed'
    exit 1
fi

# Configuring run levels
chkconfig iptables off
if [ -e /etc/init.d/sendmail ]; then
    chkconfig sendmail off
fi
if [ -e /etc/init.d/postfix ]; then
    chkconfig postfix off
fi
chkconfig vesta on
chkconfig httpd on
chkconfig nginx on
chkconfig mysqld on
chkconfig vsftpd on
chkconfig named on
chkconfig exim on
chkconfig clamd on
chkconfig spamassassin on
chkconfig dovecot on

# Make dirs more visible
echo 'LS_COLORS="$LS_COLORS:di=00;33"' >> /etc/profile

# Vesta does not support SELINUX for now
if [ -e '/etc/sysconfig/selinux' ]; then
    sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/sysconfig/selinux
    setenforce 0
fi
if [ -e '/etc/selinux/config' ]; then
    sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config
    setenforce 0
fi

# Vesta use own webalizer routine
rm -f /etc/cron.daily/00webalizer

# NTP Synchronization
echo '#!/bin/sh' > /etc/cron.daily/ntpdate
echo "$(which ntpdate) -s pool.ntp.org" >> /etc/cron.daily/ntpdate
chmod 775 /etc/cron.daily/ntpdate
ntpdate -s pool.ntp.org

# Vesta Environment
echo "export VESTA='/usr/local/vesta'" > /etc/profile.d/vesta.sh
chmod 755 /etc/profile.d/vesta.sh
source /etc/profile.d/vesta.sh
echo 'PATH=$PATH:/usr/local/vesta/bin' >> /root/.bash_profile
echo 'export PATH' >> /root/.bash_profile
source /root/.bash_profile
mkdir -p $VESTA/conf
mkdir -p $VESTA/log
mkdir -p $VESTA/data
mkdir -p $VESTA/ssl
chmod 770 $VESTA/conf

# Make backup directory
vst_backups="/root/vst_install_backups/$(date +%s)"
mkdir -p $vst_backups
mkdir -p $vst_backups/nginx
mkdir -p $vst_backups/httpd
mkdir -p $vst_backups/mysql
mkdir -p $vst_backups/exim
mkdir -p $vst_backups/dovecot
mkdir -p $vst_backups/clamd
mkdir -p $vst_backups/vsftpd
mkdir -p $vst_backups/named

wget $RHOST/$REPO/vesta.conf -O $VESTA/conf/vesta.conf
if [ -e '/etc/sudoers' ]; then
    mv /etc/sudoers $vst_backups/
fi
wget $CHOST/$VERSION/sudoers.conf -O /etc/sudoers
chmod 0440 /etc/sudoers
wget $CHOST/$VERSION/vesta.log -O /etc/logrotate.d/vesta

sed -i "s/umask 022/umask 002/g" /etc/profile

# Create backup directory
adduser backup
ln -s /home/backup /backup
chmod a+x /backup

# Configuring data templates
cd /usr/local/vesta/data
mkdir ips
mkdir queue
mkdir users
touch queue/backup.pipe
touch queue/disk.pipe
touch queue/webstats.pipe
touch queue/restart.pipe
touch queue/traffic.pipe
chmod 750 users
chmod 750 ips
chmod -R 750 queue
wget $CHOST/$VERSION/packages.tar.gz -O packages.tar.gz
tar -xzf packages.tar.gz
rm -f packages.tar.gz
cd /usr/local/vesta/data
wget $CHOST/$VERSION/templates.tar.gz -O templates.tar.gz
tar -xzf templates.tar.gz
rm -f templates.tar.gz
chmod -R 755 /usr/local/vesta/data/templates

cp templates/web/skel/public_html/index.html /var/www/html/
sed -i 's/%domain%/It worked!/g' /var/www/html/index.html

# Configuring ssl keys
cd /usr/local/vesta/ssl
wget $CHOST/$VERSION/certificate.crt -O certificate.crt
wget $CHOST/$VERSION/certificate.key -O certificate.key

# Adding admin user
vpass=$(gen_pass)
$VESTA/bin/v-add-user admin $vpass $email default System Administrator
if [ $? -ne 0 ]; then
    echo "Error: can't create admin user"
    exit 1
fi

# Set shell
$VESTA/bin/v-change-user-shell admin bash

# Apache
if [ -e '/etc/httpd/conf/httpd.conf' ]; then
    mv /etc/httpd/conf/httpd.conf $vst_backups/httpd/
fi
if [ -e '/etc/httpd/conf.d/ssl.conf' ]; then
    mv /etc/httpd/conf.d/ssl.conf $vst_backups/httpd/
fi
if [ -e '/etc/httpd/conf.d/proxy_ajp.conf' ]; then
    mv /etc/httpd/conf.d/proxy_ajp.conf $vst_backups/httpd/
fi
wget $CHOST/$VERSION/httpd.conf -O /etc/httpd/conf/httpd.conf
wget $CHOST/$VERSION/httpd-status.conf -O /etc/httpd/conf.d/status.conf
wget $CHOST/$VERSION/httpd-ssl.conf -O /etc/httpd/conf.d/ssl.conf
wget $CHOST/$VERSION/httpd.log -O /etc/logrotate.d/httpd
echo "MEFaccept 127.0.0.1" >> /etc/httpd/conf.d/mod_extract_forwarded.conf
echo > /etc/httpd/conf.d/proxy_ajp.conf
echo > /etc/httpd/conf.d/vesta.conf
touch /var/log/httpd/access_log
touch /var/log/httpd/error_log
touch /var/log/httpd/suexec.log
mkdir -p /var/log/httpd/domains
chmod a+x /var/log/httpd
chmod 640 /var/log/httpd/access_log
chmod 640 /var/log/httpd/error_log
chmod 640 /var/log/httpd/suexec.log
chmod 751 /var/log/httpd/domains

# Enable short_open_tag in php config
sed -i 's/short_open_tag = Off/short_open_tag = On/g' /etc/php.ini

# Nginx
if [ -e '/etc/nginx/nginx.conf' ]; then
    mv /etc/nginx/nginx.conf $vst_backups/nginx/
fi
if [ -f '/etc/nginx/conf.d/default.conf' ]; then
    mv /etc/nginx/conf.d/default.conf $vst_backups/nginx/
fi
if [ -e '/etc/nginx/conf.d/example_ssl.conf' ]; then
    mv /etc/nginx/conf.d/example_ssl.conf $vst_backups/nginx/
fi

wget $CHOST/$VERSION/nginx.conf -O /etc/nginx/nginx.conf
wget $CHOST/$VERSION/nginx-status.conf -O /etc/nginx/conf.d/status.conf
touch /etc/nginx/conf.d/vesta_ip.conf
touch /etc/nginx/conf.d/vesta_users.conf

# VsFTP
if [ -e '/etc/vsftpd/vsftpd.conf' ]; then
    mv /etc/vsftpd/vsftpd.conf $vst_backups/vsftpd/
fi
wget $CHOST/$VERSION/vsftpd.conf -O /etc/vsftpd/vsftpd.conf

# MySQL
if [ -e '/etc/my.cnf' ]; then
    mv /etc/my.cnf $vst_backups/mysql/
fi

if [ -e '/root/.my.cnf' ]; then
    mv /root/.my.cnf $vst_backups/mysql/
fi
mpass=$(gen_pass)
server_memory="$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])"
if [ "$server_memory" -gt '1000000' ]; then
    wget $CHOST/$VERSION/mysql.cnf -O /etc/my.cnf
else
    wget $CHOST/$VERSION/mysql-512.cnf -O /etc/my.cnf
fi
service mysqld start
mysqladmin -u root password $mpass
echo -e "[client]\npassword='$mpass'\n" >/root/.my.cnf
$VESTA/bin/v-add-database-server mysql localhost 3306 root $mpass
$VESTA/bin/v-add-database admin default default $(gen_pass) mysql

# Bind
if [ -e '/etc/named.conf' ]; then
    mv /etc/named.conf $vst_backups/named/
fi
wget $CHOST/$VERSION/named.conf -O /etc/named.conf
chown root:named /etc/named.conf
chmod 640 /etc/named.conf

# Exim
if [ -e '/etc/exim/exim.conf' ]; then
    mv /etc/exim/exim.conf $vst_backups/exim/
fi
if [ -e '/etc/clamd.conf' ]; then
    mv /etc/clamd.conf $vst_backups/clamd/
fi
wget $CHOST/$VERSION/exim.conf -O /etc/exim/exim.conf
wget $CHOST/$VERSION/dnsbl.conf -O /etc/exim/dnsbl.conf
wget $CHOST/$VERSION/spam-blocks.conf -O /etc/exim/spam-blocks.conf
wget $CHOST/$VERSION/clamd.conf -O /etc/clamd.conf
mkdir /etc/exim/domains
chmod 640 /etc/exim/exim.conf
gpasswd -a clam exim
gpasswd -a exim mail
gpasswd -a clam mail
gpasswd -a dovecot mail
/usr/bin/freshclam

# Dovecot config
if [ "$release" -eq '5' ]; then
    if -e [ '/etc/dovecot.conf' ]; then
        mv /etc/dovecot.conf $vst_backups/dovecot/
    fi
    wget $CHOST/$VERSION/dovecot.conf -O /etc/dovecot.conf
    
else
    if [ -e '/etc/dovecot' ]; then
        mv /etc/dovecot/* $vst_backups/dovecot/
    fi
    wget $CHOST/$VERSION/dovecot.tar.gz -O  /etc/dovecot.tar.gz
    cd /etc/
    tar -xzf dovecot.tar.gz
    rm -f dovecot.tar.gz
fi

# PMA
wget $CHOST/$VERSION/httpd-pma.conf -O /etc/httpd/conf.d/phpMyAdmin.conf
wget $CHOST/$VERSION/pma.conf -O /etc/phpMyAdmin/config.inc.php
sed -i "s/%blowfish_secret%/$(gen_pass)/g" /etc/phpMyAdmin/config.inc.php

# Roundcube setup
wget $CHOST/$VERSION/httpd-webmail.conf -O /etc/httpd/conf.d/roundcubemail.conf
wget $CHOST/$VERSION/roundcube-main.conf -O /etc/roundcubemail/main.inc.php
wget $CHOST/$VERSION/roundcube-db.conf -O /etc/roundcubemail/db.inc.php
wget $CHOST/$VERSION/roundcube-driver.php -O /usr/share/roundcubemail/plugins/password/vesta.php
wget $CHOST/$VERSION/roundcube-pw.conf -O /usr/share/roundcubemail/plugins/password/config.inc.php

r="$(gen_pass)"
mysql -e "CREATE DATABASE roundcube"
mysql -e "GRANT ALL ON roundcube.* TO roundcube@localhost IDENTIFIED BY '$r'"
sed -i "s/%password%/$r/g" /etc/roundcubemail/db.inc.php
mysql roundcube < /usr/share/doc/roundcubemail-*/SQL/mysql.initial.sql

# Configuring ip
$VESTA/bin/v-update-sys-ip

# Get main ip
main_ip=$(ifconfig |grep 'inet addr:' |grep -v 127.0.0.1 |head -n1 |\
    cut -f2 -d: | cut -f1 -d ' ')

# Add default web domain on main ip
$VESTA/bin/v-add-web-domain admin default.domain $main_ip

# Add default dns domain on main ip
$VESTA/bin/v-add-dns-domain admin default.domain $main_ip

# Add default mail domain
$VESTA/bin/v-add-mail-domain admin default.domain

# Configuring crond
command='sudo /usr/local/vesta/bin/v-update-sys-queue disk'
$VESTA/bin/v-add-cron-job 'admin' '15' '02' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-update-sys-queue traffic'
$VESTA/bin/v-add-cron-job 'admin' '10' '00' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-update-sys-queue webstats'
$VESTA/bin/v-add-cron-job 'admin' '30' '03' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-update-sys-queue backup'
$VESTA/bin/v-add-cron-job 'admin' '*/30' '*' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-backup-users'
$VESTA/bin/v-add-cron-job 'admin' '10' '05' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-update-user-stats'
$VESTA/bin/v-add-cron-job 'admin' '20' '00' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-update-sys-rrd'
$VESTA/bin/v-add-cron-job 'admin' '*/5' '*' '*' '*' '*' "$command"

# Creating symlink
ln -s /usr/local/vesta/log /var/log/vesta

# Stop unused services
services='iptables sendmail postfix'
for srv in $services; do
    service $srv status > /dev/null
    if [ $? -eq 0 ]; then
        service $srv stop
    fi
done

# Start system service
services='vesta httpd nginx vsftpd exim dovecot clamd spamassassin named crond'
for srv in $services; do
    service $srv status > /dev/null
    if [ $? -gt 0 ]; then
        service $srv start
    else
        service $srv restart
    fi
done

# Change sendmail client
rm -f /etc/alternatives/mta
ln -s /usr/sbin/sendmail.exim /etc/alternatives/mta

# Build inititall rrd images
$VESTA/bin/v-update-sys-rrd

# Send notification to vestacp.com
wget vestacp.com/notify/?$REPO -O /dev/null

# Get server ip
vst_ip=$(wget vestacp.com/what-is-my-ip/ -O - 2>/dev/null)
if [ ! -z "$vst_ip" ] && [ "$vst_ip" != "$main_ip" ]; then
    # Assign passive ip address
    echo "pasv_address=$vst_ip" >> /etc/vsftpd/vsftpd.conf
fi

if [ -z "$vst_ip" ]; then
    vst_ip=$main_ip
fi

# Send email
echo -e "Congratulations, you have just successfully installed \
the Vesta Control Panel

You can login in Vesta with following credentials:
    username: admin
    password: $vpass
    https://$vst_ip:8083

We hope that you enjoy your installation of Vesta. Please \
feel free to contact us anytime if you have any questions.
Thank you.

--
Sincerely yours
vestacp.com team
" > $tmpfile

cat $tmpfile | mail -s "Vesta Control Panel" $email
rm -f $tmpfile

# Congrats
echo
echo
echo '         ***********************************************************'
echo
echo '             _|      _|  _|_|_|_|    _|_|_|  _|_|_|_|_|    _|_|     '
echo '             _|      _|  _|        _|            _|      _|    _|   '
echo '             _|      _|  _|_|_|      _|_|        _|      _|_|_|_|   '
echo '               _|  _|    _|              _|      _|      _|    _|   '
echo '                 _|      _|_|_|_|  _|_|_|        _|      _|    _|   '
echo
echo '           Congratulations, you have just successfully installed'
echo '           the Vesta Control Panel!'
echo
echo '           Now you can login in Vesta with following credentials:'
echo '               username: admin'
echo "               password: $vpass"
echo "               https://$vst_ip:8083/"
echo
echo
echo '           Thank you for using our product.'
echo
echo '         ***********************************************************'
echo
echo

# Tricky way to get new PATH variable
cd
bash

# EOF
