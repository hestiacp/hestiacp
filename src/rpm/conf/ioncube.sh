#!/bin/bash

VESTA='/usr/local/vesta'
ioncube="ioncube_loader_lin_5.6.so"
php='/usr/local/vesta/php/lib/php.ini'

# Check if extention is enabled
if [ -z "$(grep $ioncube $php |grep -v ';')" ]; then
    echo "zend_extension = '$VESTA/ioncube/$ioncube'" >> $php
    /etc/init.d/vesta restart
fi

exit
