#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.6.12

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

if [ -f "/etc/fail2ban/jail.local" ]; then
    sed -i "s|/var/log/mysql.log|/var/log/mysql/error.log|g" /etc/fail2ban/jail.local
fi
