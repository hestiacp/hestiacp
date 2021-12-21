#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.5.3

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

# Configure chrony time synchronization service
if [ -f "/etc/chrony/chrony.conf" ]; then
    echo "[ * ] Configuring time synchronization service..."
    sed -i 's/pool 2.debian.pool.ntp.org iburst/#pool 2.debian.pool.ntp.org iburst/g' /etc/chrony/chrony.conf
    echo 'pool pool.ntp.org iburst' > /etc/chrony/sources.d/hestia.sources
    chronyc reload sources > /dev/null 2>&1
fi
