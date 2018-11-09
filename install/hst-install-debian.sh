#!/bin/bash

# Hestia Debian installer v1.0

#----------------------------------------------------------#
#                  Variables&Functions                     #
#----------------------------------------------------------#
export PATH=$PATH:/sbin
export DEBIAN_FRONTEND=noninteractive
RHOST='apt.hestiacp.com'
GPG='gpg.hestiacp.com'
VERSION='debian'
HESTIA='/usr/local/hestia'
memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])
arch=$(uname -i)
os='debian'
release=$(cat /etc/debian_version|grep -o [0-9]|head -n1)
codename="$(cat /etc/os-release |grep VERSION= |cut -f 2 -d \(|cut -f 1 -d \))"
hestiacp="$HESTIA/install/$VERSION/$release"

if [ "$release" -eq 9 ]; then
    software="nginx apache2 apache2-utils apache2-suexec-custom
        libapache2-mod-ruid2 libapache2-mod-fcgid libapache2-mod-php php
        php-common php-cgi php-mysql php-curl php-pgsql awstats webalizer
        vsftpd proftpd-basic bind9 exim4 exim4-daemon-heavy clamav-daemon 
        spamassassin dovecot-imapd dovecot-pop3d roundcube-core net-tools
        roundcube-mysql roundcube-plugins mysql-server mysql-common
        mysql-client postgresql postgresql-contrib phppgadmin phpmyadmin mc
        flex whois rssh git idn zip sudo bc ftp lsof ntpdate rrdtool quota
        e2fslibs bsdutils e2fsprogs curl imagemagick fail2ban dnsutils
        bsdmainutils cron hestia hestia-nginx hestia-php expect libmail-dkim-perl
        unrar-free vim-common"
else
    software="nginx apache2 apache2-utils apache2.2-common
        apache2-suexec-custom libapache2-mod-ruid2
        libapache2-mod-fcgid libapache2-mod-php5 php5 php5-common php5-cgi
        php5-mysql php5-curl php5-pgsql awstats webalizer vsftpd net-tools
        proftpd-basic bind9 exim4 exim4-daemon-heavy clamav-daemon
        spamassassin dovecot-imapd dovecot-pop3d roundcube-core
        roundcube-mysql roundcube-plugins mysql-server mysql-common
        mysql-client postgresql postgresql-contrib phppgadmin phpMyAdmin mc
        flex whois rssh git idn zip sudo bc ftp lsof ntpdate rrdtool quota
        e2fslibs bsdutils e2fsprogs curl imagemagick fail2ban dnsutils
        bsdmainutils cron hestia hestia-nginx hestia-php expect libmail-dkim-perl
        unrar-free vim-common"
fi

# Defining help function
help() {
    echo "Usage: $0 [OPTIONS]
  -a, --apache            Install Apache        [yes|no]  default: yes
  -n, --nginx             Install Nginx         [yes|no]  default: yes
  -w, --phpfpm            Install PHP-FPM       [yes|no]  default: no
  -o, --multiphp          Install Multi-PHP     [yes|no]  default: no
  -v, --vsftpd            Install Vsftpd        [yes|no]  default: yes
  -j, --proftpd           Install ProFTPD       [yes|no]  default: no
  -k, --named             Install Bind          [yes|no]  default: yes
  -m, --mysql             Install MySQL         [yes|no]  default: yes
  -g, --postgresql        Install PostgreSQL    [yes|no]  default: no
  -x, --exim              Install Exim          [yes|no]  default: yes
  -z, --dovecot           Install Dovecot       [yes|no]  default: yes
  -c, --clamav            Install ClamAV        [yes|no]  default: yes
  -t, --spamassassin      Install SpamAssassin  [yes|no]  default: yes
  -i, --iptables          Install Iptables      [yes|no]  default: yes
  -b, --fail2ban          Install Fail2ban      [yes|no]  default: yes
  -q, --quota             Filesystem Quota      [yes|no]  default: no
  -d, --api               Activate API          [yes|no]  default: yes
  -r, --port              Change Backend Port             default: 8083
  -l, --lang              Default language                default: en
  -y, --interactive       Interactive install   [yes|no]  default: yes
  -s, --hostname          Set hostname
  -e, --email             Set admin email
  -p, --password          Set admin password
  -f, --force             Force installation
  -h, --help              Print this help

  Example: bash $0 -e demo@hestiacp.com -p p4ssw0rd --apache no --phpfpm yes"
    exit 1
}


# Defining password-gen function
gen_pass() {
    MATRIX='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
    LENGTH=10
    while [ ${n:=1} -le $LENGTH ]; do
        PASS="$PASS${MATRIX:$(($RANDOM%${#MATRIX})):1}"
        let n+=1
    done
    echo "$PASS"
}

# Defning return code check function
check_result() {
    if [ $1 -ne 0 ]; then
        echo "Error: $2"
        exit $1
    fi
}

# Defining function to set default value
set_default_value() {
    eval variable=\$$1
    if [ -z "$variable" ]; then
        eval $1=$2
    fi
    if [ "$variable" != 'yes' ] && [ "$variable" != 'no' ]; then
        eval $1=$2
    fi
}

# Define function to set default language value
set_default_lang() {
    if [ -z "$lang" ]; then
        eval lang=$1
    fi
    lang_list="
        ar cz el fa hu ja no pt se ua
        bs da en fi id ka pl ro tr vi
        cn de es fr it nl pt-BR ru tw
        bg ko sr th ur"
    if !(echo $lang_list |grep -w $lang 1>&2>/dev/null); then
        eval lang=$1
    fi
}

# Define the default backend port
set_default_port() {
    if [ -z "$port" ]; then
        eval port=$1
    fi
}



#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

# Creating temporary file
tmpfile=$(mktemp -p /tmp)

# Translating argument to --gnu-long-options
for arg; do
    delim=""
    case "$arg" in
        --apache)               args="${args}-a " ;;
        --nginx)                args="${args}-n " ;;
        --phpfpm)               args="${args}-w " ;;
        --vsftpd)               args="${args}-v " ;;
        --proftpd)              args="${args}-j " ;;
        --named)                args="${args}-k " ;;
        --mysql)                args="${args}-m " ;;
        --postgresql)           args="${args}-g " ;;
        --exim)                 args="${args}-x " ;;
        --dovecot)              args="${args}-z " ;;
        --clamav)               args="${args}-c " ;;
        --spamassassin)         args="${args}-t " ;;
        --iptables)             args="${args}-i " ;;
        --fail2ban)             args="${args}-b " ;;
        --multiphp)             args="${args}-o " ;;
        --quota)                args="${args}-q " ;;
        --port)                 args="${args}-r " ;;
        --lang)                 args="${args}-l " ;;
        --interactive)          args="${args}-y " ;;
        --api)                  args="${args}-d " ;;
        --hostname)             args="${args}-s " ;;
        --email)                args="${args}-e " ;;
        --password)             args="${args}-p " ;;
        --force)                args="${args}-f " ;;
        --help)                 args="${args}-h " ;;
        *)                      [[ "${arg:0:1}" == "-" ]] || delim="\""
                                args="${args}${delim}${arg}${delim} ";;
    esac
done
eval set -- "$args"

