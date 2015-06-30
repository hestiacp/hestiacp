#!/bin/bash

# Vesta RHEL/CentOS installer v.04

#----------------------------------------------------------#
#                  Variables&Functions                     #
#----------------------------------------------------------#
export PATH=$PATH:/sbin
RHOST='r.vestacp.com'
CHOST='c.vestacp.com'
REPO='cmmnt'
VERSION='0.9.8/rhel'
YUM_REPO='/etc/yum.repos.d/vesta.repo'
software="nginx httpd mod_ssl mod_ruid2 mod_extract_forwarded mod_fcgid
    php php-bcmath php-cli php-common php-gd php-imap php-mbstring php-mcrypt
    php-mysql php-pdo php-soap php-tidy php-xml php-xmlrpc quota e2fsprogs
    phpMyAdmin awstats webalizer vsftpd mysql mysql-server exim dovecot clamd
    spamassassin curl roundcubemail bind bind-utils bind-libs mc screen ftp
    libpng libjpeg libmcrypt mhash zip unzip openssl flex rssh libxml2
    ImageMagick sqlite pcre sudo bc jwhois mailx lsof tar telnet rrdtool
    fail2ban GeoIP freetype ntp openssh-clients vesta vesta-nginx vesta-php"

# Help
help() {
    echo "usage: $0 [OPTIONS]
   -h, --help                 Print this help and exit
   -f, --force                Force installation
   -i, --disable-iptables     Disable iptables support
   -b, --disable-fail2ban     Disable fail2ban protection
   -d, --disable-remi         Disable remi repository
   -n, --noupdate             Do not run yum update command
   -s, --hostname             Set server hostname
   -e, --email                Set email address
   -p, --password             Set admin password instead of generating it
   -m, --mysql-password       Set MySQL password instead of generating it
   -q, --quota                Enable File System Quota"
    exit 1
}

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


#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

# Translating argument to --gnu-long-options
for arg; do
    delim=""
    case "$arg" in
        --help)                 args="${args}-h " ;;
        --force)                args="${args}-f " ;;
        --disable-fail2ban)     args="${args}-b " ;;
        --disable-remi)         args="${args}-d " ;;
        --disable-iptables)     args="${args}-i " ;;
        --noupdate)             args="${args}-n " ;;
        --hostname)             args="${args}-s " ;;
        --email)                args="${args}-e " ;;
        --password)             args="${args}-p " ;;
        --mysql-password)       args="${args}-m " ;;
        --quota)                args="${args}-q " ;;
        *)              [[ "${arg:0:1}" == "-" ]] || delim="\""
                        args="${args}${delim}${arg}${delim} ";;
    esac
done
eval set -- "$args"

# Getopt
while getopts "hfibdnqe:m:p:s:" Option; do
    case $Option in
        h) help ;;                        # Help
        f) force='yes' ;;                 # Force install
        i) disable_iptables='yes' ;;      # Disable iptables
        b) disable_fail2ban='yes' ;;      # Disable fail2ban
        d) disable_remi='yes' ;;          # Disable remi repo
        n) noupdate='yes' ;;              # Disable yum update
        s) servername=$OPTARG ;;          # Server hostname
        e) email=$OPTARG ;;               # Set email
        p) vpass=$OPTARG ;;               # Admin password
        m) mpass=$OPTARG ;;               # MySQL pasword
        q) quota='yes' ;;                 # Enable quota
        *) help ;;                        # Default
    esac
done

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

# Check supported OS
arch=$(uname -i)
os=$(cut -f 1 -d ' ' /etc/redhat-release)
release=$(grep -o "[0-9]" /etc/redhat-release |head -n1)
codename="${os}_$release"
if [ $os !=  'CentOS' ] && [ $os != 'Red' ]; then
    echo 'Error: sorry, we currently support RHEL and CentOS only'
    exit
fi

