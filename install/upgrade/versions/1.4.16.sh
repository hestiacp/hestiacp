#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.16

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

if [ -n "$DB_PMA_ALIAS" ]; then
    $HESTIA/bin/v-change-sys-db-alias 'pma' "$DB_PMA_ALIAS"
fi

