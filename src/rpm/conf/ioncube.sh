#!/bin/bash

VESTA='/usr/local/vesta'
ioncube_loader="ioncube_loader_lin_5.6.so"
php_ini='/usr/local/vesta/php/lib/php.ini'

# Check if extention is enabled
if [ -z "$(grep $ioncube_loader $php_ini | grep -v ';')" ]; then
    echo "zend_extension = \"$VESTA/ioncube/$ioncube_loader\"" >> $php
    /etc/init.d/vesta restart
    exit
fi

exit
