#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.5.11

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

# Fix Roundcube logdir permission

if [ -d "/var/log/roundcube" ]; then
    chown www-data:www-data /var/log/roundcube
fi