# Parsing arguments
while getopts "a:n:w:v:j:k:m:g:d:x:z:c:t:i:b:r:o:q:l:y:s:e:p:fh" Option; do
    case $Option in
        a) apache=$OPTARG ;;            # Apache
        n) nginx=$OPTARG ;;             # Nginx
        w) phpfpm=$OPTARG ;;            # PHP-FPM
        o) multiphp=$OPTARG ;;          # Multi-PHP
        v) vsftpd=$OPTARG ;;            # Vsftpd
        j) proftpd=$OPTARG ;;           # Proftpd
        k) named=$OPTARG ;;             # Named
        m) mysql=$OPTARG ;;             # MySQL
        g) postgresql=$OPTARG ;;        # PostgreSQL
        x) exim=$OPTARG ;;              # Exim
        z) dovecot=$OPTARG ;;           # Dovecot
        c) clamd=$OPTARG ;;             # ClamAV
        t) spamd=$OPTARG ;;             # SpamAssassin
        i) iptables=$OPTARG ;;          # Iptables
        b) fail2ban=$OPTARG ;;          # Fail2ban
        q) quota=$OPTARG ;;             # FS Quota
        r) port=$OPTARG ;;              # Backend Port
        l) lang=$OPTARG ;;              # Language
        d) api=$OPTARG ;;               # Activate API
        y) interactive=$OPTARG ;;       # Interactive install
        s) servername=$OPTARG ;;        # Hostname
        e) email=$OPTARG ;;             # Admin email
        p) vpass=$OPTARG ;;             # Admin password
        f) force='yes' ;;               # Force install
        h) help ;;                      # Help
        *) help ;;                      # Print help (default)
    esac
done

# Defining default software stack
set_default_value 'nginx' 'yes'
set_default_value 'apache' 'yes'
set_default_value 'phpfpm' 'no'
set_default_value 'multiphp' 'no'
set_default_value 'vsftpd' 'yes'
set_default_value 'proftpd' 'no'
set_default_value 'named' 'yes'
set_default_value 'mysql' 'yes'
set_default_value 'postgresql' 'no'
set_default_value 'exim' 'yes'
set_default_value 'dovecot' 'yes'
if [ $memory -lt 1500000 ]; then
    set_default_value 'clamd' 'no'
    set_default_value 'spamd' 'no'
else
    set_default_value 'clamd' 'yes'
    set_default_value 'spamd' 'yes'
fi
set_default_value 'iptables' 'yes'
set_default_value 'fail2ban' 'yes'
set_default_value 'quota' 'no'
set_default_value 'api' 'yes'
set_default_value 'interactive' 'yes'
set_default_port '8083'
set_default_lang 'en'

# Checking software conflicts
if [ "$phpfpm" = 'yes' ]; then
    apache='no'
    nginx='yes'
fi
if [ "$multiphp" = 'yes' ]; then
    phpfpm='no'
fi
if [ "$proftpd" = 'yes' ]; then
    vsftpd='no'
fi
if [ "$exim" = 'no' ]; then
    clamd='no'
    spamd='no'
    dovecot='no'
fi
if [ "$iptables" = 'no' ]; then
    fail2ban='no'
fi

# Checking root permissions
if [ "x$(id -u)" != 'x0' ]; then
    check_error 1 "Script can be run executed only by root"
fi

# Checking admin user account
if [ ! -z "$(grep ^admin: /etc/passwd /etc/group)" ] && [ -z "$force" ]; then
    echo 'Please remove admin user account before proceeding.'
    echo 'If you want to do it automatically run installer with -f option:'
    echo -e "Example: bash $0 --force\n"
    check_result 1 "User admin exists"
fi

# Update apt repository
echo "Please wait a few seconds, we update your repository before we start the installation process..."
apt-get -qq update

# Checking wget
if [ ! -e '/usr/bin/wget' ]; then
    apt-get -y install wget
    check_result $? "Can't install wget"
fi

# Check if apt-transport-https is installed
if [ ! -e '/usr/lib/apt/methods/https' ]; then
    apt-get -y install apt-transport-https
    check_result $? "Can't install apt-transport-https"
fi

# Check if apparmor is installed
if [ $(dpkg-query -W -f='${Status}' apparmor 2>/dev/null | grep -c "ok installed") -eq 0 ]; then
    apparmor='no'
else
    apparmor='yes'
fi

# Checking repository availability
wget -q "https://$GPG/deb_signing.key" -O /dev/null
check_result $? "No access to Hestia repository"

# Check installed packages
tmpfile=$(mktemp -p /tmp)
dpkg --get-selections > $tmpfile
for pkg in exim4 mysql-server apache2 nginx hestia; do
    if [ ! -z "$(grep $pkg $tmpfile)" ]; then
        conflicts="$pkg $conflicts"
    fi
done
rm -f $tmpfile
if [ ! -z "$conflicts" ] && [ -z "$force" ]; then
    echo '!!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!!'
    echo
    echo 'Following packages are already installed:'
    echo "$conflicts"
    echo
    echo 'It is highly recommended to remove them before proceeding.'
    echo 'If you want to force installation run this script with -f option:'
    echo "Example: bash $0 --force"
    echo
    echo '!!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!!'
    echo
    check_result 1 "Control Panel should be installed on clean server."
fi


#----------------------------------------------------------#
#                       Brief Info                         #
#----------------------------------------------------------#

# Printing nice ASCII logo
clear
echo
echo '  _   _           _   _        ____ ____  '
echo ' | | | | ___  ___| |_(_) __ _ / ___|  _ \ '
echo ' | |_| |/ _ \/ __| __| |/ _` | |   | |_) |'
echo ' |  _  |  __/\__ \ |_| | (_| | |___|  __/ '
echo ' |_| |_|\___||___/\__|_|\__,_|\____|_|    '
echo
echo '                      Hestia Control Panel'
echo -e "\n\n"

echo 'Following software will be installed on your system:'

# Web stack
if [ "$nginx" = 'yes' ]; then
    echo '   - Nginx Web Server'
fi
if [ "$apache" = 'yes' ] && [ "$nginx" = 'no' ] ; then
    echo '   - Apache Web Server'
fi
if [ "$apache" = 'yes' ] && [ "$nginx"  = 'yes' ] ; then
    echo '   - Apache Web Server (as backend)'
fi
if [ "$phpfpm"  = 'yes' ]; then
    echo '   - PHP-FPM Application Server'
fi
if [ "$multiphp"  = 'yes' ]; then
    echo '   - Multi-PHP Environment'
fi

# DNS stack
if [ "$named" = 'yes' ]; then
    echo '   - Bind DNS Server'
fi

# Mail Stack
if [ "$exim" = 'yes' ]; then
    echo -n '   - Exim mail server'
    if [ "$clamd" = 'yes'  ] ||  [ "$spamd" = 'yes' ] ; then
        echo -n ' + '
        if [ "$clamd" = 'yes' ]; then
            echo -n 'Antivirus '
        fi
        if [ "$spamd" = 'yes' ]; then
            echo -n 'Antispam'
        fi
    fi
    echo
    if [ "$dovecot" = 'yes' ]; then
        echo '   - Dovecot POP3/IMAP Server'
    fi
fi

# DB stack
if [ "$mysql" = 'yes' ]; then
    echo '   - MySQL Database Server'
fi
if [ "$postgresql" = 'yes' ]; then
    echo '   - PostgreSQL Database Server'
fi
# FTP stack
if [ "$vsftpd" = 'yes' ]; then
    echo '   - Vsftpd FTP Server'
fi
if [ "$proftpd" = 'yes' ]; then
    echo '   - ProFTPD FTP Server'
fi

# Firewall stack
if [ "$iptables" = 'yes' ]; then
    echo -n '   - Iptables Firewall'
fi
if [ "$iptables" = 'yes' ] && [ "$fail2ban" = 'yes' ]; then
    echo -n ' + Fail2Ban'
fi
echo -e "\n\n"

# Asking for confirmation to proceed
if [ "$interactive" = 'yes' ]; then
    read -p 'Would you like to continue [y/n]: ' answer
    if [ "$answer" != 'y' ] && [ "$answer" != 'Y'  ]; then
        echo 'Goodbye'
        exit 1
    fi

    # Asking for contact email
    if [ -z "$email" ]; then
        read -p 'Please enter admin email address: ' email
    fi

    # Asking to set FQDN hostname
    if [ -z "$servername" ]; then
        read -p "Please enter FQDN hostname [$(hostname)]: " servername
    fi
fi