# Check admin user account
if [ ! -z "$(grep ^admin: /etc/passwd)" ] && [ "$force" != 'yes' ]; then
    echo "Error: user admin exists"
    echo
    echo 'Please remove admin user account before proceeding.'
    echo 'If you want to do it automatically run installer with -f option:'
    echo "Example: bash $0 --force"
    exit 1
fi

# Check admin group
if [ ! -z "$(grep ^admin: /etc/group)" ] && [ "$force" != 'yes' ]; then
    echo "Error: group admin exists"
    echo
    echo 'Please remove admin group before proceeding.'
    echo 'If you want to do it automatically run installer with -f option:'
    echo "Example: bash $0 --force"
    exit 1
fi

# Check wget
if [ ! -e '/usr/bin/wget' ]; then
    yum -y install wget
    if [ $? -ne 0 ]; then
        echo "Error: can't install wget"
        exit 1
    fi
fi

# Check repo availability
wget -q "$CHOST/$VERSION/vesta.conf" -O /dev/null
if [ $? -ne 0 ]; then
    echo "Error: no access to $REPO repository"
    exit 1
fi

# Check installed packages
tmpfile=$(mktemp -p /tmp)
rpm -qa > $tmpfile
for pkg in exim bind-9 mysql-server httpd nginx vesta; do
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
    exit 1
fi

# Check server type
memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])
if [ "$memory" -lt '350000' ] && [ -z "$force" ]; then
    echo "Error: not enough memory to install Vesta Control Panel."
    echo -e "\nMinimum RAM required: 350Mb"
    echo 'If you want to force installation run this script with -f option:'
    echo "Example: bash $0 --force"
    exit 1
fi
srv_type='micro'

if [ "$memory" -gt '1000000' ]; then
    srv_type='small'
fi

if [ "$memory" -gt '3000000' ]; then
    srv_type='medium'
fi

if [ "$memory" -gt '7000000' ]; then
    srv_type='large'
fi

# Are you sure ?
if [ -z $email ]; then
    clear
    echo
    echo ' _|      _|  _|_|_|_|    _|_|_|  _|_|_|_|_|    _|_|     '
    echo ' _|      _|  _|        _|            _|      _|    _|   '
    echo ' _|      _|  _|_|_|      _|_|        _|      _|_|_|_|   '
    echo '   _|  _|    _|              _|      _|      _|    _|   '
    echo '     _|      _|_|_|_|  _|_|_|        _|      _|    _|   '
    echo
    echo '                                  Vesta Control Panel'
    echo
    echo
    echo 'Following software will be installed on your system:'
    echo '   - Nginx frontend web server'
    echo '   - Apache application web server'
    echo '   - Bind DNS server'
    echo '   - Exim mail server'
    echo '   - Dovecot IMAP and POP3 server'
    if [ "$srv_type" = 'medium' ] ||  [ "$srv_type" = 'large' ]; then
        echo '   - Clam mail antivirus'
        echo '   - SpamAssassin antispam'
    fi
    echo '   - MySQL database server'
    echo '   - Vsftpd FTP server'
    echo 
    echo '   * SELinux and Iptables will be disabled'
    echo 

    read -p 'Do you want to proceed? [y/n]): ' answer
    if [ "$answer" != 'y' ] && [ "$answer" != 'Y'  ]; then
        echo 'Goodbye'
        exit 1
    fi

    # Define email
    read -p 'Please enter valid email address: ' email

    # Define server hostname
    if [ -z "$servername" ]; then
        read -p "Please enter hostname [$(hostname)]: " servername
    fi
fi

# Validate email
local_part=$(echo $email | cut  -s -f1 -d\@)
remote_host=$(echo $email | cut -s -f2 -d\@)
mx_failed=1
if [ ! -z "$remote_host" ] && [ ! -z "$local_part" ]; then
    /usr/bin/host -t mx "$remote_host" > /dev/null 2>&1
    mx_failed="$?"
fi

if [ "$mx_failed" -eq 1 ]; then
    echo "Error: email $email is not valid"
    exit 1
fi

