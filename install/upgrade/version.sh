#!/bin/bash

if [ $VERSION = "$version" ]; then
    echo "(!) The latest version of Hestia Control Panel ($version) is already installed."
    echo "    Verifying configuration..."
    echo ""
    source /usr/local/hestia/install/upgrade/versions/$version.sh
    VERSION="$version"
fi
if [ $VERSION = "0.9.8-27" ]; then
    source /usr/local/hestia/install/upgrade/versions/0.9.8-28.sh
    VERSION="0.9.8-28"
fi
if [ $VERSION = "0.9.8-28" ]; then
    source /usr/local/hestia/install/upgrade/versions/1.00.0-190618.sh
    VERSION="1.00.0-190618"
fi
if [ $VERSION = "0.10.00" ] || [ $VERSION = "1.00.0-190618" ]; then
    source /usr/local/hestia/install/upgrade/versions/$version.sh
    VERSION="$version"
fi