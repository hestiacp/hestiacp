#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.5.8

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
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

echo "[ * ] Create .gnupg directory"
mkdir -p /root/.gnupg/ && chmod 700 /root/.gnupg/

echo "[ * ] Ensure jail is enabled for sftp or ftp users"
shells="rssh|nologin"
for user in $(grep "$HOMEDIR" /etc/passwd |egrep "$shells" |cut -f 1 -d:); do
    $BIN/v-add-user-sftp-jail "$user" "no"
done