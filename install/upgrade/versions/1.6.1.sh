#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.6.0

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

# Fix exim rate limit send issue
if [ "$MAIL_SYSTEM" = "exim4" ]; then 
    acl=$(cat /etc/exim4/exim4.conf.template | grep '${extract{1}{:}{${lookup{$sender_address_local_part@$sender_address_domain}')
    if [ ! -z "$acl" ]; then
        echo "[ * ] Fixed an issue with rate limits and alias mail addresses"
        sed -i 's/${extract{1}{:}{${lookup{$sender_address_local_part@$sender_address_domain}/${extract{1}{:}{${lookup{$authenticated_id}/' /etc/exim4/exim4.conf.template
    fi
fi
