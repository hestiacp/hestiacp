#!/bin/sh

hestia_ver() {
    ver=$(osal_kv_read $HESTIA/conf/hestia.conf 'VERSION' 'unknown')
    echo "Hestia version $ver"
    echo ""
    echo "An open-source Linux web server control panel. https://hestiacp.com/"
    echo -e "\033[0;31m♥\033[0m Made with love & pride by the open-source community from around the world."
}
