#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.8.1

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

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

if [ "$MAIL_SYSTEM" = "exim4" ]; then
	exim_version=$(exim4 --version | head -1 | awk '{print $3}' | cut -f -2 -d .)
	# if Exim version > 4.95 or greater!
	if version_ge "$exim_version" "4.95"; then
		sed -i "s/SRS_SECRET = readfile{\/etc\/exim4\/srs.conf}/SRS_SECRET = \${readfile{\/etc\/exim4\/srs.conf}}/g" /etc/exim4/exim4.conf.template
		chown root:Debian-exim /etc/exim4/srs.conf
		chown 644 /etc/exim4/srs.conf
	fi
fi
