#!/bin/bash
# Switch apache to remoteip module

# Checking rpaf config
if [ ! -e "/etc/apache2/mods-enabled/rpaf.load" ]; then
    exit
fi

# Checking remoteip module
if [ ! -e "/etc/apache2/mods-available/remoteip.load" ]; then
    exit
fi

if [ -f "/etc/apache2/mods-enabled/remoteip.load" ]; then
    echo "RemoteIP is already activated"
    exit
fi

# Disabling rpaf
/usr/sbin/a2dismod rpaf > /dev/null 2>&1
rm -f /etc/apache2/mods-enabled/rpaf.conf

# Enabling remoteip
/usr/sbin/a2enmod remoteip > /dev/null 2>&1

# Creating configuration
conf="/etc/apache2/mods-enabled/remoteip.conf"
echo "<IfModule remoteip_module>" > $conf
echo "    RemoteIPHeader X-Real-IP" >> $conf
for ip in $(ls /usr/local/vesta/data/ips); do
    echo "    RemoteIPInternalProxy $ip" >> $conf
done
echo "</IfModule>" >> $conf

sed -i "s/LogFormat \"%h/LogFormat \"%a/g" /etc/apache2/apache2.conf

# Restarting apache
/usr/sbin/apachectl restart > /dev/null 2>&1

# EOF
exit