# Check for ipv6 on loopback interface
check_lo_ipv6=$(/sbin/ifconfig lo| grep 'inet6 addr')
check_rc_ipv6=$(grep 'scope global dev lo' /etc/rc.local)
if [ ! -z "$check_lo_ipv6)" ] && [ -z "$check_rc_ipv6" ]; then
    ip addr add ::2/128 scope global dev lo
    echo "# Vesta: Workraround for openssl validation func" >> /etc/rc.local
    echo "ip addr add ::2/128 scope global dev lo" >> /etc/rc.local
    chmod a+x /etc/rc.local
fi


#----------------------------------------------------------#
#                   Install repository                     #
#----------------------------------------------------------#
# Let's start
echo -e "\n\n\n\nInstallation will take about 15 minutes ...\n"
sleep 5

# Update system
if [ -z "$noupdate" ]; then
    yum -y update
    if [ $? -ne 0 ]; then
        echo 'Error: yum update failed'
        exit 1
    fi
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

# Install nginx repo
if [ ! -e '/etc/yum.repos.d/nginx.repo' ]; then
    echo "[nginx]" > /etc/yum.repos.d/nginx.repo
    echo "name=nginx repo" >> /etc/yum.repos.d/nginx.repo
    echo "baseurl=http://nginx.org/packages/centos/$release/\$basearch/" \
        >> /etc/yum.repos.d/nginx.repo
    echo "gpgcheck=0" >> /etc/yum.repos.d/nginx.repo
    echo "enabled=1" >> /etc/yum.repos.d/nginx.repo
fi

# Install vesta repo
echo "[vesta]" > $YUM_REPO
echo "name=Vesta - $REPO" >> $YUM_REPO
echo "baseurl=http://$RHOST/$REPO/$release/\$basearch/" >> $YUM_REPO
echo "enabled=1" >> $YUM_REPO
echo "gpgcheck=1" >> $YUM_REPO
echo "gpgkey=file:///etc/pki/rpm-gpg/RPM-GPG-KEY-VESTA" >> $YUM_REPO
wget $CHOST/GPG.txt -O /etc/pki/rpm-gpg/RPM-GPG-KEY-VESTA


#----------------------------------------------------------#
#                         Backups                          #
#----------------------------------------------------------#

# Prepare backup tree
vst_backups="/root/vst_install_backups/$(date +%s)"
mkdir -p $vst_backups/nginx
mkdir -p $vst_backups/httpd
mkdir -p $vst_backups/mysql
mkdir -p $vst_backups/exim
mkdir -p $vst_backups/dovecot
mkdir -p $vst_backups/clamd
mkdir -p $vst_backups/spamassassin
mkdir -p $vst_backups/vsftpd
mkdir -p $vst_backups/named
mkdir -p $vst_backups/vesta/admin
mkdir -p $vst_backups/home

# Backup sudoers
if [ -e '/etc/sudoers' ]; then
    cp /etc/sudoers $vst_backups/
fi

