#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.16

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### Pass trough information to the end user incase of a issue or problem    #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### to the upgrade email. Please add it using:                              #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

if [ -n "$DB_PMA_ALIAS" ]; then
    $HESTIA/bin/v-change-sys-db-alias 'pma' "$DB_PMA_ALIAS"
fi

