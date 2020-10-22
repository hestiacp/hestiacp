#!/bin/bash

hestia_package_web-server_integrate() {
    source $HESTIA/bin/module/func.inc

    osal_service_stop $OSAL_SERVICE_NGINX > /dev/null 2>&1
    osal_service_stop $OSAL_SERVICE_APACHE > /dev/null 2>&1

    local apache_installed=$(hestia_module_isinstalled apache && echo 1)
    local nginx_installed=$(hestia_module_isinstalled nginx && echo 1)

    if [ "$apache_installed" ] && [ ! "$nginx_installed" ] ; then
        echo "Setup Web module (Apache only)..."
        WEB_SYSTEM=$OSAL_PKG_APACHE
        WEB_RGROUPS=$OSAL_USER_APACHE_DATA
        WEB_PORT='80'
        WEB_SSL_PORT='443'
        WEB_SSL='mod_ssl'
        PROXY_SYSTEM=''
        PROXY_PORT=''
        PROXY_SSL_PORT=''
        osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'service_name' $OSAL_SERVICE_APACHE
    elif [ "$apache_installed" ] && [ "$nginx_installed" ] ; then
        echo "Setup Web module (Apache + Nginx)..."
        WEB_SYSTEM=$OSAL_PKG_APACHE
        WEB_RGROUPS=$OSAL_USER_APACHE_DATA
        WEB_PORT='8080'
        WEB_SSL_PORT='8443'
        WEB_SSL='mod_ssl'
        PROXY_SYSTEM='nginx'
        PROXY_PORT='80'
        PROXY_SSL_PORT='443'
        osal_kv_write $HESTIA_CONF_MODULES/apache.conf 'service_name' $OSAL_SERVICE_APACHE
        osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'service_name' $OSAL_SERVICE_NGINX
    elif [ ! "$apache_installed" ] && [ "$nginx_installed" ]; then
        echo "Setup Web module (Nginx only)..."
        WEB_SYSTEM='nginx'
        WEB_RGROUPS=''
        WEB_PORT='80'
        WEB_SSL_PORT='443'
        WEB_SSL='openssl'
        PROXY_SYSTEM=''
        PROXY_PORT=''
        PROXY_SSL_PORT=''
        osal_kv_write $HESTIA_CONF_MODULES/nginx.conf 'service_name' $OSAL_SERVICE_NGINX
    else
        echo "Setup web module (no web server)..."
        WEB_SYSTEM=''
        WEB_RGROUPS=''
        WEB_PORT='80'
        WEB_SSL_PORT='443'
        WEB_SSL='openssl'
        PROXY_SYSTEM=''
        PROXY_PORT=''
        PROXY_SSL_PORT=''
    fi

    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_SYSTEM' $WEB_SYSTEM
    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_RGROUPS' $WEB_RGROUPS
    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_PORT' $WEB_PORT
    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_SSL_PORT' $WEB_SSL_PORT
    osal_kv_write $HESTIA/conf/hestia.conf 'WEB_SSL' $WEB_SSL
    osal_kv_write $HESTIA/conf/hestia.conf 'PROXY_SYSTEM' $PROXY_SYSTEM
    osal_kv_write $HESTIA/conf/hestia.conf 'PROXY_PORT' $PROXY_PORT
    osal_kv_write $HESTIA/conf/hestia.conf 'PROXY_SSL_PORT' $PROXY_SSL_PORT



    # *** PHP (FPM and CLI)
    if hestia_module_isinstalled 'php'; then
        echo "Integrating PHP..."
        source $HESTIA/bin/module/php/func.inc
        for php_version in $PHP_SUPPORTED_VERSIONS; do
            local php_withoutdot=${php_version//.}
            php_version_present=$(osal_kv_read $HESTIA_CONF_MODULES/php.conf "php${php_withoutdot}_present" 0)
            if [ "$php_version_present" == "1" ]; then
                echo " Enabling PHP $php_version"
                local php_prefix=$(osal_php_package_prefix $php_version)
                osal_service_enable $php_prefix-fpm
                osal_service_restart $php_prefix-fpm
            fi
        done

        if hestia_module_isinstalled 'apache'; then
            # Enable mod_ruid/mpm_itk or mpm_event
            # Disable prefork and php, enable event
            osal_apache_module_disable php$fpm_v > /dev/null 2>&1
            osal_apache_module_disable mpm_prefork > /dev/null 2>&1
            osal_apache_module_enable mpm_event > /dev/null 2>&1
            cp -f $HESTIA_INSTALL_DIR/$WEB_SYSTEM/hestia-event.conf $OSAL_PATH_APACHE_CONF_D/
        fi

        # Update default CLI version
        if [ "$OS_BASE" = 'debian' ]; then    
            update-alternatives --set php /usr/bin/php$PHP_DEFAULT_VERSION > /dev/null 2>&1
        elif [ "$OS_BASE" == 'rhel' ]; then
            local php_withoutdot=${PHP_DEFAULT_VERSION//.}
            ln -sf /usr/bin/php$php_withoutdot /usr/bin/php
        fi
    fi



    # *** Rebuild IPs and domains
    if [ "$apache_installed" ] || [ "$nginx_installed" ] ; then
        # Install default success page
        mkdir -p /var/www/html
        mkdir -p /var/www/document_errors
        if [ ! -f /var/www/html/index.html ]; then
            cp -rf $HESTIA_INSTALL_DIR/templates/web/unassigned/index.html /var/www/html/
            cp -rf $HESTIA_INSTALL_DIR/templates/web/skel/document_errors/* /var/www/document_errors/
        fi

        hestia package web-server setup-ips
        
        # Rebuild domains
        echo "Rebuilding web domains..."
        for user in $($HESTIA/bin/v-list-sys-users plain); do
            $BIN/v-rebuild-web-domains $user 'no' > /dev/null 2>&1
        done
    fi



    # phpMyAdmin
    if hestia_module_isinstalled 'phpmyadmin'; then
        echo "Integrating phpMyAdmin..."
        # FIXME: use Nginx-web config when using Nginx proxy (ignore apache in that case)
        if [ "$WEB_SYSTEM" = 'nginx' ]; then
            # Setup Nginx config
            cp -f $HESTIA_INSTALL_DIR/nginx/phpmyadmin.inc $OSAL_PATH_NGINX_CONF_D/
            sed -i "s|%pma_alias%|$DB_PMA_ALIAS|g" $OSAL_PATH_NGINX_CONF_D/phpmyadmin.inc
            # Remove apache support        
            hestia_safe_rm $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf
        elif [ "$WEB_SYSTEM" = 'apache2' ] || [ "$WEB_SYSTEM" = 'httpd' ]; then
            # Setup Apache config
            cp -f $HESTIA_INSTALL_DIR/pma/apache.conf /etc/phpmyadmin/
            sed -i "s|%pma_alias%|$DB_PMA_ALIAS|g" /etc/phpmyadmin/apache.conf
            ln -sf /etc/phpmyadmin/apache.conf $OSAL_PATH_APACHE_CONF_D/phpmyadmin.conf
            # Remove Nginx support
            hestia_safe_rm $OSAL_PATH_NGINX_CONF_D/phpmyadmin.inc
        fi
    fi



    # Start web server and proxy
    if [ "$apache_installed" ]; then
        osal_service_enable $OSAL_SERVICE_APACHE
        osal_service_start $OSAL_SERVICE_APACHE
    fi

    if [ "$nginx_installed" ]; then
        osal_service_enable $OSAL_SERVICE_NGINX
        osal_service_start $OSAL_SERVICE_NGINX
    fi
}
