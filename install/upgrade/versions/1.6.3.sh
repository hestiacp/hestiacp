#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.6.3

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
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'yes'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'yes'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

if [ -f /etc/exim4/exim4.conf.template ]; then 
    host=$(cat /etc/exim4/exim4.conf.template | grep hosts_try_fastopen);
    if [ -z "$host" ]; then
        echo "[ * ] Fix an issue with sending large attachments to Google / Gmail"
        sed -i '/dkim_strict = .*/a hosts_try_fastopen = !*.l.google.com' /etc/exim4/exim4.conf.template
    fi
fi