# Backup nginx
service nginx stop > /dev/null 2>&1
if [ -e '/etc/nginx/nginx.conf' ]; then
    cp -r /etc/nginx/* $vst_backups/nginx/
fi

# Backup httpd
service httpd stop > /dev/null 2>&1
if [ -e '/etc/httpd/conf/httpd.conf' ]; then
    cp -r /etc/httpd/* $vst_backups/httpd/
fi

# Backup bind
service named stop > /dev/null 2>&1
if [ -e '/etc/named.conf' ]; then
    cp /etc/named.conf $vst_backups/named/
fi

# Backup vsftpd
service vsftpd stop > /dev/null 2>&1
if [ -e '/etc/vsftpd/vsftpd.conf' ]; then
    cp /etc/vsftpd/vsftpd.conf $vst_backups/vsftpd/
fi

# Backup exim
service exim stop > /dev/null 2>&1
if [ -e '/etc/exim/exim.conf' ]; then
    cp -r /etc/exim/* $vst_backups/exim/
fi

# Backup clamav
service clamd stop > /dev/null 2>&1
if [ -e '/etc/clamd.conf' ]; then
    cp /etc/clamd.conf $vst_backups/clamd/
fi

# Backup SpamAssassin
service spamassassin stop > /dev/null 2>&1
if [ -e '/etc/mail/spamassassin' ]; then
    cp -r /etc/mail/spamassassin/* $vst_backups/spamassassin/
fi

# Backup dovecot
service dovecot stop > /dev/null 2>&1
if [ -e '/etc/dovecot.conf' ]; then
    cp /etc/dovecot.conf $vst_backups/dovecot/
fi
if [ -e '/etc/dovecot' ]; then
    cp -r /etc/dovecot/* $vst_backups/dovecot/
fi

# Backup MySQL stuff
service mysqld stop > /dev/null 2>&1
if [ -e '/var/lib/mysql' ]; then
    mv /var/lib/mysql $vst_backups/mysql/mysql_datadir
fi
if [ -e '/etc/my.cnf' ]; then 
    cp /etc/my.cnf $vst_backups/mysql/
fi
if [ -e '/root/.my.cnf' ]; then
    mv /root/.my.cnf  $vst_backups/mysql/
fi

# Backup vesta
service vesta stop > /dev/null 2>&1
if [ -e '/usr/local/vesta/data' ]; then
    mv /usr/local/vesta/data $vst_backups/vesta/
fi

if [ -e '/usr/local/vesta/conf' ]; then
    mv /usr/local/vesta/conf $vst_backups/vesta/
fi

if [ -e '/home/admin/conf/' ]; then
    mv /home/admin/conf/ $vst_backups/vesta/admin
fi


#----------------------------------------------------------#
#                     Install packages                     #
#----------------------------------------------------------#

# Exclude heavy packages
if [ "$srv_type" = 'micro' ]; then
    software=$(echo "$software" | sed -e 's/mod_fcgid//')
    software=$(echo "$software" | sed -e 's/clamd//')
    software=$(echo "$software" | sed -e 's/spamassassin//')
fi

if [ "$srv_type" = 'small' ]; then
    software=$(echo "$software" | sed -e 's/clamd//')
    software=$(echo "$software" | sed -e 's/spamassassin//')
fi

# Exclude fail2ban
if [ "$disable_fail2ban" = 'yes' ]; then
    software=$(echo "$software" | sed -e 's/fail2ban//')
fi

# Install Vesta packages
if [ -z "$disable_remi" ]; then 
    yum -y --disablerepo=* --enablerepo="base,updates,nginx,epel,vesta,remi" \
        install $software
else
    yum -y --disablerepo=* --enablerepo="base,updates,nginx,epel,vesta" \
        install $software
fi
if [ $? -ne 0 ]; then
    echo 'Error: yum install failed'
    exit 1
fi


#----------------------------------------------------------#
#                     Configure system                     #
#----------------------------------------------------------#

# Set writable permission on tmp directory
chmod 777 /tmp

# Disabling SELinux
if [ -e '/etc/sysconfig/selinux' ]; then
    sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/sysconfig/selinux
    setenforce 0
fi
if [ -e '/etc/selinux/config' ]; then
    sed -i 's/SELINUX=enforcing/SELINUX=disabled/g' /etc/selinux/config
    setenforce 0
fi

# Vesta configuration
echo "export VESTA='/usr/local/vesta'" > /etc/profile.d/vesta.sh
chmod 755 /etc/profile.d/vesta.sh
source /etc/profile.d/vesta.sh
echo 'PATH=$PATH:/usr/local/vesta/bin' >> /root/.bash_profile
echo 'export PATH' >> /root/.bash_profile
source /root/.bash_profile
wget $CHOST/$VERSION/vesta.log -O /etc/logrotate.d/vesta

# Directory tree
mkdir -p $VESTA/conf
mkdir -p $VESTA/log
mkdir -p $VESTA/ssl
mkdir -p $VESTA/data
mkdir -p $VESTA/data/ips
mkdir -p $VESTA/data/queue
mkdir -p $VESTA/data/users
mkdir -p $VESTA/data/firewall
touch $VESTA/data/queue/backup.pipe
touch $VESTA/data/queue/disk.pipe
touch $VESTA/data/queue/webstats.pipe
touch $VESTA/data/queue/restart.pipe
touch $VESTA/data/queue/traffic.pipe
chmod 750 $VESTA/conf
chmod 750 $VESTA/data/users
chmod 750 $VESTA/data/ips
chmod -R 750 $VESTA/data/queue
ln -s /usr/local/vesta/log /var/log/vesta
touch /var/log/vesta/system.log
touch /var/log/vesta/nginx-error.log
touch /var/log/vesta/auth.log
chmod 660 /var/log/vesta/*
adduser backup
ln -s /home/backup /backup
chmod a+x /backup

# vesta.conf
wget $CHOST/$VERSION/vesta.conf -O $VESTA/conf/vesta.conf
if [ "$srv_type" = 'micro' ] ||  [ "$srv_type" = 'small' ]; then
    sed -i "s/clamav//g" $VESTA/conf/vesta.conf
    sed -i "s/spamassassin//g" $VESTA/conf/vesta.conf
fi

# Set server hostname
if [ -z "$servername" ]; then
    servername=$(hostname)
fi
/usr/local/vesta/bin/v-change-sys-hostname $servername 2>/dev/null

# Templates
cd /usr/local/vesta/data
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
if [ "$srv_type" = 'micro' ]; then
    rm -f /usr/local/vesta/data/templates/web/httpd/phpfcgid.*
fi

# Generating SSL certificate
$VESTA/bin/v-generate-ssl-cert $(hostname) $email 'US' 'California' \
     'San Francisco' 'Vesta Control Panel' 'IT' > /tmp/vst.pem

# Parsing merged certificate file
crt_end=$(grep -n "END CERTIFICATE-" /tmp/vst.pem |cut -f 1 -d:)
key_start=$(grep -n "BEGIN RSA" /tmp/vst.pem |cut -f 1 -d:)
key_end=$(grep -n  "END RSA" /tmp/vst.pem |cut -f 1 -d:)

# Adding SSL certificate
cd /usr/local/vesta/ssl
sed -n "1,${crt_end}p" /tmp/vst.pem > certificate.crt
sed -n "$key_start,${key_end}p" /tmp/vst.pem > certificate.key
chown root:mail /usr/local/vesta/ssl/*
chmod 660 /usr/local/vesta/ssl/*
rm /tmp/vst.pem

# Disabling webalizer routine
rm -f /etc/cron.daily/00webalizer

# Set directory color
echo 'LS_COLORS="$LS_COLORS:di=00;33"' >> /etc/profile

# Sudo configuration
wget $CHOST/$VERSION/sudoers.conf -O /etc/sudoers
wget $CHOST/$VERSION/sudoers.admin.conf -O /etc/sudoers.d/admin
chmod 440 /etc/sudoers
chmod 440 /etc/sudoers.d/admin

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

# Nginx configuration
rm -f /etc/nginx/conf.d/*.conf
wget $CHOST/$VERSION/nginx.conf -O /etc/nginx/nginx.conf
wget $CHOST/$VERSION/nginx-status.conf -O /etc/nginx/conf.d/status.conf
touch /etc/nginx/conf.d/vesta.conf
chkconfig nginx on
service nginx start
if [ "$?" -ne 0 ]; then
    echo "Error: nginx start failed"
    exit 1
fi

# Apache configuration
wget $CHOST/$VERSION/httpd.conf -O /etc/httpd/conf/httpd.conf
wget $CHOST/$VERSION/httpd-status.conf -O /etc/httpd/conf.d/status.conf
wget $CHOST/$VERSION/httpd-ssl.conf -O /etc/httpd/conf.d/ssl.conf
wget $CHOST/$VERSION/httpd.log -O /etc/logrotate.d/httpd
echo "MEFaccept 127.0.0.1" >> /etc/httpd/conf.d/mod_extract_forwarded.conf
rm -f /etc/httpd/conf.d/proxy_ajp.conf
echo > /etc/httpd/conf.d/proxy_ajp.conf
rm -f /etc/httpd/conf.d/vesta.conf
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
chkconfig httpd on
service httpd start
if [ "$?" -ne 0 ]; then
    echo "Error: httpd start failed"
    exit 1
fi

# Vsftpd configuration
wget $CHOST/$VERSION/vsftpd.conf -O /etc/vsftpd/vsftpd.conf
chkconfig vsftpd on
service vsftpd start
if [ "$?" -ne 0 ]; then
    echo "Error: vsftpd start failed"
    exit 1
fi

# MySQL configuration
if [ "$srv_type" = 'micro' ]; then
    wget $CHOST/$VERSION/mysql-512.cnf -O /etc/my.cnf
else
    wget $CHOST/$VERSION/mysql.cnf -O /etc/my.cnf
fi
chkconfig mysqld on
service mysqld start
if [ "$?" -ne 0 ]; then
    # Fix for aio on OpenVZ
    if [ -e "/proc/user_beancounters" ]; then
        sed -i "s/#innodb_use_native_aio/innodb_use_native_aio/g" /etc/my.cnf
    fi

    service mysqld start
    if [ "$?" -ne 0 ]; then
        echo "Error: mysqld start failed"
        exit 1
    fi
fi

# Generating MySQL password if it wasn't set
if [ -z "$mpass" ]; then
    mpass=$(gen_pass)
fi

mysqladmin -u root password $mpass
echo -e "[client]\npassword='$mpass'\n" > /root/.my.cnf
chmod 600 /root/.my.cnf
mysql -e "DELETE FROM mysql.user WHERE User=''"
mysql -e "DROP DATABASE test"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%'"
mysql -e "DELETE FROM mysql.user WHERE user='' or password='';"
mysql -e "FLUSH PRIVILEGES"

# Bind configuration
wget $CHOST/$VERSION/named.conf -O /etc/named.conf
chown root:named /etc/named.conf
chmod 640 /etc/named.conf
chkconfig named on
service named start
if [ "$?" -ne 0 ]; then
    echo "Error: named start failed"
    exit 1
fi

# Exim
wget $CHOST/$VERSION/exim.conf -O /etc/exim/exim.conf
if [ "$srv_type" != 'micro' ] &&  [ "$srv_type" != 'small' ]; then
    sed -i "s/#SPAM/SPAM/g" /etc/exim/exim.conf
    sed -i "s/#CLAMD/CLAMD/g" /etc/exim/exim.conf
fi
wget $CHOST/$VERSION/dnsbl.conf -O /etc/exim/dnsbl.conf
wget $CHOST/$VERSION/spam-blocks.conf -O /etc/exim/spam-blocks.conf
touch /etc/exim/white-blocks.conf
rm -rf /etc/exim/domains
mkdir -p /etc/exim/domains
chmod 640 /etc/exim/exim.conf
gpasswd -a exim mail
if [ -e /etc/init.d/sendmail ]; then
    chkconfig sendmail off
    service sendmail stop
fi
if [ -e /etc/init.d/postfix ]; then
    chkconfig postfix off
    service postfix stop
fi
rm -f /etc/alternatives/mta
ln -s /usr/sbin/sendmail.exim /etc/alternatives/mta
chkconfig exim on
service exim start
if [ "$?" -ne 0 ]; then
    echo "Error: exim start failed"
    exit 1
fi

# Dovecot configuration
if [ "$release" -eq '5' ]; then
    wget $CHOST/$VERSION/dovecot.conf -O /etc/dovecot.conf
else
    wget $CHOST/$VERSION/$release/dovecot.tar.gz -O  /etc/dovecot.tar.gz
    cd /etc
    if [ -d /etc/dovecot ]; then
        rm -rf /etc/dovecot
    fi
    if [ -f /etc/dovecot.conf ]; then
        rm /etc/dovecot.conf
    fi
    tar -xzf dovecot.tar.gz
    rm -f dovecot.tar.gz
    if [ -d /etc/dovecot ]; then
        chown -R root:root /etc/dovecot
    fi
fi
gpasswd -a dovecot mail
chkconfig dovecot on
service dovecot start
if [ "$?" -ne 0 ]; then
    echo "Error: dovecot start failed"
    exit 1
fi

# ClamAV configuration
if [ "$srv_type" = 'medium' ] ||  [ "$srv_type" = 'large' ]; then
    wget $CHOST/$VERSION/clamd.conf -O /etc/clamd.conf
    wget $CHOST/$VERSION/freshclam.conf -O /etc/freshclam.conf
    gpasswd -a clam exim
    gpasswd -a clam mail
    /usr/bin/freshclam
    chkconfig clamd on
    service clamd start
    if [ "$?" -ne 0 ]; then
        echo "Error: clamd start failed"
        exit 1
    fi
fi

# SpamAssassin configuration
if [ "$srv_type" = 'medium' ] ||  [ "$srv_type" = 'large' ]; then
    chkconfig spamassassin on
    service spamassassin start
    if [ "$?" -ne 0 ]; then
        echo "Error: spamassassin start failed"
        exit 1
    fi
fi

# Fail2ban configuration
if [ -z "$disable_fail2ban" ]; then
    cd /etc
    wget $CHOST/$VERSION/fail2ban.tar.gz -O fail2ban.tar.gz
    tar -xzf fail2ban.tar.gz
    rm -f fail2ban.tar.gz
    chkconfig fail2ban on
    service fail2ban start
else
    sed -i "s/fail2ban//" $VESTA/conf/vestac.conf
fi

# php configuration
sed -i 's/short_open_tag = Off/short_open_tag = On/g' /etc/php.ini
sed -i "s/;date.timezone =/date.timezone = UTC/g" /etc/php.ini

# phpMyAdmin configuration
wget $CHOST/$VERSION/httpd-pma.conf -O /etc/httpd/conf.d/phpMyAdmin.conf
wget $CHOST/$VERSION/pma.conf -O /etc/phpMyAdmin/config.inc.php
sed -i "s/%blowfish_secret%/$(gen_pass)/g" /etc/phpMyAdmin/config.inc.php

# Roundcube configuration
wget $CHOST/$VERSION/httpd-webmail.conf -O /etc/httpd/conf.d/roundcubemail.conf
wget $CHOST/$VERSION/roundcube-main.conf -O /etc/roundcubemail/main.inc.php
wget $CHOST/$VERSION/roundcube-db.conf -O /etc/roundcubemail/db.inc.php
wget $CHOST/$VERSION/roundcube-driver.php -O \
    /usr/share/roundcubemail/plugins/password/drivers/vesta.php
wget $CHOST/$VERSION/roundcube-pw.conf -O \
    /usr/share/roundcubemail/plugins/password/config.inc.php
r="$(gen_pass)"
mysql -e "CREATE DATABASE roundcube"
mysql -e "GRANT ALL ON roundcube.* TO roundcube@localhost IDENTIFIED BY '$r'"
sed -i "s/%password%/$r/g" /etc/roundcubemail/db.inc.php
if [ -e "/usr/share/roundcubemail/SQL/mysql.initial.sql" ]; then
    mysql roundcube < /usr/share/roundcubemail/SQL/mysql.initial.sql
else
    mysql roundcube < /usr/share/doc/roundcubemail-*/SQL/mysql.initial.sql
