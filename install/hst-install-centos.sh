#!/bin/bash

# Hestia RHEL/CentOS installer v1.0

#----------------------------------------------------------#
#                  Variables&Functions                     #
#----------------------------------------------------------#
export PATH=$PATH:/sbin
#export DEBIAN_FRONTEND=noninteractive
RHOST='rhel.hestiacp.com'
GPG='gpg.hestiacp.com'
VERSION='rhel'
HESTIA='/usr/local/hestia'
LOG="/root/hst_install_backups/hst_install-$(date +%d%m%Y%H%M).log"
memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])
hst_backups="/root/hst_install_backups/$(date +%d%m%Y%H%M)"
arch=$(uname -i)
spinner="/-\|"
os='rhel'
release=$(grep -o "[0-9]" /etc/redhat-release |head -n1)
codename="${os}_$release"
HESTIA_INSTALL_DIR="$HESTIA/install/rhel"
VERBOSE='no'

# Define software versions
HESTIA_INSTALL_VER='1.2.0'
pma_v='5.0.2'
multiphp_v=("5.6" "7.0" "7.1" "7.2" "7.3" "7.4")
fpm_v="73"
mariadb_v="10.3"

# Defining software pack for all distros
software=" nginx awstats bc bind bind-libs bind-utils clamav clamav-update
    curl dovecot e2fsprogs exim expect fail2ban flex freetype ftp GeoIP httpd
    ImageMagick iptables-services lsof mailx mariadb mariadb-server mc
    mod_fcgid mod_ruid2 mod_ssl net-tools openssh-clients pcre php
    php-bcmath php-cli php-common php-fpm php-gd php-imap php-mbstring
    php-mcrypt phpMyAdmin php-mysql php-pdo phpPgAdmin php-pgsql php-soap
    php-tidy php-xml php-xmlrpc postgresql postgresql-contrib
    postgresql-server proftpd roundcubemail rrdtool rsyslog screen
    spamassassin sqlite sudo tar telnet unzip hestia hestia-nginx
    hestia-php vim-common vsftpd webalizer which zip wget tar "

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
  -m, --mysql             Install MariaDB       [yes|no]  default: yes
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
  -D, --with-rpms         Path to Hestia rpms
  -f, --force             Force installation
  -h, --help              Print this help

  Example: bash $0 -e demo@hestiacp.com -p p4ssw0rd --apache no --phpfpm yes"
    exit 1
}

# Defining file download function
download_file() {
    wget $1 -q --show-progress --progress=bar:force
}

# Defining password-gen function
gen_pass() {
    MATRIX='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
    LENGTH=16
    while [ ${n:=1} -le $LENGTH ]; do
        PASS="$PASS${MATRIX:$(($RANDOM%${#MATRIX})):1}"
        let n+=1
    done
    echo "$PASS"
}

# Defining return code check function
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

# Defining function to set default language value
set_default_lang() {
    if [ -z "$lang" ]; then
        eval lang=$1
    fi
    lang_list="
        ar cz el fa hu ja no pt se ua
        bs da en fi id ka pl ro tr vi
        cn de es fr it nl pt-BR ru tw
        bg ko sr th ur"
    if !(echo $lang_list |grep -w $lang > /dev/null 2>&1); then
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
        --with-rpms)            args="${args}-D " ;;
        --help)                 args="${args}-h " ;;
        *)                      [[ "${arg:0:1}" == "-" ]] || delim="\""
                                args="${args}${delim}${arg}${delim} ";;
    esac
done
eval set -- "$args"

# Parsing arguments
while getopts "a:n:w:v:j:k:m:g:d:x:z:c:t:i:b:r:o:q:l:y:s:e:p:D:fh" Option; do
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
        D) withrpms=$OPTARG ;;          # Hestia rpms path
        f) force='yes' ;;               # Force install
        h) help ;;                      # Help
        *) help ;;                      # Print help (default)
    esac
done

# Defining default software stack
set_default_value 'nginx' 'yes'
set_default_value 'apache' 'yes'
set_default_value 'phpfpm' 'yes'
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
set_default_value 'interactive' 'yes'
set_default_value 'api' 'yes'
set_default_port '8083'
set_default_lang 'en'

# Checking software conflicts

if [ "$multiphp" = 'yes' ]; then
    phpfpm='yes'
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
    check_result 1 "Script can be run executed only by root"
fi

# Checking admin user account
if [ ! -z "$(grep ^admin: /etc/passwd /etc/group)" ] && [ -z "$force" ]; then
    echo 'Please remove admin user account before proceeding.'
    echo 'If you want to do it automatically run installer with -f option:'
    echo -e "Example: bash $0 --force\n"
    check_result 1 "User admin exists"
fi

# Check if a default webserver was set
if [ $apache = 'no' ] && [ $nginx = 'no' ]; then
    check_result 1 "No web server was selected"
fi

# Clear the screen once launch permissions have been verified
clear

# Welcome message
echo "Welcome to the Hestia Control Panel installer!"
echo 
echo "Please wait a moment while we update your system's repositories and"
echo "install any necessary dependencies required to proceed with the installation..."
echo 

# Creating backup directory
mkdir -p $hst_backups

# Checking ntpdate
if [ "$release" -eq '7' ]; then
    if [ ! -e '/usr/sbin/ntpdate' ]; then
        echo "(*) Installing ntpdate..."
        yum -y install ntpdate >> $LOG
        check_result $? "Can't install ntpdate"
    fi
else
    # 8 and up
    if [ ! -e '/usr/sbin/chronyd' ]; then
        echo "(*) Installing chrony..."
        yum -y install chrony >> $LOG
        check_result $? "Can't install chrony"
    fi
fi

# Checking wget
if [ ! -e '/usr/bin/wget' ]; then
    echo "(*) Installing wget..."
    yum -y install wget >> $LOG
    check_result $? "Can't install wget"
fi

# Checking installed packages
tmpfile=$(mktemp -p /tmp)
rpm -qa > $tmpfile
for pkg in exim mariadb-server MariaDB-server mysql-server httpd nginx hestia postfix; do
    if [ ! -z "$(grep $pkg $tmpfile)" ]; then
        conflicts="$pkg* $conflicts"
    fi
done
rm -f $tmpfile
if [ ! -z "$conflicts" ] && [ -z "$force" ]; then
    echo '!!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!!'
    echo
    echo 'WARNING: The following packages are already installed'
    echo "$conflicts"
    echo
    echo 'It is highly recommended that you remove them before proceeding.'
    echo
    echo '!!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!! !!!'
    echo
    read -p 'Would you like to remove the conflicting packages? [y/n] ' answer
    if [ "$answer" = 'y' ] || [ "$answer" = 'Y'  ]; then
        yum remove $conflicts -y
        check_result $? 'yum remove failed'
        unset $answer
    else
        check_result 1 "Hestia Control Panel should be installed on a clean server."
    fi
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
echo "                                    v${HESTIA_INSTALL_VER}"
echo -e "\n"
echo "===================================================================="
echo -e "\n"
echo 'The following server components will be installed on your system:'
echo

# Web stack
if [ "$nginx" = 'yes' ]; then
    echo '   - NGINX Web / Proxy Server'
fi
if [ "$apache" = 'yes' ] && [ "$nginx" = 'no' ] ; then
    echo '   - Apache Web Server'
fi
if [ "$apache" = 'yes' ] && [ "$nginx"  = 'yes' ] ; then
    echo '   - Apache Web Server (as backend)'
fi
if [ "$phpfpm"  = 'yes' ] && [ "$multiphp" = 'no' ]; then
    echo '   - PHP-FPM Application Server'
