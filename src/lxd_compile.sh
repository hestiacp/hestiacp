#!/bin/bash

branch=${1-main}

apt -y install curl wget

curl https://raw.githubusercontent.com/hestiacp/hestiacp/$branch/src/dst_autocompile.sh > /tmp/dst_autocompile.sh
chmod +x /tmp/dst_autocompile.sh

mkdir -p /opt/hestiacp

# Building Hestia
if bash /tmp/dst_autocompile.sh --devcp --noinstall --keepbuild $branch; then
	cp /tmp/hestiacp-src/deb/*.deb /opt/hestiacp/
fi

# Building PHP
if bash /tmp/dst_autocompile.sh --php --noinstall --keepbuild $branch; then
	cp /tmp/hestiacp-src/deb/*.deb /opt/hestiacp/
fi

# Building NGINX
if bash /tmp/dst_autocompile.sh --nginx --noinstall --keepbuild $branch; then
	cp /tmp/hestiacp-src/deb/*.deb /opt/hestiacp/
fi
