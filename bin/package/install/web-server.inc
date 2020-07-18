#!/bin/bash

hestia_package_install_mail-server() {
    memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])

    if [ "$param_help" ]; then
        usage
        exit 0
    fi

    echo "Installing web server package..."

    $BIN/hestia module install apache
    if [ $memory -ge 1572864 ] || [ "$param_withproxy" ]; then
        if [ ! "$param_withoutproxy" ]; then
            $BIN/hestia module install nginx
        fi
    fi

    if [ $memory -ge 1572864 ] || [ "$param_withphp" ]; then
        if [ ! "$param_withoutphp" ]; then
            $BIN/hestia module install php
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