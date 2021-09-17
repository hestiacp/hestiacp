#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.14

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

if [ -f "/etc/network/interfaces" ] && [ -f "/etc/netplan/60-hestia.yaml" ]; then
    add_upgrade_message "Warning: Please check your network config!\n\nDuring this update a network compatibility issues has been detected. Both /etc/network/interfaces and /etc/netplan/60-hestia.yaml exists. This can lead to issues after a system reboot. Please review your network configuration."
    $HESTIA/bin/v-add-user-notification admin "Invalid network configuration detected\n\nDuring this update a network compatibility issues has been detected. Both /etc/network/interfaces and /etc/netplan/60-hestia.yaml exists. This can lead to issues after a system reboot. Please review your network configuration."
fi