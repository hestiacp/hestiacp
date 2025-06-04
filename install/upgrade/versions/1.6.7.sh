#!/bin/bash

# DevIT Control Panel upgrade script for target version 1.6.7

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
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'yes'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

if [ -f "/etc/roundcube/config.inc.php" ]; then
	sed -i "s/\$config\['auto_create_user'] = false;/\$config\['auto_create_user'] = true;/g" /etc/roundcube/config.inc.php
	sed -i "s/\$config\['prefer_html'] = false;/\$config\['prefer_html'] = true;/g" /etc/roundcube/config.inc.php

	#For older installs
	sed -i "s/\$config\['default_host']/\$config\['imap_host']/g" /etc/roundcube/config.inc.php
fi
