#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.5

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
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'

# Load the phpMyAdmin tempdir configuration last, leaving lower-numbered
# prefixes available for additional phpMyAdmin configuration files.
pma_old_tempdir_conf="/etc/phpmyadmin/conf.d/02-tempdir.php"
pma_new_tempdir_conf="/etc/phpmyadmin/conf.d/99-tempdir.php"

if [[ -f "$pma_old_tempdir_conf" ]]; then
	mv "$pma_old_tempdir_conf" "$pma_new_tempdir_conf"
fi

# Add the list-all-user-objects API key permission.
if [ -f "$HESTIA/data/api/list-all-user-objects" ]; then
	rm "$HESTIA/data/api/list-all-user-objects"
fi
cp "$HESTIA/install/common/api/list-all-user-objects" "$HESTIA/data/api/list-all-user-objects"
