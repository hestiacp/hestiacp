#!/bin/bash

RHOST='r.vestacp.com'
CHOST='c.vestacp.com'
REPO='cmmnt'
VERSION='rhel'
VESTA='/usr/local/vesta'
os=$(cut -f 1 -d ' ' /etc/redhat-release)
release=$(grep -o "[0-9]" /etc/redhat-release |head -n1)
codename="${os}_$release"
vestacp="http://$CHOST/$VERSION/$release"
servername=$(hostname -f)


# PATH fix
if [ ! -f "/etc/profile.d/vesta.sh" ]; then
    echo "export VESTA='$VESTA'" > /etc/profile.d/vesta.sh
fi
if [ $( grep -ic "vesta" /root/.bash_profile ) -eq 0 ]; then
    echo 'PATH=$PATH:'$VESTA'/bin' >> /root/.bash_profile
fi


# Linking /var/log/vesta
if [ ! -L "/var/log/vesta" ]; then
    ln -s $VESTA/log /var/log/vesta
fi


# Added default install "expect" to work for backup sftp
yum -y install expect > /dev/null 2>&1


# Roundcube Vesta password driver - changing password_vesta_host (in config) to server hostname 
if [ -f "/usr/share/roundcubemail/plugins/password/config.inc.php" ]; then
    sed -i "s/localhost/$servername/g" /usr/share/roundcubemail/plugins/password/config.inc.php
fi


# Workaround for OpenVZ/Virtuozzo
if [ "$release" -eq '7' ] && [ -e "/proc/vz/veinfo" ]; then
    if [ $( grep -ic "Vesta: workraround for networkmanager" /etc/rc.local ) -eq 0 ]; then
        if [ -f "/etc/nginx/nginx.conf" ] ; then
            echo "#Vesta: workraround for networkmanager" >> /etc/rc.local
            echo "sleep 3 && service nginx restart" >> /etc/rc.local
        fi
        if [ -f "/etc/httpd/conf/httpd.conf" ] ; then
            echo "#Vesta: workraround for networkmanager" >> /etc/rc.local
            echo "sleep 2 && service httpd restart" >> /etc/rc.local
        fi
    fi
fi


# Fix for Spamassassin user_prefs
if [ -f "/etc/mail/spamassassin/local.cf" ] ; then
    if [ ! -d "/var/lib/spamassassin" ] ; then
        if [ "$release" -eq '7' ]; then
            groupadd -g 1001 spamd
            useradd -u 1001 -g spamd -s /sbin/nologin -d \
                /var/lib/spamassassin spamd
            mkdir /var/lib/spamassassin
            chown spamd:spamd /var/lib/spamassassin
        fi
    fi
fi


# Fix for clamav: /var/run ownership and foreground option
if [ -f "/etc/clamd.conf" ] ; then
    if [ ! -d "/var/run/clamav" ]; then
        mkdir /var/run/clamav
    fi
    chown -R clam:clam /var/run/clamav
    chown -R clam:clam /var/log/clamav
    if [ "$release" -eq '7' ]; then
        sed -i "s/nofork/foreground/" /usr/lib/systemd/system/clamd.service
        file="/usr/lib/systemd/system/clamd.service"
        if [ $( grep -ic "mkdir" $file ) -eq 0 ]; then
            sed -i "s/Type = simple/Type = simple\nExecStartPre = \/usr\/bin\/mkdir -p \/var\/run\/clamav\nExecStartPre = \/usr\/bin\/chown -R clam:clam \/var\/run\/clamav/g" $file
        fi
        systemctl daemon-reload
        /bin/systemctl restart clamd.service
    fi
fi


# Fixing empty NAT ip
ip=$(ip addr|grep 'inet '|grep global|head -n1|awk '{print $2}'|cut -f1 -d/)
pub_ip=$(curl -s vestacp.com/what-is-my-ip/)
file="$VESTA/data/ips/$ip"
if [ -f "$file" ] && [ $( grep -ic "NAT=''" $file ) -eq 1 ]; then
    if [ ! -z "$pub_ip" ] && [ "$pub_ip" != "$ip" ]; then
        v-change-sys-ip-nat $ip $pub_ip
    fi
fi

# Dovecot logrorate script
wget $vestacp/logrotate/dovecot -O /etc/logrotate.d/dovecot
