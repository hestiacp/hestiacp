#!/bin/bash

# Hestia Control Panel upgrade script for target version unreleased

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

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

if [ -f /etc/nginx/nginx.conf ]; then 
    sed -i "s/fastcgi_buffers                 4 256k;/fastcgi_buffers                 8 256k;/g" /etc/nginx/nginx.conf 
fi

# Sync up config files #2819
if [ -f "/etc/roundcube/config.inc.php" ]; then
    sed -i "s/?>//" /etc/roundcube/config.inc.php
    sed -i "s/?>//" /etc/roundcube/mimetypes.php    
fi