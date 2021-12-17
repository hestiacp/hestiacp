#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.14

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

if [ -f "/etc/network/interfaces" ] && [ -f "/etc/netplan/60-hestia.yaml" ]; then
    add_upgrade_message "Warning: Please check your network configuration!\n\nDuring this update network compatibility issues were detected. Both /etc/network/interfaces and /etc/netplan/60-hestia.yaml exist which can lead to issues after a system reboot. Please review your network configuration."
    $HESTIA/bin/v-add-user-notification admin "WARNING: Invalid network configuration detected\n\nDuring this update network compatibility issues were detected. Both /etc/network/interfaces and /etc/netplan/60-hestia.yaml exist which can lead to issues after a system reboot. Please review your network configuration."
fi