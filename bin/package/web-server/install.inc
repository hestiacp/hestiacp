#!/bin/bash

hestia_package_web-server_install() {
    memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])

    if [ "$param_help" ]; then
        usage
        return 0
    fi

    echo "Installing web server package..."

    hestia module install apache
    hestia module install awstats

    if [ $memory -ge 1572864 ] || [ "$param_with_proxy" ]; then
        if [ ! "$param_without_proxy" ]; then
            hestia module install nginx
        fi
    fi

    if [ $memory -ge 1572864 ] || [ "$param_with_php" ]; then
        if [ ! "$param_without_php" ]; then
            hestia module install php
            hestia module install php-fpm
        fi
    fi

    if [ ! "$param_without_mariadb" ]; then
        hestia module install mariadb
    fi

    # Integrate everything
    osal_enqueue_integrate web-server
}

usage() {
    echo "Hestia web server package."
    echo "Usage:"
    echo "  hestia package install web-server \\"
    echo "    [--with-proxy|--without-proxy]\\"
    echo "    [--with-php|--without-php]"
    echo ""
    echo "Proxy and PHP packages will be installed hestia_config_backup 'nginx-remove' $OSAL_PATH_NGINX_CONFon systems"
    echo "with at least 1.5 GB of RAM, unless otherwise specified."
}