# Generating admin password if it wasn't set
if [ -z "$vpass" ]; then
    vpass=$(gen_pass)
fi

# Set hostname if it wasn't set
if [ -z "$servername" ]; then
    servername=$(hostname -f)
fi

# Set FQDN if it wasn't set
mask1='(([[:alnum:]](-?[[:alnum:]])*)\.)'
mask2='*[[:alnum:]](-?[[:alnum:]])+\.[[:alnum:]]{2,}'
if ! [[ "$servername" =~ ^${mask1}${mask2}$ ]]; then
    if [ ! -z "$servername" ]; then
        servername="$servername.example.com"
    else
        servername="example.com"
    fi
    echo "127.0.0.1 $servername" >> /etc/hosts
fi

# Set email if it wasn't set
if [ -z "$email" ]; then
    email="admin@$servername"
fi

# Defining backup directory
hst_backups="/root/hst_install_backups/$(date +%s)"
echo "Installation backup directory: $hst_backups"

# Printing start message and sleeping for 5 seconds
echo -e "\n\n\n\nInstallation will take about 15 minutes ...\n"
sleep 5


#----------------------------------------------------------#
#                      Checking swap                       #
#----------------------------------------------------------#

# Checking swap on small instances
if [ -z "$(swapon -s)" ] && [ $memory -lt 1000000 ]; then
    fallocate -l 1G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo "/swapfile   none    swap    sw    0   0" >> /etc/fstab
fi


#----------------------------------------------------------#
#                   Install repository                     #
#----------------------------------------------------------#

# Updating system
apt-get -y upgrade
check_result $? 'apt-get upgrade failed'

# Installing nginx repo
apt=/etc/apt/sources.list.d
echo "deb http://nginx.org/packages/debian/ $codename nginx" > $apt/nginx.list
wget http://nginx.org/keys/nginx_signing.key -O /tmp/nginx_signing.key
apt-key add /tmp/nginx_signing.key

if [ "$multiphp" = 'yes' ] || [ "$phpfpm" = 'yes' ]; then
    # Installing sury php repo
    echo "deb https://packages.sury.org/php/ $codename main" > $apt/php.list
    wget https://packages.sury.org/php/apt.gpg -O /tmp/php_signing.key
    apt-key add /tmp/php_signing.key
fi

# Installing hestia repo
echo "deb https://$RHOST/ $codename main" > $apt/hestia.list
wget https://gpg.hestiacp.com/deb_signing.key -O deb_signing.key
apt-key add deb_signing.key


#----------------------------------------------------------#
#                         Backup                           #
#----------------------------------------------------------#

# Creating backup directory tree
mkdir -p $hst_backups
cd $hst_backups
mkdir nginx apache2 php php5 php5-fpm vsftpd proftpd bind exim4 dovecot clamd
mkdir spamassassin mysql postgresql hestia