fi

# Adding admin user
if [ ! -z "$(grep ^admin: /etc/passwd)" ] && [ "$force" = 'yes' ]; then
    chattr -i /home/admin/conf > /dev/null 2>&1
    userdel -f admin
    chattr -i /home/admin/conf
    mv -f /home/admin  $vst_backups/home/
    rm -f /tmp/sess_*
fi
if [ ! -z "$(grep ^admin: /etc/group)" ] && [ "$force" = 'yes' ]; then
    groupdel admin > /dev/null 2>&1
fi

# Generating admin password if it wasn't set
if [ -z "$vpass" ]; then
    vpass=$(gen_pass)
fi

# Adding vesta account
$VESTA/bin/v-add-user admin $vpass $email default System Administrator
if [ $? -ne 0 ]; then
    echo "Error: can't create admin user"
    exit 1
fi
$VESTA/bin/v-change-user-shell admin bash
$VESTA/bin/v-change-user-language admin en

# Configuring mysql host
$VESTA/bin/v-add-database-host mysql localhost root $mpass
$VESTA/bin/v-add-database admin default default $(gen_pass) mysql

# Configuring system ips
$VESTA/bin/v-update-sys-ip

# Firewall configuration
wget $CHOST/$VERSION/firewall.tar.gz -O firewall.tar.gz
tar -xzf firewall.tar.gz
rm -f firewall.tar.gz
if [ "$disable_iptables" = 'yes' ]; then
    sed -i "s/iptables//" $VESTA/conf/vesta.conf
    chkconfig iptables off
    service iptables stop
