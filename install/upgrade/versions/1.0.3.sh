#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.3

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Set default theme
if [ -z $THEME ]; then
    echo "(*) Enabling support for customizable themes and configuring default..."
    $BIN/v-change-sys-theme default
fi

# Replace dhparam 1024 with dhparam 4096
cp -f $HESTIA/install/deb/ssl/dhparam.pem /etc/ssl