fi
if [ "$multiphp"  = 'yes' ]; then
    echo '   - Multi-PHP Environment'
fi

# DNS stack
if [ "$named" = 'yes' ]; then
    echo '   - Bind DNS Server'
fi

# Mail stack
if [ "$exim" = 'yes' ]; then
    echo -n '   - Exim Mail Server'
    if [ "$clamd" = 'yes'  ] ||  [ "$spamd" = 'yes' ] ; then
        echo -n ' + '
        if [ "$clamd" = 'yes' ]; then
            echo -n 'ClamAV '
        fi
        if [ "$spamd" = 'yes' ]; then
            if [ "$clamd" = 'yes' ]; then
                echo -n '+ '
            fi
            echo -n 'SpamAssassin'
        fi
    fi
    echo
    if [ "$dovecot" = 'yes' ]; then
        echo '   - Dovecot POP3/IMAP Server'
    fi
fi

# Database stack
if [ "$mysql" = 'yes' ]; then
        echo '   - MariaDB Database Server'
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
    echo -n '   - Firewall (Iptables)'
fi
if [ "$iptables" = 'yes' ] && [ "$fail2ban" = 'yes' ]; then
    echo -n ' + Fail2Ban Access Monitor'
fi
echo -e "\n"
echo "===================================================================="
echo -e "\n"

# Asking for confirmation to proceed
if [ "$interactive" = 'yes' ]; then
    read -p 'Would you like to continue with the installation? [Y/N]: ' answer
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
        read -p "Please enter FQDN hostname [$(hostname -f)]: " servername
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
echo -e "Installation backup directory: $hst_backups"

# Print Log File Path
echo "Installation log file: $LOG"

# Print new line
echo


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
echo "Adding required repositories to proceed with installation:"
echo

# Installing EPEL repository
yum install epel-release -y
check_result $? "Can't install EPEL repository"

# Installing Remi repository
yum -y install http://rpms.remirepo.net/enterprise/remi-release-$release.rpm
check_result $? "Can't install REMI repository"
sed -i "s/enabled=0/enabled=1/g" /etc/yum.repos.d/remi.repo

# Installing Nginx repository
nrepo="/etc/yum.repos.d/nginx.repo"
echo "[nginx]" > $nrepo
echo "name=nginx repo" >> $nrepo
echo "baseurl=https://nginx.org/packages/centos/$release/\$basearch/" >> $nrepo
echo "gpgcheck=0" >> $nrepo
echo "enabled=1" >> $nrepo

#----------------------------------------------------------#
#                         Backup                           #
#----------------------------------------------------------#

# Creating backup directory tree
mkdir -p $hst_backups
cd $hst_backups
mkdir nginx httpd php vsftpd proftpd bind exim4 dovecot clamd
mkdir spamassassin mysql postgresql hestia

