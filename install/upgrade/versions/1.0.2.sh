#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Set default theme

if [ -z $THEME ]; then
    echo "(*) Enabling support for customizable themes and configuring default..."
    $BIN/v-change-sys-theme default
fi