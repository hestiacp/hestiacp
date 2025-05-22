#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.9.3

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
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'true'

if grep -q "internal-sftp-server" /etc/ssh/sshd_config; then
	sed -i 's/Subsystem sftp internal-sftp-.*/Subsystem sftp internal-sftp/' /etc/ssh/sshd_config
fi

if grep -q "Subsystem sftp /usr/lib/sftp-server-" /etc/ssh/sshd_config; then
	sed -i 's/Subsystem sftp \/usr\/lib\/sftp-server-.*/Subsystem sftp \/usr\/lib\/sftp-server/' /etc/ssh/sshd_config
fi
