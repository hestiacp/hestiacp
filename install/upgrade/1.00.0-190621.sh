#!/bin/bash

# Define global variables
if [ -z "$HESTIA" ] || [ ! -f "${HESTIA}/conf/hestia.conf" ]; then
    export HESTIA="/usr/local/hestia"
    export BIN="/usr/local/hestia/bin"
fi

# Set backup folder
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"

# Set installation source folder
hestiacp="$HESTIA/install/deb"

# Load hestia.conf
source /usr/local/hestia/conf/hestia.conf

# Get hestia version
version=$(dpkg -l | awk '$2=="hestia" { print $3 }')

# Compare version for upgrade routine
if [ "$version" != "1.00.0-190618" ] && [ "$version" != "0.10.0" ] then
    source $HESTIA/install/upgrade/1.00.0-190618.sh
fi

# Place additional commands below.