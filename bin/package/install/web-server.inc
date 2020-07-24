#!/bin/bash

hestia_package_install_mail-server() {
    memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])

    if [ "$param_help" ]; then
        usage
        return 0
    fi

    echo "Installing web server package..."

    hestia module install apache
    hestia module install awstats
    if [ $memory -ge 1572864 ] || [ "$param_withproxy" ]; then
        if [ ! "$param_withoutproxy" ]; then
            hestia module install nginx
        fi
    fi

    if [ $memory -ge 1572864 ] || [ "$param_withphp" ]; then
        if [ ! "$param_withoutphp" ]; then
            hestia module install php
        fi
    fi
}

usage() {
    echo "Hestia web server package."
    echo "Usage:"
    echo "  hestia package install web-server \\"
    echo "    [--withproxy|--withoutprxy]\\"
    echo "    [--withphp|--withoutphp]"
    echo ""
    echo "Proxy and PHP packages will install on systems"
    echo "with at least 1.5 GB of RAM, unless otherwise specified."
}