#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.2.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################


# Add Z-Push ActiveSync/AutoDiscover to mail stack
if [ -z "$MAIL_SYSTEM" ] && [ -z "$IMAP_SYSTEM" ]; then
    echo "(*) Configuring Z-Push ActiveSync & AutoDiscover service..."
    apt-get -qq update && apt-get install hestia-zpush
    cp -f $HESTIA/install/deb/zpush/zpush_params /etc/nginx/conf.d/
fi