# Backup nginx configuration
systemctl stop nginx > /dev/null 2>&1
cp -r /etc/nginx/* $hst_backups/nginx > /dev/null 2>&1

# Backup Apache configuration
systemctl stop httpd > /dev/null 2>&1
cp -r /etc/httpd/* $hst_backups/httpd > /dev/null 2>&1

# Backup PHP-FPM configuration
systemctl stop php-fpm >/dev/null 2>&1
cp /etc/php.ini $hst_backups/php > /dev/null 2>&1
cp -r /etc/php.d  $hst_backups/php > /dev/null 2>&1
cp /etc/php-fpm.conf $hst_backups/php-fpm > /dev/null 2>&1
mv -f /etc/php-fpm.d/* $hst_backups/php-fpm/ > /dev/null 2>&1

# Backup Bind configuration
yum remove bind-chroot > /dev/null 2>&1
systemctl stop named > /dev/null 2>&1
cp /etc/named.conf $hst_backups/named >/dev/null 2>&1

# Backup Vsftpd configuration
systemctl stop vsftpd > /dev/null 2>&1
cp /etc/vsftpd/vsftpd.conf $hst_backups/vsftpd >/dev/null 2>&1

# Backup ProFTPD configuration
systemctl stop proftpd > /dev/null 2>&1
cp /etc/proftpd.conf $hst_backups/proftpd >/dev/null 2>&1

# Backup Exim configuration
systemctl stop exim > /dev/null 2>&1
cp -r /etc/exim/* $hst_backups/exim >/dev/null 2>&1

# Backup ClamAV configuration
systemctl stop clamd > /dev/null 2>&1
cp /etc/clamd.conf $hst_backups/clamd >/dev/null 2>&1
cp -r /etc/clamd.d $hst_backups/clamd >/dev/null 2>&1

# Backup SpamAssassin configuration
systemctl stop spamassassin > /dev/null 2>&1
cp -r /etc/mail/spamassassin/* $hst_backups/spamassassin >/dev/null 2>&1

# Backup Dovecot configuration
systemctl stop dovecot > /dev/null 2>&1
cp /etc/dovecot.conf $hst_backups/dovecot > /dev/null 2>&1
cp -r /etc/dovecot/* $hst_backups/dovecot > /dev/null 2>&1

# Backup MySQL/MariaDB configuration and data
systemctl stop mysql > /dev/null 2>&1
systemctl stop mysqld > /dev/null 2>&1
systemctl stop mariadb > /dev/null 2>&1
mv /var/lib/mysql $hst_backups/mysql/mysql_datadir >/dev/null 2>&1
cp /etc/my.cnf $hst_backups/mysql > /dev/null 2>&1
cp /etc/my.cnf.d $hst_backups/mysql > /dev/null 2>&1
mv /root/.my.cnf  $hst_backups/mysql > /dev/null 2>&1

# Backup PostgreSQL configuration and data
systemctl stop postgresql > /dev/null 2>&1
mv /var/lib/pgsql/data $hst_backups/postgresql/  >/dev/null 2>&1

# Backup Hestia
systemctl stop hestia-nginx > /dev/null 2>&1
systemctl stop hestia-php > /dev/null 2>&1
cp -r $HESTIA* $hst_backups/hestia > /dev/null 2>&1
yum -y remove hestia hestia-nginx hestia-php > /dev/null 2>&1
rm -rf $HESTIA > /dev/null 2>&1


#----------------------------------------------------------#
#                     Package Includes                     #
#----------------------------------------------------------#

if [ "$phpfpm" = 'yes' ]; then
    phpfpm_prefix="$fpm_v-php"  # phpfpm_prefix="$fpm_v" for Debian
    fpm="php$phpfpm_prefix php$phpfpm_prefix-common php$phpfpm_prefix-bcmath php$phpfpm_prefix-cli
         php$phpfpm_prefix-curl php$phpfpm_prefix-fpm php$phpfpm_prefix-gd php$phpfpm_prefix-intl
         php$phpfpm_prefix-mysql php$phpfpm_prefix-soap php$phpfpm_prefix-xml php$phpfpm_prefix-zip
         php$phpfpm_prefix-mbstring php$phpfpm_prefix-json php$phpfpm_prefix-bz2 php$phpfpm_prefix-pspell
         php$phpfpm_prefix-imagick"
    software="$software $fpm "
fi


#----------------------------------------------------------#
#                     Package Excludes                     #
#----------------------------------------------------------#

# Excluding packages
if [ "$nginx" = 'no'  ]; then
    software=$(echo "$software" | sed -e "s/\bnginx\b/ /")
fi
if [ "$apache" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bhttpd\b/ /")
    software=$(echo "$software" | sed -e "s/\bm\od_ssl\b/ /")
    software=$(echo "$software" | sed -e "s/\bmod_fcgid\b/ /")
    software=$(echo "$software" | sed -e "s/\bmod_ruid2\b/ /")
fi
if [ "$phpfpm" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bphp-fpm\b/ /")
fi
if [ "$vsftpd" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bvsftpd\b/ /")
fi
if [ "$proftpd" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bproftpd\b/ /")
fi
if [ "$named" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bbind\b/ /")
fi
if [ "$exim" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bexim\b/ /")
    software=$(echo "$software" | sed -e "s/\bdovecot\b/ /")
    software=$(echo "$software" | sed -e "s/\bclamd\b/ /")
    software=$(echo "$software" | sed -e "s/\bclamav\b/ /")
    software=$(echo "$software" | sed -e "s/\bclamav-update\b/ /")
    software=$(echo "$software" | sed -e "s/\bspamassassin\b/ /")
    software=$(echo "$software" | sed -e "s/\broundcube-core\b/ /")
    software=$(echo "$software" | sed -e "s/\broundcube-mysql\b/ /")
    software=$(echo "$software" | sed -e "s/\broundcube-plugins\b/ /")
fi
if [ "$clamd" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bclamd\b/ /")
    software=$(echo "$software" | sed -e "s/\bclamav\b/ /")
    software=$(echo "$software" | sed -e "s/\bclamav-update\b/ /")
fi
if [ "$spamd" = 'no' ]; then
    software=$(echo "$software" | sed -e 's/\bspamassassin\b/ /')
fi
if [ "$dovecot" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bdovecot-imapd\b/ /")
    software=$(echo "$software" | sed -e "s/\bdovecot-pop3d\b/ /")
    software=$(echo "$software" | sed -e "s/\broundcube-core\b/ /")
    software=$(echo "$software" | sed -e "s/\broundcube-mysql\b/ /")
    software=$(echo "$software" | sed -e "s/\broundcube-plugins\b/ /")
fi
if [ "$mysql" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bmariadb-server\b/ /")
    software=$(echo "$software" | sed -e "s/\bmariadb-client\b/ /")
    software=$(echo "$software" | sed -e "s/\bmariadb-common\b/ /")
    software=$(echo "$software" | sed -e "s/\bphp$phpfpm_prefix-mysql\b/ /")
    if [ "$multiphp" = 'yes' ]; then
        for v in "${multiphp_v[@]}"; do
            software=$(echo "$software" | sed -e "s/\bphp$v-mysql\b/ /")
            software=$(echo "$software" | sed -e "s/\bphp$v-bz2\b/ /")
        done
fi
    software=$(echo "$software" | sed -e "s/\bphpmyadmin\b/ /")
fi
if [ "$postgresql" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bpostgresql-contrib\b/ /")
    software=$(echo "$software" | sed -e "s/\bpostgresql-server\b/ /")
    software=$(echo "$software" | sed -e "s/\bphp$phpfpm_prefix-pgsql\b/ /")
    if [ "$multiphp" = 'yes' ]; then
        for v in "${multiphp_v[@]}"; do
            software=$(echo "$software" | sed -e "s/\bphp$v-pgsql\b/ /")
        done
fi
    software=$(echo "$software" | sed -e "s/\bphppgadmin\b/ /")
fi
if [ "$iptables" = 'no' ] || [ "$fail2ban" = 'no' ]; then
    software=$(echo "$software" | sed -e "s/\bfail2ban\b/ /")
fi
if [ "$phpfpm" = 'yes' ]; then
    software=$(echo "$software" | sed -e "s/\bphp$phpfpm_prefix-cgi\b/ /")
fi
if [ -d "$withrpms" ]; then
    software=$(echo "$software" | sed -e "s/\bhestia-nginx\b/ /")
    software=$(echo "$software" | sed -e "s/\bhestia-php\b/ /")
    software=$(echo "$software" | sed -e "s/\bhestia\b/ /")
fi

#----------------------------------------------------------#
#                     Install packages                     #
#----------------------------------------------------------#

if [ "$codename" = "rhel_7" ]; then
    enabled_repos="*base *updates,nginx,epel,hestia,remi*"
elif [ "$codename" = "rhel_8" ]; then
    # Enable Remi PHP stream
    dnf module disable -y php:*
    dnf module enable -y php:remi-7.4

    # Enable Perl 5.26
    dnf module disable -y perl:*
    dnf module enable -y perl:5.26
    
    dnf config-manager --set-enabled BaseOS
    dnf config-manager --set-enabled epel
    dnf config-manager --set-enabled epel-modular
    dnf config-manager --set-enabled extras
    dnf config-manager --set-enabled nginx
    dnf config-manager --set-enabled remi
    dnf config-manager --set-enabled remi-modular
    dnf config-manager --set-enabled PowerTools

    # Raven-extras repo for mod_ruid2
    dnf install -y https://pkgs.dyn.su/el8/base/x86_64/raven-release-1.0-1.el8.noarch.rpm
    dnf config-manager --set-enabled raven-extras
    

    # No webalizer, phpPgAdmin on CentOS 8 yet
    software=$(echo "$software" | sed -e "s/\bwebalizer\b/ /")
    software=$(echo "$software" | sed -e "s/\bphpPgAdmin\b/ /")

    enabled_repos="BaseOS AppStream \
        epel epel-modular extras nginx PowerTools \
        raven raven-extras remi remi-modular"
fi

# Installing rpm packages
yum install -y $software
if [ $? -ne 0 ]; then
    echo yum -y --disablerepo=\* \
        --enablerepo="$enabled_repos" \
        install $software
    yum -y --disablerepo=\* \
        --enablerepo="$enabled_repos" \
        install $software
fi
check_result $? "yum install failed"

if [ -d "$withrpms" ]; then
    yum install -y $withrpms/hestia-*.rpm
else
    # Check repository availability
    wget --quiet "https://$GPG/rhel_signing.key" -O /dev/null
    check_result $? "Unable to connect to the Hestia RHEL repository"

    # Installing Hestia repository
    vrepo='/etc/yum.repos.d/hestia.repo'
    echo "[hestia]" > $vrepo
    echo "name=Hestia - $REPO" >> $vrepo
    echo "baseurl=http://$RHOST/$REPO/$release/\$basearch/" >> $vrepo
    echo "enabled=1" >> $vrepo
    echo "gpgcheck=1" >> $vrepo
    echo "gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-HESTIA" >> $vrepo
    wget c.hestiacp.com/GPG.txt -O /etc/pki/rpm-gpg/RPM-GPG-KEY-HESTIA

    yum install -y hestia hestia-nginx hestia-php
fi



#----------------------------------------------------------#
#                     Configure system                     #
#----------------------------------------------------------#

echo "(*) Configuring system settings..."
# Restarting rsyslog
systemctl restart rsyslog > /dev/null 2>&1

# Checking ipv6 on loopback interface
check_lo_ipv6=$(/sbin/ip addr | grep 'inet6')
check_rc_ipv6=$(grep 'scope global dev lo' /etc/rc.local)
if [ ! -z "$check_lo_ipv6)" ] && [ -z "$check_rc_ipv6" ]; then
    ip addr add ::2/128 scope global dev lo
    echo "# Hestia: Workraround for openssl validation func" >> /etc/rc.local
    echo "ip addr add ::2/128 scope global dev lo" >> /etc/rc.local
    chmod a+x /etc/rc.local
fi

# Disabling SELinux
if [ -e '/etc/sysconfig/selinux' ]; then
    sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/sysconfig/selinux
    sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config
    setenforce 0 2>/dev/null
fi

# Disabling iptables
systemctl stop iptables
systemctl stop firewalld >/dev/null 2>&1

# Configuring NTP synchronization
if [ "$codename" = "rhel_7" ]; then
    echo '#!/bin/sh' > /etc/cron.daily/ntpdate
    echo "$(which ntpdate) -s pool.ntp.org" >> /etc/cron.daily/ntpdate
    chmod 775 /etc/cron.daily/ntpdate
    ntpdate -s pool.ntp.org
elif [ "$codename" = "rhel_7" ]; then
    systemctl enable --now chronyd
fi

# Disabling webalizer routine
rm -f /etc/cron.daily/00webalizer

# Adding backup user
adduser backup 2>/dev/null
ln -sf /home/backup /backup
chmod a+x /backup

# Fix for nonexistent Debian-style "nogroup" on RHEL-based systems
groupadd -o -g $(id -g nobody) nogroup

# Set directory color
if [ -z "$(grep 'LS_COLORS="$LS_COLORS:di=00;33"' /etc/profile)" ]; then
    echo 'LS_COLORS="$LS_COLORS:di=00;33"' >> /etc/profile
fi

# Register /sbin/nologin and /usr/sbin/nologin
if [ -z "$(grep ^/sbin/nologin /etc/shells)" ]; then
    echo "/sbin/nologin" >> /etc/shells
fi

if [ -z "$(grep ^/usr/sbin/nologin /etc/shells)" ]; then
    echo "/usr/sbin/nologin" >> /etc/shells
fi

# Changing default systemd interval
if [ "$release" -eq '7' ]; then
    # Hi Lennart
    echo "DefaultStartLimitInterval=1s" >> /etc/systemd/system.conf
    echo "DefaultStartLimitBurst=60" >> /etc/systemd/system.conf
    systemctl daemon-reexec
fi


#----------------------------------------------------------#
#                     Configure Hestia                     #
#----------------------------------------------------------#

echo "(*) Configuring Hestia Control Panel..."
# Installing sudo configuration
mkdir -p /etc/sudoers.d
cp -f $HESTIA_INSTALL_DIR/sudo/admin /etc/sudoers.d/
chmod 440 /etc/sudoers.d/admin

# Configuring system env
echo "export HESTIA='$HESTIA'" > /etc/profile.d/hestia.sh
echo 'PATH=$PATH:'$HESTIA'/bin' >> /etc/profile.d/hestia.sh
echo 'export PATH' >> /etc/profile.d/hestia.sh
chmod 755 /etc/profile.d/hestia.sh
source /etc/profile.d/hestia.sh

# Configuring logrotate for hestia logs
cp -f $HESTIA_INSTALL_DIR/logrotate/hestia /etc/logrotate.d/

# Building directory tree and creating some blank files for Hestia
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
chmod 770 $HESTIA/data/sessions

# Generating Hestia configuration
rm -f $HESTIA/conf/hestia.conf > /dev/null 2>&1
touch $HESTIA/conf/hestia.conf
chmod 660 $HESTIA/conf/hestia.conf

# Web stack
if [ "$apache" = 'yes' ] && [ "$nginx" = 'no' ] ; then
    echo "WEB_SYSTEM='httpd'" >> $HESTIA/conf/hestia.conf
    echo "WEB_RGROUPS='apache'" >> $HESTIA/conf/hestia.conf
    echo "WEB_PORT='80'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL_PORT='443'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL='mod_ssl'"  >> $HESTIA/conf/hestia.conf
    echo "STATS_SYSTEM='awstats'" >> $HESTIA/conf/hestia.conf
fi
if [ "$apache" = 'yes' ] && [ "$nginx"  = 'yes' ] ; then
    echo "WEB_SYSTEM='httpd'" >> $HESTIA/conf/hestia.conf
    echo "WEB_RGROUPS='apache'" >> $HESTIA/conf/hestia.conf
    echo "WEB_PORT='8080'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL_PORT='8443'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL='mod_ssl'"  >> $HESTIA/conf/hestia.conf
    echo "PROXY_SYSTEM='nginx'" >> $HESTIA/conf/hestia.conf
    echo "PROXY_PORT='80'" >> $HESTIA/conf/hestia.conf
    echo "PROXY_SSL_PORT='443'" >> $HESTIA/conf/hestia.conf
    echo "STATS_SYSTEM='awstats'" >> $HESTIA/conf/hestia.conf
fi
if [ "$apache" = 'no' ] && [ "$nginx"  = 'yes' ]; then
    echo "WEB_SYSTEM='nginx'" >> $HESTIA/conf/hestia.conf
    echo "WEB_PORT='80'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL_PORT='443'" >> $HESTIA/conf/hestia.conf
    echo "WEB_SSL='openssl'"  >> $HESTIA/conf/hestia.conf
    echo "STATS_SYSTEM='awstats'" >> $HESTIA/conf/hestia.conf
fi

if [ "$phpfpm" = 'yes' ] || [ "$multiphp" = 'yes' ]; then
        echo "WEB_BACKEND='php-fpm'" >> $HESTIA/conf/hestia.conf
fi

# Database stack
if [ "$mysql" = 'yes' ]; then
    installed_db_types='mysql'
fi

if [ "$pgsql" = 'yes' ]; then
    installed_db_types="$installed_db_type,pgsql"
fi

if [ ! -z "$installed_db_types" ]; then
    db=$(echo "$installed_db_types" |\
        sed "s/,/\n/g"|\
        sort -r -u |\
        sed "/^$/d"|\
        sed ':a;N;$!ba;s/\n/,/g')
    echo "DB_SYSTEM='$db'" >> $HESTIA/conf/hestia.conf
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
    echo "DNS_SYSTEM='named'" >> $HESTIA/conf/hestia.conf
fi

# Mail stack
if [ "$exim" = 'yes' ]; then
    echo "MAIL_SYSTEM='exim'" >> $HESTIA/conf/hestia.conf
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

# Cron daemon
echo "CRON_SYSTEM='crond'" >> $HESTIA/conf/hestia.conf

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

# Version & Release Branch
echo "VERSION='${HESTIA_INSTALL_VER}'" >> $HESTIA/conf/hestia.conf
echo "RELEASE_BRANCH='release'" >> $HESTIA/conf/hestia.conf

# Installing hosting packages
cp -rf $HESTIA_INSTALL_DIR/packages $HESTIA/data/

# Update nameservers in hosting package
IFS='.' read -r -a domain_elements <<< "$servername"
if [ ! -z "${domain_elements[-2]}" ] && [ ! -z "${domain_elements[-1]}" ]; then
    serverdomain="${domain_elements[-2]}.${domain_elements[-1]}"
    sed -i s/"domain.tld"/"$serverdomain"/g $HESTIA/data/packages/*.pkg
fi

# Installing templates
cp -rf $HESTIA_INSTALL_DIR/templates $HESTIA/data/

mkdir -p /var/www/html
mkdir -p /var/www/document_errors

# Install default success page
cp -rf $HESTIA_INSTALL_DIR/templates/web/unassigned/index.html /var/www/html/
cp -rf $HESTIA_INSTALL_DIR/templates/web/skel/document_errors/* /var/www/document_errors/

# Installing firewall rules
cp -rf $HESTIA_INSTALL_DIR/firewall $HESTIA/data/

# Configuring server hostname
$HESTIA/bin/v-change-sys-hostname $servername > /dev/null 2>&1

# Generating SSL certificate
echo "(*) Generating default self-signed SSL certificate..."
$HESTIA/bin/v-generate-ssl-cert $(hostname) $email 'US' 'California' \
     'San Francisco' 'Hestia Control Panel' 'IT' > /tmp/hst.pem

# Parsing certificate file
crt_end=$(grep -n "END CERTIFICATE-" /tmp/hst.pem |cut -f 1 -d:)
key_start=$(grep -n "BEGIN RSA" /tmp/hst.pem |cut -f 1 -d:)
key_end=$(grep -n  "END RSA" /tmp/hst.pem |cut -f 1 -d:)

# Adding SSL certificate
echo "(*) Adding SSL certificate to Hestia Control Panel..."
cd $HESTIA/ssl
sed -n "1,${crt_end}p" /tmp/hst.pem > certificate.crt
sed -n "$key_start,${key_end}p" /tmp/hst.pem > certificate.key
chown root:mail $HESTIA/ssl/*
chmod 660 $HESTIA/ssl/*
rm /tmp/hst.pem

# Adding nologin as a valid system shell
if [ -z "$(grep nologin /etc/shells)" ]; then
    echo "/usr/sbin/nologin" >> /etc/shells
fi

# Install dhparam.pem
cp -f $HESTIA_INSTALL_DIR/ssl/dhparam.pem /etc/ssl

#----------------------------------------------------------#
#                     Configure Nginx                      #
#----------------------------------------------------------#

if [ "$nginx" = 'yes' ]; then
    echo "(*) Configuring NGINX..."
    rm -f /etc/nginx/conf.d/*.conf
    cp -f $HESTIA_INSTALL_DIR/nginx/nginx.conf /etc/nginx/
    cp -f $HESTIA_INSTALL_DIR/nginx/status.conf /etc/nginx/conf.d/
    cp -f $HESTIA_INSTALL_DIR/nginx/phpmyadmin.inc /etc/nginx/conf.d/
    cp -f $HESTIA_INSTALL_DIR/nginx/phppgadmin.inc /etc/nginx/conf.d/
    cp -f $HESTIA_INSTALL_DIR/logrotate/nginx /etc/logrotate.d/
    mkdir -p /etc/nginx/conf.d/domains
    mkdir -p /var/log/nginx/domains
    mkdir -p /etc/systemd/system/nginx.service.d
    cd /etc/systemd/system/nginx.service.d
    echo "[Service]" > limits.conf
    echo "LimitNOFILE=500000" >> limits.conf

    # Update dns servers in nginx.conf
    dns_resolver=$(cat /etc/resolv.conf | grep -i '^nameserver' | cut -d ' ' -f2 | tr '\r\n' ' ' | xargs)
    for ip in $dns_resolver; do
        if [[ $ip =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            resolver="$ip $resolver"
        fi
    done
    if [ ! -z "$resolver" ]; then
        sed -i "s/1.0.0.1 1.1.1.1/$resolver/g" /etc/nginx/nginx.conf
        sed -i "s/1.0.0.1 1.1.1.1/$resolver/g" /usr/local/hestia/nginx/conf/nginx.conf
    fi

    systemctl enable nginx
    systemctl start nginx >> $LOG
    check_result $? "nginx start failed"
fi


#----------------------------------------------------------#
#                    Configure Apache                      #
#----------------------------------------------------------#

if [ "$apache" = 'yes'  ]; then
    echo "(*) Configuring Apache Web Server..."

    # Copy configuration files
    cp -f $HESTIA_INSTALL_DIR/httpd/httpd.conf /etc/httpd/conf/
    cp -f $HESTIA_INSTALL_DIR/httpd/status.conf /etc/httpd/conf.d/
    cp -f $HESTIA_INSTALL_DIR/httpd/hestia-event.conf /etc/httpd/conf.modules.d/
    cp -f $HESTIA_INSTALL_DIR/logrotate/httpd /etc/logrotate.d/

    # Disable modules
    if [ -e "/etc/httpd/conf.modules.d/00-dav.conf" ]; then
        cd /etc/httpd/conf.modules.d
        sed -i "s/^/#/" 00-dav.conf
    fi
    if [ -e "/etc/httpd/conf.modules.d/00-lua.conf" ]; then
        cd /etc/httpd/conf.modules.d
        sed -i "s/^/#/" 00-lua.conf 00-proxy.conf
    fi
    if [ -e "/etc/httpd/conf.modules.d/00-proxy.conf" ]; then
        cd /etc/httpd/conf.modules.d
        sed -i "s/^/#/" 00-proxy.conf
    fi

    if [ "$phpfpm" = 'yes' ]; then
        # Disable prefork and php, enable event
        # apache_module_disable 'php5'
        sed -i "/LoadModule php5_module/ s/^/#/" /etc/httpd/conf.modules.d/*.conf
        # apache_module_disable 'php7'
        sed -i "/LoadModule php7_module/ s/^/#/" /etc/httpd/conf.modules.d/*.conf
        # apache_module_disable 'mpm_prefork'
        sed -i "/LoadModule mpm_prefork_module/ s/^/#/" /etc/httpd/conf.modules.d/*.conf
        # apache_module_enable 'mpm_event'
        sed -i "/LoadModule mpm_event_module/ s/#*//" /etc/httpd/conf.modules.d/*.conf
    else
        # apache_module_enable 'ruid2'
        sed -i "/LoadModule ruid2_module/ s/#*//" /etc/httpd/conf.modules.d/*.conf
    fi

    sed -i "/LoadModule proxy_http2_module/ s/^/#/" /etc/httpd/conf.modules.d/*.conf

    mkdir -p /etc/httpd/conf.d/domains
    echo "# Powered by hestia" > /etc/httpd/conf.d/welcome.conf

    mkdir -p /var/log/httpd/domains
    chmod a+x /var/log/httpd
    chmod 640 /var/log/httpd/access.log /var/log/httpd/error.log
    chmod 751 /var/log/httpd/domains
    chmod -f 777 /var/lib/php/session

    # Not needed. status.conf is fixed.
    # sed -i '/Allow from all/d' /etc/apache2/mods-enabled/status.conf

    systemctl enable httpd
    systemctl start httpd >> $LOG
    check_result $? "httpd start failed"
else
    systemctl disable httpd > /dev/null 2>&1
    systemctl stop httpd > /dev/null 2>&1
fi


#----------------------------------------------------------#
#                     Configure PHP-FPM                    #
#----------------------------------------------------------#

if [ "$multiphp" = 'yes' ] ; then
    for v in "${multiphp_v[@]}"; do
        cp -r /etc/php/$v/ /root/hst_install_backups/php$v/
        rm -f /etc/php/$v/fpm/pool.d/*
        echo "(*) Install PHP version $v..."
        $HESTIA/bin/v-add-web-php "$v" > /dev/null 2>&1
    done
fi

if [ "$phpfpm" = 'yes' ]; then
    echo "(*) Configuring PHP-FPM..."
    $HESTIA/bin/v-add-web-php "$fpm_v" > /dev/null 2>&1
    cp -f $HESTIA_INSTALL_DIR/php-fpm/www.conf /etc/php/$fpm_v/fpm/pool.d/www.conf
    systemctl enable php$phpfpm_prefix-fpm > /dev/null 2>&1
    systemctl start php$phpfpm_prefix-fpm >> $LOG
    check_result $? "php-fpm start failed"
    update-alternatives --set php /usr/bin/php$fpm_v > /dev/null 2>&1
fi


#----------------------------------------------------------#
#                     Configure PHP                        #
#----------------------------------------------------------#

echo "(*) Configuring PHP..."
ZONE=$(timedatectl 2>/dev/null|grep Timezone|awk '{print $2}')
if [ -z "$ZONE" ]; then
    ZONE='UTC'
fi
for pconf in $(find /etc/php* -name php.ini); do
    sed -i "s|;date.timezone =|date.timezone = $ZONE|g" $pconf
    sed -i 's%_open_tag = Off%_open_tag = On%g' $pconf
done

# Cleanup php session files not changed in the last 7 days (60*24*7 minutes)
echo '#!/bin/sh' > /etc/cron.daily/php-session-cleanup
echo "find -O3 /home/*/tmp/ -ignore_readdir_race -depth -mindepth 1 -name 'sess_*' -type f -cmin '+10080' -delete > /dev/null 2>&1" >> /etc/cron.daily/php-session-cleanup
echo "find -O3 $HESTIA/data/sessions/ -ignore_readdir_race -depth -mindepth 1 -name 'sess_*' -type f -cmin '+10080' -delete > /dev/null 2>&1" >> /etc/cron.daily/php-session-cleanup
chmod 755 /etc/cron.daily/php-session-cleanup

