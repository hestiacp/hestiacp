#!/bin/bash

hestia_package_web-server_install() {
    memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])

    if [ "$param_help" ]; then
        usage
        return 0
    fi

    echo "Installing web server package..."

    hestia module install apache --no-integrate
    hestia module install awstats --no-integrate

    if [ $memory -ge 1572864 ] || [ "$param_withproxy" ]; then
        if [ ! "$param_withoutproxy" ]; then
            hestia module install nginx --no-integrate
        fi
    fi

    if [ $memory -ge 1572864 ] || [ "$param_withphp" ]; then
        if [ ! "$param_withoutphp" ]; then
            hestia module install php --no-integrate
        fi
    fi

    # Integrate everything
    hestia package web-server integrate
}

usage() {
    echo "Hestia web server package."
    echo "Usage:"
    echo "  hestia package install web-server \\"
    echo "    [--withproxy|--withoutproxy]\\"
    echo "    [--withphp|--withoutphp]"
    echo ""
    echo "Proxy and PHP packages will be installed hestia_config_backup 'nginx-remove' $OSAL_PATH_NGINX_CONFon systems"
    echo "with at least 1.5 GB of RAM, unless otherwise specified."
}