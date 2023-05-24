#!/bin/bash

branch=${1-main}

if [ -f "/etc/redhat-release" ]; then
	dnf -y install curl wget
else
	apt -y install curl wget
fi

curl https://raw.githubusercontent.com/hestiacp/hestiacp/$branch/src/hst_autocompile.sh > /tmp/hst_autocompile.sh
chmod +x /tmp/hst_autocompile.sh

mkdir -p /opt/hestiacp

# Building Hestia
if bash /tmp/hst_autocompile.sh --hestia --noinstall --keepbuild $branch; then
	if [ -f "/etc/redhat-release" ]; then
		cp /tmp/hestiacp-src/rpm/*.rpm /opt/hestiacp/
	else
		cp /tmp/hestiacp-src/deb/*.deb /opt/hestiacp/
	fi
fi

# Building PHP
if bash /tmp/hst_autocompile.sh --php --noinstall --keepbuild $branch; then
	if [ -f "/etc/redhat-release" ]; then
		cp /tmp/hestiacp-src/rpm/*.rpm /opt/hestiacp/
	else
		cp /tmp/hestiacp-src/deb/*.deb /opt/hestiacp/
	fi
fi

# Building NGINX
if bash /tmp/hst_autocompile.sh --nginx --noinstall --keepbuild $branch; then
	if [ -f "/etc/redhat-release" ]; then
		cp /tmp/hestiacp-src/rpm/*.rpm /opt/hestiacp/
	else
		cp /tmp/hestiacp-src/deb/*.deb /opt/hestiacp/
	fi
fi
