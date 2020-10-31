#!/bin/bash

branch=${1-main}

apt -y install curl wget

curl https://raw.githubusercontent.com/hestiacp/hestiacp/main/src/hst_autocompile.sh > /tmp/hst_autocompile.sh
chmod +x /tmp/hst_autocompile.sh

mkdir -p /opt/hestiacp

# Building Hestia
if bash /tmp/hst_autocompile.sh --hestia $branch no; then
    cp /tmp/hestiacp-src/debs/*.deb /opt/hestiacp/
fi

# Building PHP
if bash /tmp/hst_autocompile.sh --php $branch no; then
    cp /tmp/hestiacp-src/debs/*.deb /opt/hestiacp/
fi

# Building NGINX
if bash /tmp/hst_autocompile.sh --nginx $branch no; then
    cp /tmp/hestiacp-src/debs/*.deb /opt/hestiacp/
fi