#----------------------------------------------------------#
#                    Configure Vsftpd                      #
#----------------------------------------------------------#

if [ "$vsftpd" = 'yes' ]; then
        echo "(*) Configuring Vsftpd server..."
    cp -f $HESTIA_INSTALL_DIR/vsftpd/vsftpd.conf /etc/
    touch /var/log/vsftpd.log
    chown root:adm /var/log/vsftpd.log
    chmod 640 /var/log/vsftpd.log
    touch /var/log/xferlog
    chown root:adm /var/log/xferlog
    chmod 640 /var/log/xferlog
    systemctl enable vsftpd
    systemctl start vsftpd
    check_result $? "vsftpd start failed"

fi


#----------------------------------------------------------#
#                    Configure ProFTPD                     #
#----------------------------------------------------------#

if [ "$proftpd" = 'yes' ]; then
    echo "(*) Configuring ProFTPD server..."
    echo "127.0.0.1 $servername" >> /etc/hosts
    cp -f $HESTIA_INSTALL_DIR/proftpd/proftpd.conf /etc/proftpd/
    systemctl enable proftpd > /dev/null 2>&1
    systemctl start proftpd >> $LOG
    check_result $? "proftpd start failed"
fi


#----------------------------------------------------------#
#                  Configure MySQL/MariaDB                 #
#----------------------------------------------------------#