else
    /usr/local/vesta/bin/v-update-firewall
fi

# Get main ip
main_ip=$(ifconfig |grep 'inet addr:' |grep -v 127.0.0.1 |head -n1 | \
    cut -f2 -d: | cut -f1 -d ' ')

# Get remote ip
vst_ip=$(wget vestacp.com/what-is-my-ip/ -O - 2>/dev/null)
if [ ! -z "$vst_ip" ] && [ "$vst_ip" != "$main_ip" ]; then
    # Set NAT association
    $VESTA/bin/v-change-sys-ip-nat $main_ip $vst_ip
fi
if [ -z "$vst_ip" ]; then
    vst_ip=$main_ip
fi

# Add default web domain
$VESTA/bin/v-add-web-domain admin default.domain $vst_ip

# Add default dns domain
$VESTA/bin/v-add-dns-domain admin default.domain $vst_ip

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
$VESTA/bin/v-add-cron-job 'admin' '*/5' '*' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-backup-users'
$VESTA/bin/v-add-cron-job 'admin' '10' '05' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-update-user-stats'
$VESTA/bin/v-add-cron-job 'admin' '20' '00' '*' '*' '*' "$command"
command='sudo /usr/local/vesta/bin/v-update-sys-rrd'
$VESTA/bin/v-add-cron-job 'admin' '*/5' '*' '*' '*' '*' "$command"

