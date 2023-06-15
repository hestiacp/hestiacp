#!/bin/bash

# Hestia Control Panel upgrade script for target version [ To be released ]

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

packages=$(ls --sort=time $HESTIA/data/packages | grep .pkg)
echo "[ * ] Update existing packages to support add support for incremental backups"
for package in $packages; do
	if [ -z "$(grep -e 'BACKUPS_INCREMENTAL' $HESTIA/data/packages/$package)" ]; then
		echo "BACKUPS_INCREMENTAL='no'" >> $HESTIA/data/packages/$package
	fi
done
