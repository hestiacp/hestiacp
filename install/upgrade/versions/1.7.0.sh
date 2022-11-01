#!/bin/bash

# Hestia Control Panel upgrade script for target version unreleased

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### upgrade_config_set_value only accepts true or false.                    #######
#######                                                                         #######
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

# Make sure to sync install quoteshell arg
if [ "$FILE_MANAGER" = "true" ]; then
    echo "[ * ] Force update filemanager..."
    $HESTIA/bin/v-delete-sys-filemanager quiet
    $HESTIA/bin/v-add-sys-filemanager quiet
fi

packages=$(ls --sort=time $HESTIA/data/packages |grep .pkg)
echo "[ * ] Update existing packages to support rate limit mail accounts..."
for package in $packages; do
    if [ -z "$(grep -e 'RATE_LIMIT' $HESTIA/data/packages/$package)" ]; then
       echo "RATE_LIMIT='200'" >> $HESTIA/data/packages/$package
    fi
done
