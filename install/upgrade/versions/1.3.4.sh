#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.3.4

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

echo '[ * ] Updating System Administrator account permissions...'
$HESTIA/bin/v-change-user-role admin admin

# Send end-of-life notification to admin user on servers running Ubuntu 16.04
if [ "$OS_TYPE" = "Ubuntu" ]; then
    if [ "$OS_VERSION" = '16.04' ]; then
        $HESTIA/bin/v-add-user-notification admin 'IMPORTANT: End of support for Ubuntu 16.04 LTS' '<b>Hestia Control Panel no longer supports Ubuntu 16.04 LTS</b>, as a result your server will no longer receive upgrades or security patches after <b>v1.3.4</b>.<br><br>Please upgrade to a supported operating system.'
    fi
fi