#!/bin/bash

action=${1-add}
VESTA='/usr/local/vesta'
ioncube="ioncube_loader_lin_5.6.so"
php='/usr/local/vesta/php/lib/php.ini'

if [ ! -e "$php" ]; then
    exit
fi

if [ ! -e "$VESTA/ioncube/$ioncube" ]; then
    exit
fi

if [ "$action" = 'add' ]; then
    if [ -z "$(grep $ioncube $php |grep -v ';')" ]; then
        echo "zend_extension = '$VESTA/ioncube/$ioncube'" >> $php
        /etc/init.d/vesta restart
    fi
else
    if [ ! -z "$(grep $ioncube $php |grep -v ';')" ]; then
        sed -i "/$ioncube/d"  $php
        /etc/init.d/vesta restart
    fi
fi

exit
