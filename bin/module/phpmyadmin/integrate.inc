#!/bin/bash

hestia_module_phpmyadmin_integrate() {
    source $HESTIA/bin/module/func.inc
    
    # FIXME: use Nginx-web config when using Nginx proxy (ignore apache in that case)
    if [ "$WEB_SYSTEM" = 'nginx' ]; then
        # Setup Nginx config
        cp -f $HESTIA_INSTALL_DIR/nginx/phpmyadmin.inc $OSAL_PATH_NGINX_CONF_D/
        # Remove apache support        
        hestia_safe_rm $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf
    elif [ "$WEB_SYSTEM" = 'apache2' ] || [ "$WEB_SYSTEM" = 'httpd' ]; then
        # Setup Apache config
        cp -f $HESTIA_INSTALL_DIR/pma/apache.conf /etc/phpmyadmin/
        ln -sf /etc/phpmyadmin/apache.conf $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf
        # Remove Nginx support
        hestia_safe_rm $OSAL_PATH_NGINX_CONF_D/phpmyadmin.inc
    fi
}
