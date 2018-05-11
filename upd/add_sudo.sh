#!/bin/bash
# New sudoers format

if [ ! -e '/etc/sudoers.d/admin' ]; then
    if [ ! -d '/etc/sudoers.d' ]; then
        mkdir /etc/sudoers.d
        chmod 750 /etc/sudoers.d
    fi
    echo '# Created by vesta update-trigger' > /etc/sudoers.d/admin
    echo 'Defaults env_keep="VESTA"' >> /etc/sudoers.d/admin
    echo 'Defaults:admin !syslog' >> /etc/sudoers.d/admin
    echo 'Defaults:admin !requiretty' >> /etc/sudoers.d/admin
    echo 'Defaults:root !requiretty' >> /etc/sudoers.d/admin
    echo '' >> /etc/sudoers.d/admin
    echo 'admin   ALL=(ALL)       ALL' >> /etc/sudoers.d/admin
    echo 'admin   ALL=NOPASSWD:/usr/local/vesta/bin/*' >> /etc/sudoers.d/admin
    chmod 440 /etc/sudoers.d/admin

    if [ -z "$(grep /etc/sudoers.d /etc/sudoers)" ]; then
        echo -e "\n#includedir /etc/sudoers.d" >> /etc/sudoers
    fi
fi
