#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.4

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################


#Add nginx user_agent separation to desktop/mobile
cp -f $HESTIA_INSTALL_DIR/nginx/agents.conf /etc/nginx/conf.d/

# Reset PMA SSO to fix bug with Nginx + Apache2 
if [ "$PHPMYADMIN_KEY" != "" ]; then
    echo "[ * ] Refressh hestia-sso for PMA..."
    $BIN/v-delete-sys-pma-sso 
    $BIN/v-add-sys-pma-sso 
fi