# Backing up Nginx configuration
service nginx stop > /dev/null 2>&1
cp -r /etc/nginx/* $hst_backups/nginx >/dev/null 2>&1

# Backing up Apache configuration
service apache2 stop > /dev/null 2>&1
cp -r /etc/apache2/* $hst_backups/apache2 > /dev/null 2>&1
rm -f /etc/apache2/conf.d/* > /dev/null 2>&1

# Backing up PHP configuration
cp /etc/php.ini $hst_backups/php > /dev/null 2>&1
cp -r /etc/php.d  $hst_backups/php > /dev/null 2>&1

# Backing up PHP configuration
service php5-fpm stop >/dev/null 2>&1
cp /etc/php5/* $hst_backups/php5 > /dev/null 2>&1
rm -f /etc/php5/fpm/pool.d/* >/dev/null 2>&1

# Backing up Bind configuration
service bind9 stop > /dev/null 2>&1
cp -r /etc/bind/* $hst_backups/bind > /dev/null 2>&1

# Backing up Vsftpd configuration
service vsftpd stop > /dev/null 2>&1
cp /etc/vsftpd.conf $hst_backups/vsftpd > /dev/null 2>&1

# Backing up ProFTPD configuration
service proftpd stop > /dev/null 2>&1
cp /etc/proftpd.conf $hst_backups/proftpd >/dev/null 2>&1

# Backing up Exim configuration
service exim4 stop > /dev/null 2>&1
cp -r /etc/exim4/* $hst_backups/exim4 > /dev/null 2>&1

# Backing up ClamAV configuration
service clamav-daemon stop > /dev/null 2>&1
cp -r /etc/clamav/* $hst_backups/clamav > /dev/null 2>&1

# Backing up SpamAssassin configuration
service spamassassin stop > /dev/null 2>&1
cp -r /etc/spamassassin/* $hst_backups/spamassassin > /dev/null 2>&1

# Backing up Dovecot configuration
service dovecot stop > /dev/null 2>&1
cp /etc/dovecot.conf $hst_backups/dovecot > /dev/null 2>&1
cp -r /etc/dovecot/* $hst_backups/dovecot > /dev/null 2>&1

# Backing up MySQL/MariaDB configuration and data
service mysql stop > /dev/null 2>&1
killall -9 mysqld > /dev/null 2>&1
mv /var/lib/mysql $hst_backups/mysql/mysql_datadir > /dev/null 2>&1
cp -r /etc/mysql/* $hst_backups/mysql > /dev/null 2>&1
mv -f /root/.my.cnf $hst_backups/mysql > /dev/null 2>&1

# Backup Hestia
service hestia stop > /dev/null 2>&1
cp -r $HESTIA/* $hst_backups/hestia > /dev/null 2>&1
apt-get -y remove hestia hestia-nginx hestia-php > /dev/null 2>&1
apt-get -y purge hestia hestia-nginx hestia-php > /dev/null 2>&1
rm -rf $HESTIA > /dev/null 2>&1


#----------------------------------------------------------#
#                     Package Excludes                     #
#----------------------------------------------------------#

# Excluding packages
if [ "$nginx" = 'no'  ]; then
    software=$(echo "$software" | sed -e "s/^nginx//")
fi
if [ "$apache" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/apache2 //")
    software=$(echo "$software" | sed -e "s/apache2-utils//")
    software=$(echo "$software" | sed -e "s/apache2-suexec-custom//")
    software=$(echo "$software" | sed -e "s/apache2.2-common//")
    software=$(echo "$software" | sed -e "s/libapache2-mod-ruid2//")
    software=$(echo "$software" | sed -e "s/libapache2-mod-fcgid//")
    software=$(echo "$software" | sed -e "s/libapache2-mod-php5//")
    software=$(echo "$software" | sed -e "s/libapache2-mod-php//")
fi
if [ "$phpfpm" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/php5-fpm//")
    software=$(echo "$software" | sed -e "s/php-fpm//")
fi
if [ "$vsftpd" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/vsftpd//")
fi
if [ "$proftpd" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/proftpd-basic//")
    software=$(echo "$software" | sed -e "s/proftpd-mod-vroot//")
fi
if [ "$named" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/bind9//")
fi
if [ "$exim" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/exim4 //")
    software=$(echo "$software" | sed -e "s/exim4-daemon-heavy//")
    software=$(echo "$software" | sed -e "s/dovecot-imapd//")
    software=$(echo "$software" | sed -e "s/dovecot-pop3d//")
    software=$(echo "$software" | sed -e "s/clamav-daemon//")
    software=$(echo "$software" | sed -e "s/spamassassin//")
fi
if [ "$clamd" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/clamav-daemon//")
fi
if [ "$spamd" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/spamassassin//")
    software=$(echo "$software" | sed -e "s/libmail-dkim-perl//")
fi
if [ "$dovecot" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/dovecot-imapd//")
    software=$(echo "$software" | sed -e "s/dovecot-pop3d//")
fi
if [ "$mysql" = 'no' ]; then
    software=$(echo "$software" | sed -e 's/mysql-server//')
    software=$(echo "$software" | sed -e 's/mysql-client//')
    software=$(echo "$software" | sed -e 's/mysql-common//')
    software=$(echo "$software" | sed -e 's/php5-mysql//')
    software=$(echo "$software" | sed -e 's/php-mysql//')
    software=$(echo "$software" | sed -e 's/phpMyAdmin//')
fi
if [ "$postgresql" = 'no' ]; then
    software=$(echo "$software" | sed -e 's/postgresql-contrib//')
    software=$(echo "$software" | sed -e 's/postgresql//')
    software=$(echo "$software" | sed -e 's/php5-pgsql//')
    software=$(echo "$software" | sed -e 's/php-pgsql//')
    software=$(echo "$software" | sed -e 's/phppgadmin//')
fi
if [ "$iptables" = 'no' ] || [ "$fail2ban" = 'no' ]; then
    software=$(echo "$software" | sed -e 's/fail2ban//')
fi

#----------------------------------------------------------#
#                     Package Includes                     #
#----------------------------------------------------------#

if [ "$multiphp" = 'yes' ]; then
    mph="php5.6-apcu php5.6-mbstring php5.6-bcmath php5.6-cli php5.6-curl
         php5.6-fpm php5.6-gd php5.6-intl php5.6-mcrypt php5.6-mysql
         php5.6-soap php5.6-xml php5.6-zip php5.6-zip php7.0-mbstring
         php7.0-bcmath php7.0-cli php7.0-curl php7.0-fpm php7.0-gd
         php7.0-intl php7.0-mcrypt php7.0-mysql php7.0-soap php7.0-xml
         php7.0-zip php7.0-zip php7.1-mbstring php7.1-bcmath php7.1-cli
         php7.1-curl php7.1-fpm php7.1-gd php7.1-intl php7.1-mcrypt
         php7.1-mysql php7.1-soap php7.1-xml php7.1-zip php7.1-zip 
         php7.2-mbstring php7.2-bcmath php7.2-cli php7.2-curl php7.2-fpm
         php7.2-gd php7.2-intl php7.2-mysql php7.2-soap php7.2-xml
         php7.2-zip"
    software="$software $mph"
fi

if [ "$phpfpm" = 'yes' ]; then
    fpm="php7.2-mbstring php7.2-bcmath php7.2-cli php7.2-curl php7.2-fpm
         php7.2-gd php7.2-intl php7.2-mysql php7.2-soap php7.2-xml
         php7.2-zip"
    software="$software $fpm"
fi

#----------------------------------------------------------#
#                     Install packages                     #
#----------------------------------------------------------#

# Update system packages
apt-get update

# Disable daemon autostart /usr/share/doc/sysv-rc/README.policy-rc.d.gz
echo -e '#!/bin/sh \nexit 101' > /usr/sbin/policy-rc.d
chmod a+x /usr/sbin/policy-rc.d

# Install apt packages
apt-get -y install $software
check_result $? "apt-get install failed"

# Restore  policy
rm -f /usr/sbin/policy-rc.d


#----------------------------------------------------------#
#                     Configure system                     #
#----------------------------------------------------------#

# Enable SSH password auth
sed -i "s/rdAuthentication no/rdAuthentication yes/g" /etc/ssh/sshd_config
service ssh restart

# Disable awstats cron
rm -f /etc/cron.d/awstats

# Set directory color
echo 'LS_COLORS="$LS_COLORS:di=00;33"' >> /etc/profile

# Register /sbin/nologin and /usr/sbin/nologin
echo "/sbin/nologin" >> /etc/shells
echo "/usr/sbin/nologin" >> /etc/shells

# NTP Synchronization
echo '#!/bin/sh' > /etc/cron.daily/ntpdate
echo "$(which ntpdate) -s pool.ntp.org" >> /etc/cron.daily/ntpdate
chmod 775 /etc/cron.daily/ntpdate
ntpdate -s pool.ntp.org

# Setup rssh
if [ -z "$(grep /usr/bin/rssh /etc/shells)" ]; then
    echo /usr/bin/rssh >> /etc/shells
fi
sed -i 's/#allowscp/allowscp/' /etc/rssh.conf
sed -i 's/#allowsftp/allowsftp/' /etc/rssh.conf
sed -i 's/#allowrsync/allowrsync/' /etc/rssh.conf
chmod 755 /usr/bin/rssh


#----------------------------------------------------------#
#                     Configure Hestia                     #
#----------------------------------------------------------#

# Installing sudo configuration
mkdir -p /etc/sudoers.d
cp -f $hestiacp/sudo/admin /etc/sudoers.d/
chmod 440 /etc/sudoers.d/admin

# Configuring system env
echo "export HESTIA='$HESTIA'" > /etc/profile.d/hestia.sh
echo "export VESTA='$HESTIA'" >> /etc/profile.d/hestia.sh #Compatiblity to vesta
chmod 755 /etc/profile.d/hestia.sh
source /etc/profile.d/hestia.sh
echo 'PATH=$PATH:'$HESTIA'/bin' >> /root/.bash_profile
echo 'export PATH' >> /root/.bash_profile
source /root/.bash_profile

# Configuring logrotate for Hestia logs
cp -f $hestiacp/logrotate/hestia /etc/logrotate.d/hestia

# Building directory tree and creating some blank files for hestia
mkdir -p $HESTIA/conf $HESTIA/log $HESTIA/ssl $HESTIA/data/ips \
    $HESTIA/data/queue $HESTIA/data/users $HESTIA/data/firewall \
    $HESTIA/data/sessions
touch $HESTIA/data/queue/backup.pipe $HESTIA/data/queue/disk.pipe \
    $HESTIA/data/queue/webstats.pipe $HESTIA/data/queue/restart.pipe \
    $HESTIA/data/queue/traffic.pipe $HESTIA/log/system.log \
    $HESTIA/log/nginx-error.log $HESTIA/log/auth.log
chmod 750 $HESTIA/conf $HESTIA/data/users $HESTIA/data/ips $HESTIA/log
chmod -R 750 $HESTIA/data/queue
chmod 660 $HESTIA/log/*
rm -f /var/log/hestia
ln -s $HESTIA/log /var/log/hestia
chown admin:admin $HESTIA/data/sessions
chmod 770 $HESTIA/data/sessions

# Generating hestia configuration
rm -f $HESTIA/conf/hestia.conf 2>/dev/null
touch $HESTIA/conf/hestia.conf
chmod 660 $HESTIA/conf/hestia.conf

# Symlink to Hestia for compatibilty
ln -s $HESTIA /usr/local/vesta
ln -s $HESTIA/conf/hestia.conf /usr/local/vesta/conf/vesta.conf

# WEB stack
if [ "$apache" = 'yes' ] && [ "$nginx" = 'no' ] ; then
    echo "WEB_SYSTEM='apache2'" >> $HESTIA/conf/hestia.conf
    echo "WEB_RGROUPS='www-data'" >> $HESTIA/conf/hestia.conf
    echo "WEB_PORT='80'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL_PORT='443'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL='mod_ssl'"  >> $HESTIA/conf/hestia.conf
    echo "STATS_SYSTEM='webalizer,awstats'" >> $HESTIA/conf/hestia.conf
fi
if [ "$apache" = 'yes' ] && [ "$nginx"  = 'yes' ] ; then
    echo "WEB_SYSTEM='apache2'" >> $HESTIA/conf/hestia.conf
    echo "WEB_RGROUPS='www-data'" >> $HESTIA/conf/hestia.conf
    echo "WEB_PORT='8080'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL_PORT='8443'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL='mod_ssl'"  >> $HESTIA/conf/hestia.conf
    echo "PROXY_SYSTEM='nginx'" >> $HESTIA/conf/hestia.conf
    echo "PROXY_PORT='80'" >> $HESTIA/conf/hestia.conf
    echo "PROXY_SSL_PORT='443'" >> $HESTIA/conf/hestia.conf
    echo "STATS_SYSTEM='webalizer,awstats'" >> $HESTIA/conf/hestia.conf
fi
if [ "$apache" = 'no' ] && [ "$nginx"  = 'yes' ]; then
    echo "WEB_SYSTEM='nginx'" >> $HESTIA/conf/hestia.conf
    echo "WEB_PORT='80'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL_PORT='443'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL='openssl'"  >> $HESTIA/conf/hestia.conf
    if [ "$release" -eq 9 ]; then
        if [ "$phpfpm" = 'yes' ]; then
            echo "WEB_BACKEND='php-fpm'" >> $HESTIA/conf/hestia.conf
        fi
    else
        if [ "$phpfpm" = 'yes' ]; then
            echo "WEB_BACKEND='php5-fpm'" >> $HESTIA/conf/hestia.conf
        fi
    fi
    echo "STATS_SYSTEM='webalizer,awstats'" >> $HESTIA/conf/hestia.conf
fi

# FTP stack
if [ "$vsftpd" = 'yes' ]; then
    echo "FTP_SYSTEM='vsftpd'" >> $HESTIA/conf/hestia.conf
fi
if [ "$proftpd" = 'yes' ]; then
    echo "FTP_SYSTEM='proftpd'" >> $HESTIA/conf/hestia.conf
fi

# DNS stack
if [ "$named" = 'yes' ]; then
    echo "DNS_SYSTEM='bind9'" >> $HESTIA/conf/hestia.conf
fi

# Mail stack
if [ "$exim" = 'yes' ]; then
    echo "MAIL_SYSTEM='exim4'" >> $HESTIA/conf/hestia.conf
    if [ "$clamd" = 'yes'  ]; then
        echo "ANTIVIRUS_SYSTEM='clamav-daemon'" >> $HESTIA/conf/hestia.conf
    fi
    if [ "$spamd" = 'yes' ]; then
        echo "ANTISPAM_SYSTEM='spamassassin'" >> $HESTIA/conf/hestia.conf
    fi
    if [ "$dovecot" = 'yes' ]; then
        echo "IMAP_SYSTEM='dovecot'" >> $HESTIA/conf/hestia.conf
    fi
fi

# CRON daemon
echo "CRON_SYSTEM='cron'" >> $HESTIA/conf/hestia.conf

# Firewall stack
if [ "$iptables" = 'yes' ]; then
    echo "FIREWALL_SYSTEM='iptables'" >> $HESTIA/conf/hestia.conf
fi
if [ "$iptables" = 'yes' ] && [ "$fail2ban" = 'yes' ]; then
    echo "FIREWALL_EXTENSION='fail2ban'" >> $HESTIA/conf/hestia.conf
fi

# Disk quota
if [ "$quota" = 'yes' ]; then
    echo "DISK_QUOTA='yes'" >> $HESTIA/conf/hestia.conf
fi

# Backups
echo "BACKUP_SYSTEM='local'" >> $HESTIA/conf/hestia.conf

# Language
echo "LANGUAGE='$lang'" >> $HESTIA/conf/hestia.conf

# Version
echo "VERSION='0.9.8'" >> $HESTIA/conf/hestia.conf

# Installing hosting packages
cp -rf $hestiacp/packages $HESTIA/data/

# Installing templates
cp -rf $hestiacp/templates $HESTIA/data/

# Copying index.html to default documentroot
cp $HESTIA/data/templates/web/skel/public_html/index.html /var/www/
sed -i 's/%domain%/It worked!/g' /var/www/index.html

# Installing firewall rules
cp -rf $hestiacp/firewall $HESTIA/data/

# Configuring server hostname
$HESTIA/bin/v-change-sys-hostname $servername 2>/dev/null

# Generating SSL certificate
$HESTIA/bin/v-generate-ssl-cert $(hostname) $email 'US' 'California' \
     'San Francisco' 'Hestia Control Panel' 'IT' > /tmp/hst.pem

# Parsing certificate file
crt_end=$(grep -n "END CERTIFICATE-" /tmp/hst.pem |cut -f 1 -d:)
key_start=$(grep -n "BEGIN RSA" /tmp/hst.pem |cut -f 1 -d:)
key_end=$(grep -n  "END RSA" /tmp/hst.pem |cut -f 1 -d:)

# Adding SSL certificate
cd $HESTIA/ssl
sed -n "1,${crt_end}p" /tmp/hst.pem > certificate.crt
sed -n "$key_start,${key_end}p" /tmp/hst.pem > certificate.key
chown root:mail $HESTIA/ssl/*
chmod 660 $HESTIA/ssl/*
rm /tmp/hst.pem


#----------------------------------------------------------#
#                     Configure Nginx                      #
#----------------------------------------------------------#

if [ "$nginx" = 'yes' ]; then
    rm -f /etc/nginx/conf.d/*.conf
    cp -f $hestiacp/nginx/nginx.conf /etc/nginx/
    cp -f $hestiacp/nginx/status.conf /etc/nginx/conf.d/
    cp -f $hestiacp/nginx/phpmyadmin.inc /etc/nginx/conf.d/
    cp -f $hestiacp/nginx/phppgadmin.inc /etc/nginx/conf.d/
    cp -f $hestiacp/nginx/webmail.inc /etc/nginx/conf.d/
    cp -f $hestiacp/logrotate/nginx /etc/logrotate.d/
    echo > /etc/nginx/conf.d/hestia.conf
    mkdir -p /var/log/nginx/domains
    if [ "$apache" = 'no' ] && [ "$multiphp" = 'yes' ] && [ "$phpfpm" = 'no' ]; then
        update-rc.d php5.6-fpm defaults
        update-rc.d php7.0-fpm defaults
        update-rc.d php7.1-fpm defaults
        update-rc.d php7.2-fpm defaults
        cp -r /etc/php/5.6/ /root/hst_install_backups/php5.6/
        rm -f /etc/php/5.6/fpm/pool.d/*
        cp -r /etc/php/7.0/ /root/hst_install_backups/php7.0/
        rm -f /etc/php/7.0/fpm/pool.d/*
        cp -r /etc/php/7.1/ /root/hst_install_backups/php7.1/
        rm -f /etc/php/7.1/fpm/pool.d/*
        cp -r /etc/php/7.2/ /root/hst_install_backups/php7.2/
        rm -f /etc/php/7.2/fpm/pool.d/*
        rm -fr $HESTIA/data/templates/web/nginx/*
        cp -f $hestiacp/multiphp/nginx/* $HESTIA/data/templates/web/nginx/
        cp -f $hestiacp/php-fpm/www.conf /etc/php/7.2/fpm/pool.d/
        ln -s $HESTIA/data/templates/web/nginx/PHP-72.sh $HESTIA/data/templates/web/nginx/default.sh
        ln -s $HESTIA/data/templates/web/nginx/PHP-72.tpl $HESTIA/data/templates/web/nginx/default.tpl
        ln -s $HESTIA/data/templates/web/nginx/PHP-72.stpl $HESTIA/data/templates/web/nginx/default.stpl
        chmod a+x $HESTIA/data/templates/web/nginx/*.sh
        service php7.2-fpm start
        check_result $? "php7.2-fpm start failed"
    fi
    update-rc.d nginx defaults
    service nginx start
    check_result $? "nginx start failed"
fi


#----------------------------------------------------------#
#                    Configure Apache                      #
#----------------------------------------------------------#

if [ "$apache" = 'yes' ]; then
    cp -f $hestiacp/apache2/apache2.conf /etc/apache2/
    cp -f $hestiacp/apache2/status.conf /etc/apache2/mods-enabled/
    cp -f  $hestiacp/logrotate/apache2 /etc/logrotate.d/
    a2enmod rewrite
    a2enmod suexec
    a2enmod ssl
    a2enmod actions
    a2enmod ruid2
    a2enmod headers
    mkdir -p /etc/apache2/conf.d
    echo > /etc/apache2/conf.d/hestia.conf
    echo "# Powered by hestia" > /etc/apache2/sites-available/default
    echo "# Powered by hestia" > /etc/apache2/sites-available/default-ssl
    echo "# Powered by hestia" > /etc/apache2/ports.conf
    echo -e "/home\npublic_html/cgi-bin" > /etc/apache2/suexec/www-data
    touch /var/log/apache2/access.log /var/log/apache2/error.log
    mkdir -p /var/log/apache2/domains
    chmod a+x /var/log/apache2
    chmod 640 /var/log/apache2/access.log /var/log/apache2/error.log
    chmod 751 /var/log/apache2/domains
    if [ "$multiphp" = 'yes' ]; then
        a2enmod proxy_fcgi setenvif
        a2enconf php5.6-fpm
        a2enconf php7.0-fpm
        a2enconf php7.1-fpm
        a2enconf php7.2-fpm
        update-rc.d php5.6-fpm defaults
        update-rc.d php7.0-fpm defaults
        update-rc.d php7.1-fpm defaults
        update-rc.d php7.2-fpm defaults
        cp -r /etc/php/5.6/ /root/hst_install_backups/php5.6/
        rm -f /etc/php/5.6/fpm/pool.d/*
        cp -r /etc/php/7.0/ /root/hst_install_backups/php7.0/
        rm -f /etc/php/7.0/fpm/pool.d/*
        cp -r /etc/php/7.1/ /root/hst_install_backups/php7.1/
        rm -f /etc/php/7.1/fpm/pool.d/*
        cp -r /etc/php/7.2/ /root/hst_install_backups/php7.2/
        rm -f /etc/php/7.2/fpm/pool.d/*
        cp -f $hestiacp/multiphp/apache2/* $HESTIA/data/templates/web/apache2/
        chmod a+x $HESTIA/data/templates/web/apache2/*.sh
        if [ "$release" = '8' ]; then
            sed -i 's/#//g' $HESTIA/data/templates/web/apache2/*.tpl
            sed -i 's/#//g' $HESTIA/data/templates/web/apache2/*.stpl
        fi
    fi
    update-rc.d apache2 defaults
    service apache2 start
    check_result $? "apache2 start failed"
else
    update-rc.d apache2 disable >/dev/null 2>&1
    service apache2 stop >/dev/null 2>&1
fi


#----------------------------------------------------------#
#                     Configure PHP-FPM                    #
#----------------------------------------------------------#

if [ "$phpfpm" = 'yes' ]; then
    cp -f $hestiacp/php-fpm/www.conf /etc/php/7.2/fpm/pool.d/www.conf
    update-rc.d php7.2-fpm defaults
    service php7.2-fpm start
    check_result $? "php-fpm start failed"
fi


#----------------------------------------------------------#
#                     Configure PHP                        #
#----------------------------------------------------------#

ZONE=$(timedatectl 2>/dev/null|grep Timezone|awk '{print $2}')
if [ -z "$ZONE" ]; then
    ZONE='UTC'
fi
for pconf in $(find /etc/php* -name php.ini); do
    sed -i "s/;date.timezone =/date.timezone = $ZONE/g" $pconf
    sed -i 's%_open_tag = Off%_open_tag = On%g' $pconf
done


#----------------------------------------------------------#
#                    Configure VSFTPD                      #
#----------------------------------------------------------#

if [ "$vsftpd" = 'yes' ]; then
    cp -f $hestiacp/vsftpd/vsftpd.conf /etc/
    update-rc.d vsftpd defaults
    service vsftpd start
    check_result $? "vsftpd start failed"

    # To be deleted after release 0.9.8-18
    echo "/sbin/nologin" >> /etc/shells
fi


#----------------------------------------------------------#
#                    Configure ProFTPD                     #
#----------------------------------------------------------#

if [ "$proftpd" = 'yes' ]; then
    echo "127.0.0.1 $servername" >> /etc/hosts
    cp -f $hestiacp/proftpd/proftpd.conf /etc/proftpd/
    update-rc.d proftpd defaults
    service proftpd start
    check_result $? "proftpd start failed"
fi


#----------------------------------------------------------#
#                  Configure MySQL/MariaDB                 #
#----------------------------------------------------------#

if [ "$mysql" = 'yes' ]; then
    mycnf="my-small.cnf"
    if [ $memory -gt 1200000 ]; then
        mycnf="my-medium.cnf"
    fi
    if [ $memory -gt 3900000 ]; then
        mycnf="my-large.cnf"
    fi

    # MySQL configuration
    cp -f $hestiacp/mysql/$mycnf /etc/mysql/my.cnf
    mysql_install_db
    update-rc.d mysql defaults
    service mysql start
    check_result $? "mysql start failed"

    # Securing MySQL installation
    mpass=$(gen_pass)
    mysqladmin -u root password $mpass
    echo -e "[client]\npassword='$mpass'\n" > /root/.my.cnf
    chmod 600 /root/.my.cnf
    if [ "$release" = '8' ]; then
        mysql -e "DELETE FROM mysql.user WHERE User=''"
        mysql -e "DROP DATABASE test" >/dev/null 2>&1
        mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%'"
        mysql -e "DELETE FROM mysql.user WHERE user='' or password='';"
        mysql -e "FLUSH PRIVILEGES"
    fi
    if [ "$release" = '9' ]; then
        mysql -e "DELETE FROM mysql.user WHERE User=''"
        mysql -e "DROP DATABASE test" >/dev/null 2>&1
        mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%'"
        mysql -e "DELETE FROM mysql.user WHERE user='';"
        mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$mpass';"
        mysql -e "FLUSH PRIVILEGES"
    fi

    # Configuring phpMyAdmin
    if [ "$apache" = 'yes' ]; then
        cp -f $hestiacp/pma/apache.conf /etc/phpmyadmin/
        ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf.d/phpmyadmin.conf
    fi
    cp -f $hestiacp/pma/config.inc.php /etc/phpmyadmin/
    chmod 777 /var/lib/phpmyadmin/tmp
fi

#----------------------------------------------------------#
#                   Configure PostgreSQL                   #
#----------------------------------------------------------#

if [ "$postgresql" = 'yes' ]; then
    ppass=$(gen_pass)
    cp -f $hestiacp/postgresql/pg_hba.conf /etc/postgresql/*/main/
    service postgresql restart
    sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD '$ppass'"

    # Configuring phpPgAdmin
    if [ "$apache" = 'yes' ]; then
        cp -f $hestiacp/pga/phppgadmin.conf /etc/apache2/conf.d/
    fi
    cp -f $hestiacp/pga/config.inc.php /etc/phppgadmin/
