#!/bin/bash

hestia_module_php-fpm_integrate() {
    source $HESTIA/bin/module/php-fpm/func.inc
    
    if [ "$OS_BASE" = 'debian' ]; then    
        update-alternatives --set php /usr/bin/php$PHP_DEFAULT_VERSION > /dev/null 2>&1
    elif [ "$OS_BASE" == 'rhel' ]; then
        local php_withoutdot=${PHP_DEFAULT_VERSION//.}
        ln -sf /usr/bin/php$php_withoutdot /usr/bin/php
        echo ln -sf /usr/bin/php$php_withoutdot /usr/bin/php
    fi

    hestia package web-server integrate
}