if [ "$mysql" = 'yes' ]; then
    echo "(*) Configuring MariaDB database server..."
    mycnf="my-small.cnf"
    if [ $memory -gt 1200000 ]; then
        mycnf="my-medium.cnf"
    fi
    if [ $memory -gt 3900000 ]; then
        mycnf="my-large.cnf"
    fi

    # Configuring MariaDB
    cp -f $HESTIA_INSTALL_DIR/mysql/$mycnf /etc/my.cnf
    rm -f /etc/my.cnf.d/*.cnf
    mysql_install_db >> $LOG

    systemctl enable mariadb > /dev/null 2>&1
    systemctl start mariadb >> $LOG
    check_result $? "mariadb start failed"

    # Securing MySQL installation
    mpass=$(gen_pass)
    mysqladmin -u root password $mpass
    echo -e "[client]\npassword='$mpass'\n" > /root/.my.cnf
    chmod 600 /root/.my.cnf

    # Clear MariaDB Test Users and Databases
    mysql -e "DELETE FROM mysql.user WHERE User=''"
    mysql -e "DROP DATABASE test" > /dev/null 2>&1
    mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%'"
    mysql -e "DELETE FROM mysql.user WHERE user='';"
    mysql -e "DELETE FROM mysql.user WHERE password='' AND authentication_string='';"
fi


#----------------------------------------------------------#
#                    Configure phpMyAdmin                  #
#----------------------------------------------------------#

if [ "$mysql" = 'yes' ]; then
    # Display upgrade information
    echo "(*) Installing phpMyAdmin version v$pma_v..."

    # Download latest phpmyadmin release
    wget --quiet https://files.phpmyadmin.net/phpMyAdmin/$pma_v/phpMyAdmin-$pma_v-all-languages.tar.gz

    # Unpack files
    tar xzf phpMyAdmin-$pma_v-all-languages.tar.gz

    # Delete file to prevent error
    rm -fr /usr/share/phpMyAdmin/doc/html

    # Overwrite old files
    cp -rf phpMyAdmin-$pma_v-all-languages/* /usr/share/phpMyAdmin

    # Set config and log directory
    sed -i "s|define('CONFIG_DIR', ROOT_PATH);|define('CONFIG_DIR', '/etc/phpMyAdmin/');|" /usr/share/phpMyAdmin/libraries/vendor_config.php
    sed -i "s|define('TEMP_DIR', ROOT_PATH . 'tmp/');|define('TEMP_DIR', '/var/lib/phpMyAdmin/temp/');|" /usr/share/phpMyAdmin/libraries/vendor_config.php

    # Create temporary folder and change permission
    mkdir -p /var/lib/phpMyAdmin/temp
    chmod 777 /var/lib/phpMyAdmin/temp

    # Configuring phpMyAdmin
    if [ "$apache" = 'yes' ]; then
        cp -f $HESTIA_INSTALL_DIR/pma/apache.conf /etc/phpMyAdmin/
        rm -f /etc/httpd/conf.d/phpMyAdmin.conf
        ln -s /etc/phpMyAdmin/apache.conf /etc/httpd/conf.d/phpMyAdmin.conf
    fi
    cp -f $HESTIA_INSTALL_DIR/pma/config.inc.php /etc/phpMyAdmin/

    # Clear Up
    rm -fr phpMyAdmin-$pma_v-all-languages
    rm -f phpMyAdmin-$pma_v-all-languages.tar.gz
fi


#----------------------------------------------------------#
#                   Configure PostgreSQL                   #
#----------------------------------------------------------#

if [ "$postgresql" = 'yes' ]; then
    echo "(*) Configuring PostgreSQL database server..."
    ppass=$(gen_pass)
    cp -f $HESTIA_INSTALL_DIR/postgresql/pg_hba.conf /var/lib/pgsql/data/
    systemctl restart postgresql
    sudo -u postgres psql -c "ALTER USER postgres WITH PASSWORD '$ppass'"

    # Configuring phpPgAdmin
    if [ "$apache" = 'yes' ]; then
        cp -f $HESTIA_INSTALL_DIR/pga/phpPgAdmin.conf /etc/httpd/conf.d/
    fi
    cp -f $HESTIA_INSTALL_DIR/pga/config.inc.php /etc/phpPgAdmin/
fi


#----------------------------------------------------------#
#                      Configure Bind                      #
#----------------------------------------------------------#

if [ "$named" = 'yes' ]; then
    echo "(*) Configuring Bind DNS server..."
    cp -f $HESTIA_INSTALL_DIR/bind/named.conf /etc/
    cp -f $HESTIA_INSTALL_DIR/bind/named.conf.options /etc/
    chown root:named /etc/named.conf
    chown root:named /etc/named.conf.options
    chown named:named /var/named
    chmod 640 /etc/named.conf
    chmod 640 /etc/named.conf.options

    systemctl enable named
    systemctl restart named
    check_result $? "named start failed"
fi


#----------------------------------------------------------#
#                      Configure Exim                      #
#----------------------------------------------------------#

if [ "$exim" = 'yes' ]; then
    echo "(*) Configuring Exim mail server..."
    gpasswd -a exim mail > /dev/null 2>&1
    cp -f $HESTIA_INSTALL_DIR/exim/exim.conf.template /etc/exim/
    cp -f $HESTIA_INSTALL_DIR/exim/dnsbl.conf /etc/exim/
    cp -f $HESTIA_INSTALL_DIR/exim/spam-blocks.conf /etc/exim/
    touch /etc/exim/white-blocks.conf

    if [ "$spamd" = 'yes' ]; then
        sed -i "s/#SPAM/SPAM/g" /etc/exim/exim.conf.template
    fi
    if [ "$clamd" = 'yes' ]; then
        sed -i "s/#CLAMD/CLAMD/g" /etc/exim/exim.conf.template
    fi

    chmod 640 /etc/exim/exim.conf.template
    rm -rf /etc/exim/domains
    mkdir -p /etc/exim/domains

    rm -f /etc/alternatives/mta
    ln -s /usr/sbin/sendmail.exim /etc/alternatives/mta
    systemctl disable sendmail 2>/dev/null
    systemctl stop sendmail 2>/dev/null
    systemctl disable postfix 2>/dev/null
    systemctl stop postfix 2>/dev/null
    systemctl enable exim
    systemctl start exim
    check_result $? "exim start failed"
fi


#----------------------------------------------------------#
#                     Configure Dovecot                    #
#----------------------------------------------------------#

if [ "$dovecot" = 'yes' ]; then
    echo "(*) Configuring Dovecot POP/IMAP mail server..."
    gpasswd -a dovecot mail > /dev/null 2>&1
    cp -rf $HESTIA_INSTALL_DIR/dovecot /etc/
    cp -f $HESTIA_INSTALL_DIR/logrotate/dovecot /etc/logrotate.d/
    chown -R root:root /etc/dovecot*
    if [ "$release" -eq 7 ]; then
        sed -i "s#namespace inbox {#namespace inbox {\n  inbox = yes#" /etc/dovecot/conf.d/15-mailboxes.conf
    fi
    systemctl enable dovecot
    systemctl start dovecot
    check_result $? "dovecot start failed"
fi


#----------------------------------------------------------#
#                     Configure ClamAV                     #
#----------------------------------------------------------#

if [ "$clamd" = 'yes' ]; then
    useradd clamav -g clamupdate -s /sbin/nologin -d /var/lib/clamav 2>/dev/null
    gpasswd -a clamupdate exim
    gpasswd -a clamupdate mail
    cp -f $HESTIA_INSTALL_DIR/clamav/clamd.conf /etc/
    cp -f $HESTIA_INSTALL_DIR/clamav/freshclam.conf /etc/
    mkdir -p /var/log/clamav /var/run/clamav
    chown clamav:clamupdate /var/log/clamav /var/run/clamav
    chown -R clamav:clamupdate /var/lib/clamav
    chmod 0775 /var/lib/clamav /var/log/clamav

    cp -f $HESTIA_INSTALL_DIR/clamav/clamd.service /usr/lib/systemd/system/
    systemctl daemon-reload
    systemctl enable clamd

    echo -ne "(*) Installing ClamAV anti-virus definitions... "
    /usr/bin/freshclam >> $LOG &
    BACK_PID=$!
    spin_i=1
    while kill -0 $BACK_PID > /dev/null 2>&1 ; do
        printf "\b${spinner:spin_i++%${#spinner}:1}"
        sleep 0.5
    done
    echo
    systemctl start clamd
    check_result $? "clamav-daemon start failed"
fi


#----------------------------------------------------------#
#                  Configure SpamAssassin                  #
#----------------------------------------------------------#

if [ "$spamd" = 'yes' ]; then
    echo "(*) Configuring SpamAssassin..."
    systemctl enable spamassassin
    systemctl start spamassassin
    check_result $? "spamassassin start failed"
fi


#----------------------------------------------------------#
#                   Configure RoundCube                    #
#----------------------------------------------------------#

if [ "$dovecot" = 'yes' ] && [ "$exim" = 'yes' ] && [ "$mysql" = 'yes' ]; then
    echo "(*) Configuring Roundcube webmail client..."
    cp -f $HESTIA_INSTALL_DIR/roundcube/main.inc.php /etc/roundcubemail/config.inc.php
    cp -f $HESTIA_INSTALL_DIR/roundcube/db.inc.php /etc/roundcubemail/db.inc.php
    cp -f $HESTIA_INSTALL_DIR/roundcube/config.inc.php /etc/roundcubemail/plugins/password/
    cp -f $HESTIA_INSTALL_DIR/roundcube/hestia.php /usr/share/roundcubemail/plugins/password/drivers/
    touch /var/log/roundcubemail/errors
    chmod 640 /etc/roundcubemail/config.inc.php
    chown root:apache /etc/roundcubemail/config.inc.php
    chmod 640 /etc/roundcubemail/db.inc.php
    chown root:apache /etc/roundcubemail/db.inc.php
    chmod 640 /var/log/roundcubemail/errors
    chown apache:adm /var/log/roundcubemail/errors

    r="$(gen_pass)"
    rcDesKey="$(openssl rand -base64 30 | tr -d "/" | cut -c1-24)"
    mysql -e "CREATE DATABASE roundcube"
    mysql -e "GRANT ALL ON roundcube.*
        TO roundcube@localhost IDENTIFIED BY '$r'"
    sed -i "s/%password%/$r/g" /etc/roundcubemail/db.inc.php
    sed -i "s/%des_key%/$rcDesKey/g" /etc/roundcubemail/config.inc.php
    sed -i "s/localhost/$servername/g" /usr/share/roundcubemail/plugins/password/config.inc.php
    mysql roundcube < /usr/share/roundcubemail/SQL/mysql

    # Enable Roundcube plugins
    cp -f $HESTIA_INSTALL_DIR/roundcube/plugins/config_newmail_notifier.inc.php /etc/roundcubemail/plugins/newmail_notifier/config.inc.php
    cp -f $HESTIA_INSTALL_DIR/roundcube/plugins/config_zipdownload.inc.php /etc/roundcubemail/plugins/zipdownload/config.inc.php
    
    # Fixes for PHP 7.4 compatibility
    sed -i 's/$identities, "\\n"/"\\n", $identities/g' /usr/share/roundcubemail/plugins/enigma/lib/enigma_ui.php
    sed -i 's/(array_keys($post_search), \x27|\x27)/(\x27|\x27, array_keys($post_search))/g' /usr/share/roundcubemail/program/lib/Roundcube/rcube_contacts.php
    sed -i 's/implode($name, \x27.\x27)/implode(\x27.\x27, $name)/g' /usr/share/roundcubemail/program/lib/Roundcube/rcube_db.php
    sed -i 's/$fields, \x27,\x27/\x27,\x27, $fields/g' /usr/share/roundcubemail/program/steps/addressbook/search.inc
    sed -i 's/implode($fields, \x27,\x27)/implode(\x27,\x27, $fields)/g' /usr/share/roundcubemail/program/steps/addressbook/search.inc
    sed -i 's/implode($bstyle, \x27; \x27)/implode(\x27; \x27, $bstyle)/g' /usr/share/roundcubemail/program/steps/mail/sendmail.inc

    # Configure webmail alias
    echo "WEBMAIL_ALIAS='webmail'" >> $HESTIA/conf/hestia.conf

    # Add robots.txt
    echo "User-agent: *" > /var/lib/roundcubemail/robots.txt
    echo "Disallow: /" >> /var/lib/roundcubemail/robots.txt

    # Restart services
    if [ "$apache" = 'yes' ]; then
        systemctl restart httpd
    fi
    if [ "$nginx" = 'yes' ]; then
        systemctl restart nginx
    fi
fi


#----------------------------------------------------------#
#                    Configure Fail2Ban                    #
#----------------------------------------------------------#

if [ "$fail2ban" = 'yes' ]; then
    echo "(*) Configuring fail2ban access monitor..."
    cp -rf $HESTIA_INSTALL_DIR/fail2ban /etc/
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









    systemctl enable fail2ban
    systemctl start fail2ban
    check_result $? "fail2ban start failed"
fi


#----------------------------------------------------------#
#                       Configure API                      #
#----------------------------------------------------------#

if [ "$api" = 'yes' ]; then
    echo "API='yes'" >> $HESTIA/conf/hestia.conf
else
    rm -r $HESTIA/web/api
    echo "API='no'" >> $HESTIA/conf/hestia.conf
fi


#----------------------------------------------------------#
#                      Fix phpmyadmin                      #
#----------------------------------------------------------#
# Special thanks to Pavel Galkin (https://skurudo.ru)
# https://github.com/skurudo/phpmyadmin-fixer

if [ "$mysql" = 'yes' ]; then
    source $HESTIA_INSTALL_DIR/phpmyadmin/pma.sh > /dev/null 2>&1
fi


#----------------------------------------------------------#
#                   Configure Admin User                   #
#----------------------------------------------------------#

# Deleting old admin user
if [ ! -z "$(grep ^admin: /etc/passwd)" ] && [ "$force" = 'yes' ]; then
    chattr -i /home/admin/conf > /dev/null 2>&1
    userdel -f admin > /dev/null 2>&1
    chattr -i /home/admin/conf > /dev/null 2>&1
    mv -f /home/admin  $hst_backups/home/ > /dev/null 2>&1
    rm -f /tmp/sess_* > /dev/null 2>&1
fi
if [ ! -z "$(grep ^admin: /etc/group)" ] && [ "$force" = 'yes' ]; then
    groupdel admin > /dev/null 2>&1
fi

# Enable sftp jail
$HESTIA/bin/v-add-sys-sftp-jail > /dev/null 2>&1
check_result $? "can't enable sftp jail"

# Adding Hestia admin account
$HESTIA/bin/v-add-user admin $vpass $email default System Administrator
check_result $? "can't create admin user"
$HESTIA/bin/v-change-user-shell admin nologin
$HESTIA/bin/v-change-user-language admin $lang
chown admin:admin $HESTIA/data/sessions
chown admin:admin $HESTIA/php/var/log
chown admin:admin $HESTIA/php/var/run

# Roundcube permissions fix
if [ "$exim" = 'yes' ] && [ "$mysql" = 'yes' ]; then
    if [ ! -d "/var/log/roundcube" ]; then
        mkdir /var/log/roundcube
    fi
    chown admin:admin /var/log/roundcube
fi

# Configuring system IPs
$HESTIA/bin/v-update-sys-ip > /dev/null 2>&1

# Get main IP
ip=$(ip addr|grep 'inet '|grep global|head -n1|awk '{print $2}'|cut -f1 -d/)

# Configuring firewall
if [ "$iptables" = 'yes' ]; then
    $HESTIA/bin/v-update-firewall
fi

# Get public IP
pub_ip=$(curl --ipv4 -s https://ip.hestiacp.com/)

if [ ! -z "$pub_ip" ] && [ "$pub_ip" != "$ip" ]; then
    $HESTIA/bin/v-change-sys-ip-nat $ip $pub_ip > /dev/null 2>&1
    ip=$pub_ip
fi

# Configuring libapache2-mod-remoteip
if [ "$apache" = 'yes' ] && [ "$nginx"  = 'yes' ] ; then
    cd /etc/httpd/conf.modules.d
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
    #sed -i "s/LogFormat \"%h/LogFormat \"%a/g" /etc/apache2/apache2.conf
    #a2enmod remoteip >> $LOG
    systemctl restart httpd
fi

# Configuring MySQL/MariaDB host
if [ "$mysql" = 'yes' ]; then
    $HESTIA/bin/v-add-database-host mysql localhost root $mpass
fi

# Configuring PostgreSQL host
if [ "$postgresql" = 'yes' ]; then
    $HESTIA/bin/v-add-database-host pgsql localhost postgres $ppass
fi

# Adding default domain
$HESTIA/bin/v-add-web-domain admin $servername
check_result $? "can't create $servername domain"

# Adding cron jobs
export SCHEDULED_RESTART="yes"
command="sudo $HESTIA/bin/v-update-sys-queue restart"
$HESTIA/bin/v-add-cron-job 'admin' '*/2' '*' '*' '*' '*' "$command"
systemctl restart crond