# Build inititall rrd images
$VESTA/bin/v-update-sys-rrd

# Enable file system quota
if [ "$quota" = 'yes' ]; then
    $VESTA/bin/v-add-sys-quota
fi

# Start system service
chkconfig vesta on
service vesta start
if [ "$?" -ne 0 ]; then
    echo "Error: vesta start failed"
    exit 1
fi

# Send notification to vestacp.com
wget vestacp.com/notify/?$codename -O /dev/null

# Send notification to admin email
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

send_mail="$VESTA/web/inc/mail-wrapper.php"
cat $tmpfile | $send_mail -s "Vesta Control Panel" $email
rm -f $tmpfile

# Congrats
echo '======================================================='
echo
echo
echo ' _|      _|  _|_|_|_|    _|_|_|  _|_|_|_|_|    _|_|   '
echo ' _|      _|  _|        _|            _|      _|    _| '
echo ' _|      _|  _|_|_|      _|_|        _|      _|_|_|_| '
echo '   _|  _|    _|              _|      _|      _|    _| '
echo '     _|      _|_|_|_|  _|_|_|        _|      _|    _| '
echo
echo
echo '-------------------------------'
echo "  https://$vst_ip:8083"
echo '  username: admin'
echo "  password: $vpass"
echo '-------------------------------'
echo
echo
echo 'Congratulations,'
echo 'you have successfully installed Vesta Control Panel.'
echo
echo

# EOF