fi


#----------------------------------------------------------#
#                      Configure Bind                      #
#----------------------------------------------------------#

if [ "$named" = 'yes' ]; then
    cp -f $hestiacp/bind/named.conf /etc/bind/
    sed -i "s%listen-on%//listen%" /etc/bind/named.conf.options
    chown root:bind /etc/bind/named.conf
    chmod 640 /etc/bind/named.conf
    aa-complain /usr/sbin/named 2>/dev/null
    if [ "$apparmor" = 'yes' ]; then
        echo "/home/** rwm," >> /etc/apparmor.d/local/usr.sbin.named 2>/dev/null
        service apparmor status >/dev/null 2>&1
        if [ $? -ne 0 ]; then
            service apparmor restart
        fi
    fi
    update-rc.d bind9 defaults
    service bind9 start
    check_result $? "bind9 start failed"
fi

#----------------------------------------------------------#
#                      Configure Exim                      #
#----------------------------------------------------------#

if [ "$exim" = 'yes' ]; then
    gpasswd -a Debian-exim mail
    cp -f $hestiacp/exim/exim4.conf.template /etc/exim4/
    cp -f $hestiacp/exim/dnsbl.conf /etc/exim4/
    cp -f $hestiacp/exim/spam-blocks.conf /etc/exim4/
    touch /etc/exim4/white-blocks.conf

    if [ "$spamd" = 'yes' ]; then
        sed -i "s/#SPAM/SPAM/g" /etc/exim4/exim4.conf.template
    fi
    if [ "$clamd" = 'yes' ]; then
        sed -i "s/#CLAMD/CLAMD/g" /etc/exim4/exim4.conf.template
    fi

    chmod 640 /etc/exim4/exim4.conf.template
    rm -rf /etc/exim4/domains
    mkdir -p /etc/exim4/domains

    rm -f /etc/alternatives/mta
    ln -s /usr/sbin/exim4 /etc/alternatives/mta
    update-rc.d -f sendmail remove > /dev/null 2>&1
    service sendmail stop > /dev/null 2>&1
    update-rc.d -f postfix remove > /dev/null 2>&1
    service postfix stop > /dev/null 2>&1

    update-rc.d exim4 defaults
    service exim4 start
    check_result $? "exim4 start failed"
