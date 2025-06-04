#!/bin/bash

branch=${1-main}

apt -y install curl wget

curl https://raw.githubusercontent.com/DevITcp/DevITcp/$branch/src/hst_autocompile.sh > /tmp/hst_autocompile.sh
chmod +x /tmp/hst_autocompile.sh

mkdir -p /opt/DevITcp

# Building DevIT
if bash /tmp/hst_autocompile.sh --DevIT --noinstall --keepbuild $branch; then
	cp /tmp/DevITcp-src/deb/*.deb /opt/DevITcp/
fi

# Building PHP
if bash /tmp/hst_autocompile.sh --php --noinstall --keepbuild $branch; then
	cp /tmp/DevITcp-src/deb/*.deb /opt/DevITcp/
fi

# Building NGINX
if bash /tmp/hst_autocompile.sh --nginx --noinstall --keepbuild $branch; then
	cp /tmp/DevITcp-src/deb/*.deb /opt/DevITcp/
fi
