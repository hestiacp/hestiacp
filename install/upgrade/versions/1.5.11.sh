#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.5.11

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### Pass through information to the end user in case of a issue or problem  #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### in the upgrade notification email. Example:                             #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

PORT=$(cat $HESTIA/nginx/conf/nginx.conf | grep "listen" | sed 's/[^0-9]*//g')

if [ "$PORT" != "8083" ]; then 
    # Update F2B chains config
    if [ -f "$HESTIA/data/firewall/chains.conf" ]; then
        # Update value in chains.conf
        sed -i "s/PORT='8083'/PORT='$PORT'/g" $HESTIA/data/firewall/chains.conf
    fi
    
    # Restart services
    if [ -n "$FIREWALL_SYSTEM" ] && [ "$FIREWALL_SYSTEM" != no ]; then
        $HESTIA/bin/v-stop-firewall
        $HESTIA/bin/v-update-firewall
                
    fi
fi