fi


#----------------------------------------------------------#
#                     Configure Dovecot                    #
#----------------------------------------------------------#

if [ "$dovecot" = 'yes' ]; then
    gpasswd -a dovecot mail
    cp -rf $hestiacp/dovecot /etc/
    cp -f $hestiacp/logrotate/dovecot /etc/logrotate.d/
    chown -R root:root /etc/dovecot*
    if [ "$release" -eq 9 ]; then
        sed -i "s#namespace inbox {#namespace inbox {\n  inbox = yes#" /etc/dovecot/conf.d/15-mailboxes.conf
    fi
    update-rc.d dovecot defaults
    service dovecot start
    check_result $? "dovecot start failed"
fi


#----------------------------------------------------------#
#                     Configure ClamAV                     #
#----------------------------------------------------------#

if [ "$clamd" = 'yes' ]; then
    gpasswd -a clamav mail
    gpasswd -a clamav Debian-exim
    cp -f $hestiacp/clamav/clamd.conf /etc/clamav/
    update-rc.d clamav-daemon defaults
    if [ ! -d "/var/run/clamav" ]; then
        mkdir /var/run/clamav
    fi
    chown -R clamav:clamav /var/run/clamav
    if [ -e "/lib/systemd/system/clamav-daemon.service" ]; then
        exec_pre1='ExecStartPre=-/bin/mkdir -p /var/run/clamav'
        exec_pre2='ExecStartPre=-/bin/chown -R clamav:clamav /var/run/clamav'
        sed -i "s|\[Service\]/|[Service]\n$exec_pre1\n$exec_pre2|g" \
            /lib/systemd/system/clamav-daemon.service
        systemctl daemon-reload
    fi
    service clamav-daemon start
    echo "Updating ClamAV..."
    /usr/bin/freshclam  > /dev/null 2>&1
    service clamav-daemon restart
    check_result $? "clamav-daeom start failed"
