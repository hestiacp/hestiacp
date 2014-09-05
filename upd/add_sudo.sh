#!/bin/bash

if [ ! -e /etc/sudoers.d/admin ]; then
    echo "# Created by vesta update-trigger" > /etc/sudoers.d/admin
    echo "# $(date)" >> /etc/sudoers.d/admin
    echo "admin   ALL=(ALL)       ALL" >> /etc/sudoers.d/admin
    echo "admin   ALL=NOPASSWD:/usr/local/vesta/bin/*" >> /etc/sudoers.d/admin
    chmod 440 /etc/sudoers.d/admin

    if [ -z "$(grep /etc/sudoers.d /etc/sudoers)" ]; then
        echo -e "\n#includedir /etc/sudoers.d" >> /etc/sudoers
    fi
fi