command="sudo $HESTIA/bin/v-update-sys-queue daily"
$HESTIA/bin/v-add-cron-job 'admin' '10' '00' '*' '*' '*' "$command"
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

# Enable automatic updates
$HESTIA/bin/v-add-cron-hestia-autoupdate

# Building initital rrd images
$HESTIA/bin/v-update-sys-rrd

# Enabling file system quota
if [ "$quota" = 'yes' ]; then
    $HESTIA/bin/v-add-sys-quota
fi

# Set backend port
$HESTIA/bin/v-change-sys-port $port

# Set default theme
$HESTIA/bin/v-change-sys-theme 'default'

# Starting Hestia service
systemctl enable hestia-php
systemctl enable hestia-nginx
systemctl start hestia-php && systemctl start hestia-nginx
check_result $? "hestia start failed"


#----------------------------------------------------------#
#                  Configure FileManager                   #
#----------------------------------------------------------#

echo "(*) Configuring Filegator FileManager..."
source $HESTIA_INSTALL_DIR/filemanager/install-fm.sh > /dev/null 2>&1


#----------------------------------------------------------#
#                   Hestia Access Info                     #
#----------------------------------------------------------#

# Comparing hostname and IP
host_ip=$(host $servername| head -n 1 |awk '{print $NF}')
if [ "$host_ip" = "$ip" ]; then
    ip="$servername"