fi


#----------------------------------------------------------#
#                  Configure SpamAssassin                  #
#----------------------------------------------------------#

if [ "$spamd" = 'yes' ]; then
    update-rc.d spamassassin defaults
    sed -i "s/ENABLED=0/ENABLED=1/" /etc/default/spamassassin
    service spamassassin start
    check_result $? "spamassassin start failed"
    unit_files="$(systemctl list-unit-files |grep spamassassin)"
    if [[ "$unit_files" =~ "disabled" ]]; then
        systemctl enable spamassassin
    fi
fi


#----------------------------------------------------------#
#                   Configure RoundCube                    #
#----------------------------------------------------------#

if [ "$exim" = 'yes' ] && [ "$mysql" = 'yes' ]; then
    if [ "$apache" = 'yes' ]; then
        cp -f $hestiacp/roundcube/apache.conf /etc/roundcube/
        ln -s /etc/roundcube/apache.conf /etc/apache2/conf.d/roundcube.conf
    fi
    cp -f $hestiacp/roundcube/main.inc.php /etc/roundcube/
    cp -f  $hestiacp/roundcube/db.inc.php /etc/roundcube/
    chmod 640 /etc/roundcube/debian-db-roundcube.php
    chmod 640 /etc/roundcube/config.inc.php
    chown root:www-data /etc/roundcube/debian-db-roundcube.php
    chown root:www-data /etc/roundcube/config.inc.php
    cp -f $hestiacp/roundcube/hestia.php \
        /usr/share/roundcube/plugins/password/drivers/
    cp -f $hestiacp/roundcube/config.inc.php /etc/roundcube/plugins/password/
    r="$(gen_pass)"
    mysql -e "CREATE DATABASE roundcube"
    mysql -e "GRANT ALL ON roundcube.* 
        TO roundcube@localhost IDENTIFIED BY '$r'"
    sed -i "s/%password%/$r/g" /etc/roundcube/db.inc.php
    sed -i "s/localhost/$servername/g" \
        /etc/roundcube/plugins/password/config.inc.php
    mysql roundcube < /usr/share/dbconfig-common/data/roundcube/install/mysql
    chmod a+r /etc/roundcube/main.inc.php
    if [ "$release" -eq 8 ] || [ "$release" -eq 9 ]; then
        mv -f /etc/roundcube/main.inc.php /etc/roundcube/config.inc.php
        mv -f /etc/roundcube/db.inc.php /etc/roundcube/debian-db-roundcube.php
        chmod 640 /etc/roundcube/debian-db-roundcube.php
        chmod 640 /etc/roundcube/config.inc.php
        chown root:www-data /etc/roundcube/debian-db-roundcube.php
        chown root:www-data /etc/roundcube/config.inc.php
    fi
    if [ "$release" -eq 8 ]; then
        # RoundCube tinyMCE fix
        tinymceFixArchiveURL=$hestiacp/roundcube/roundcube-tinymce.tar.gz
        tinymceParentFolder=/usr/share/roundcube/program/js
        tinymceFolder=$tinymceParentFolder/tinymce
        tinymceBadJS=$tinymceFolder/tiny_mce.js
        tinymceFixArchive=$tinymceParentFolder/roundcube-tinymce.tar.gz
        if [[ -L "$tinymceFolder" && -d "$tinymceFolder" ]]; then
            if [ -f "$tinymceBadJS" ]; then
                wget $tinymceFixArchiveURL -O $tinymceFixArchive
                if [[ -f "$tinymceFixArchive" && -s "$tinymceFixArchive" ]]
                then
                    rm $tinymceFolder
                    tar -xzf $tinymceFixArchive -C $tinymceParentFolder
                    rm $tinymceFixArchive
                    chown -R root:root $tinymceFolder
                else
                    echo -n "File roundcube-tinymce.tar.gz is not downloaded,"
                    echo "RoundCube tinyMCE fix is not applied"
                    rm $tinymceFixArchive
                fi
            fi
        fi

    fi
fi


#----------------------------------------------------------#
#                    Configure Fail2Ban                    #
#----------------------------------------------------------#

if [ "$fail2ban" = 'yes' ]; then
    cp -rf $hestiacp/fail2ban /etc/
    if [ "$dovecot" = 'no' ]; then
        fline=$(cat /etc/fail2ban/jail.local |grep -n dovecot-iptables -A 2)
        fline=$(echo "$fline" |grep enabled |tail -n1 |cut -f 1 -d -)
        sed -i "${fline}s/true/false/" /etc/fail2ban/jail.local
    fi
    if [ "$exim" = 'no' ]; then
        fline=$(cat /etc/fail2ban/jail.local |grep -n exim-iptables -A 2)
        fline=$(echo "$fline" |grep enabled |tail -n1 |cut -f 1 -d -)
        sed -i "${fline}s/true/false/" /etc/fail2ban/jail.local
    fi
    if [ "$vsftpd" = 'yes' ]; then
        #Create vsftpd Log File
        if [ ! -f "/var/log/vsftpd.log" ]; then
            touch /var/log/vsftpd.log
        fi
        fline=$(cat /etc/fail2ban/jail.local |grep -n vsftpd-iptables -A 2)
        fline=$(echo "$fline" |grep enabled |tail -n1 |cut -f 1 -d -)
        sed -i "${fline}s/false/true/" /etc/fail2ban/jail.local
    fi 
    update-rc.d fail2ban defaults
    service fail2ban start
    check_result $? "fail2ban start failed"