fi

echo -e "\n"
echo "===================================================================="
echo -e "\n"

# Sending notification to admin email
echo -e "Congratulations!

You have successfully installed Hestia Control Panel on your server.

Ready to get started? Log in using the following credentials:

    Admin URL:  https://$ip:$port
    Username:   admin
    Password:   $vpass

Thank you for choosing Hestia Control Panel to power your full stack web server,
we hope that you enjoy using it as much as we do!

Please feel free to contact us at any time if you have any questions,
or if you encounter any bugs or problems:

E-mail:  info@hestiacp.com
Web:     https://www.hestiacp.com/
Forum:   https://forum.hestiacp.com/
GitHub:  https://www.github.com/hestiacp/hestiacp

Note: Automatic updates are enabled by default. If you would like to disable them,
please log in and navigate to Server > Updates to turn them off.

Help support the Hestia Contol Panel project by donating via PayPal:
https://www.hestiacp.com/donate
--
Sincerely yours,
The Hestia Control Panel development team

Made with love & pride by the open-source community around the world.
" > $tmpfile

send_mail="$HESTIA/web/inc/mail-wrapper.php"
cat $tmpfile | $send_mail -s "Hestia Control Panel" $email

# Congrats
echo
cat $tmpfile
rm -f $tmpfile

# Add welcome message to notification panel
$HESTIA/bin/v-add-user-notification admin 'Welcome!' 'For more information on how to use Hestia Control Panel, click on the Help icon in the top right corner of the toolbar.<br><br>Please report any bugs or issues on GitHub at<br>https://github.com/hestiacp/hestiacp/issues<br><br>Have a great day!'

echo "(!) IMPORTANT: You must logout or restart the server before continuing."
echo ""
if [ "$interactive" = 'yes' ]; then
    echo -n " Do you want to reboot now? [Y/N] "
    read reboot

    if [ "$reboot" = "Y" ] || [ "$reboot" = "y" ]; then
        reboot
    fi
fi

# EOF