fi


#----------------------------------------------------------#
#                       Configure API                      #
#----------------------------------------------------------#

if [ "$api" = 'yes' ]; then
    echo "API='no'" >> $HESTIA/conf/hestia.conf
else
    rm -r $HESTIA/web/api
    echo "API='yes'" >> $HESTIA/conf/hestia.conf
fi


#----------------------------------------------------------#
#                      Fix phpmyadmin                      #
#----------------------------------------------------------#
# Special thanks to Pavel Galkin (https://skurudo.ru)
# https://github.com/skurudo/phpmyadmin-fixer

source $hestiacp/phpmyadmin/pma.sh > /dev/null 2>&1

#----------------------------------------------------------#
#                   Configure Admin User                   #
#----------------------------------------------------------#

# Deleting old admin user
if [ ! -z "$(grep ^admin: /etc/passwd)" ] && [ "$force" = 'yes' ]; then
    chattr -i /home/admin/conf > /dev/null 2>&1
    userdel -f admin >/dev/null 2>&1
    chattr -i /home/admin/conf >/dev/null 2>&1
    mv -f /home/admin  $hst_backups/home/ >/dev/null 2>&1
    rm -f /tmp/sess_* >/dev/null 2>&1
fi
if [ ! -z "$(grep ^admin: /etc/group)" ] && [ "$force" = 'yes' ]; then
    groupdel admin > /dev/null 2>&1
fi

# Adding admin account
$HESTIA/bin/v-add-user admin $vpass $email default System Administrator
check_result $? "can't create admin user"
$HESTIA/bin/v-change-user-shell admin nologin
$HESTIA/bin/v-change-user-language admin $lang

# RoundCube permissions fix
if [ "$exim" = 'yes' ] && [ "$mysql" = 'yes' ]; then
    if [ ! -d "/var/log/roundcube" ]; then
        mkdir /var/log/roundcube
    fi
    chown admin:admin /var/log/roundcube
fi

# Configuring system ips
$HESTIA/bin/v-update-sys-ip > /dev/null 2>&1

# Get main ip
ip=$(ip addr|grep 'inet '|grep global|head -n1|awk '{print $2}'|cut -f1 -d/)
local_ip=$ip

# Firewall configuration
if [ "$iptables" = 'yes' ]; then
    $HESTIA/bin/v-update-firewall
fi

# Get public ip
pub_ip=$(curl --ipv4 -s https://www.hestiacp.com/what-is-my-ip/)

if [ ! -z "$pub_ip" ] && [ "$pub_ip" != "$ip" ]; then
    $HESTIA/bin/v-change-sys-ip-nat $ip $pub_ip > /dev/null 2>&1
    ip=$pub_ip
fi

# Configuring libapache2-mod-remoteip
if [ "$apache" = 'yes' ] && [ "$nginx"  = 'yes' ] ; then
    cd /etc/apache2/mods-available
    echo "<IfModule mod_remoteip.c>" > remoteip.conf
    echo "  RemoteIPHeader X-Real-IP" >> remoteip.conf
    if [ "$local_ip" != "127.0.0.1" ] && [ "$pub_ip" != "127.0.0.1" ]; then
        echo "  RemoteIPInternalProxy 127.0.0.1" >> remoteip.conf
    fi
    if [ ! -z "$local_ip" ] && [ "$local_ip" != "$pub_ip" ]; then
        echo "  RemoteIPInternalProxy $local_ip" >> remoteip.conf
    fi
    if [ ! -z "$pub_ip" ]; then
        echo "  RemoteIPInternalProxy $pub_ip" >> remoteip.conf
    fi
    echo "</IfModule>" >> remoteip.conf
    sed -i "s/LogFormat \"%h/LogFormat \"%a/g" /etc/apache2/apache2.conf
    a2enmod remoteip
    service apache2 restart
fi

# Configuring mysql host
if [ "$mysql" = 'yes' ]; then
    $HESTIA/bin/v-add-database-host mysql localhost root $mpass
fi

# Configuring pgsql host
if [ "$postgresql" = 'yes' ]; then
    $HESTIA/bin/v-add-database-host pgsql localhost postgres $ppass
fi

# Adding default domain
$HESTIA/bin/v-add-domain admin $servername
check_result $? "can't create $servername domain"

# Adding cron jobs
command="sudo $HESTIA/bin/v-update-sys-queue disk"
$HESTIA/bin/v-add-cron-job 'admin' '15' '02' '*' '*' '*' "$command"
command="sudo $HESTIA/bin/v-update-sys-queue traffic"
$HESTIA/bin/v-add-cron-job 'admin' '10' '00' '*' '*' '*' "$command"
command="sudo $HESTIA/bin/v-update-sys-queue webstats"
$HESTIA/bin/v-add-cron-job 'admin' '30' '03' '*' '*' '*' "$command"
command="sudo $HESTIA/bin/v-update-sys-queue backup"
$HESTIA/bin/v-add-cron-job 'admin' '*/5' '*' '*' '*' '*' "$command"
command="sudo $HESTIA/bin/v-backup-users"
$HESTIA/bin/v-add-cron-job 'admin' '10' '05' '*' '*' '*' "$command"
command="sudo $HESTIA/bin/v-update-user-stats"
$HESTIA/bin/v-add-cron-job 'admin' '20' '00' '*' '*' '*' "$command"
command="sudo $HESTIA/bin/v-update-sys-rrd"
$HESTIA/bin/v-add-cron-job 'admin' '*/5' '*' '*' '*' '*' "$command"
service cron restart

# Building inititall rrd images
$HESTIA/bin/v-update-sys-rrd

# Enabling file system quota
if [ "$quota" = 'yes' ]; then
    $HESTIA/bin/v-add-sys-quota
fi

# Set backend port
$HESTIA/bin/v-change-sys-port $port

# Starting hestia service
update-rc.d hestia defaults
service hestia start
check_result $? "hestia start failed"
chown admin:admin $HESTIA/data/sessions

# Adding cronjob for autoupdates
$HESTIA/bin/v-add-cron-hestia-autoupdate


#----------------------------------------------------------#
#                   Hestia Access Info                     #
#----------------------------------------------------------#

# Comparing hostname and ip
host_ip=$(host $servername| head -n 1 | awk '{print $NF}')
if [ "$host_ip" = "$ip" ]; then
    ip="$servername"
fi

# Sending notification to admin email
echo -e "Congratulations, you have just successfully installed \
Hestia Control Panel

    https://$ip:$port
    username: admin
    password: $vpass

We hope that you enjoy your installation of Hestia. Please \
feel free to contact us anytime if you have any questions.
Thank you.

--
Sincerely yours
hestiacp.com team
" > $tmpfile

send_mail="$HESTIA/web/inc/mail-wrapper.php"
cat $tmpfile | $send_mail -s "Hestia Control Panel" $email

# Congrats
echo '======================================================='
echo
echo '  _   _           _   _        ____ ____  '
echo ' | | | | ___  ___| |_(_) __ _ / ___|  _ \ '
echo ' | |_| |/ _ \/ __| __| |/ _` | |   | |_) |'
echo ' |  _  |  __/\__ \ |_| | (_| | |___|  __/ '
echo ' |_| |_|\___||___/\__|_|\__,_|\____|_|    '
echo
echo
cat $tmpfile
rm -f $tmpfile

# EOF
