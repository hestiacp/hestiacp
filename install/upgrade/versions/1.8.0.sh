#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.7.6

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

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

if [ "$IMAP_SYSTEM" = "dovecot" ]; then
	if ! grep -qw "^extra_groups = mail$" /etc/dovecot/conf.d/10-master.conf 2> /dev/null; then
		sed -i "s/^service auth {/service auth {\n  extra_groups = mail/g" /etc/dovecot/conf.d/10-master.conf
	fi

	if [ -f /etc/dovecot/conf.d/90-sieve.conf ]; then
		if ! grep -qw "^sieve_vacation_send_from_recipient$" /etc/dovecot/conf.d/90-sieve.conf 2> /dev/null; then
			sed -i "s/^}/  # This setting determines whether vacation messages are sent with the SMTP MAIL FROM envelope address set to the recipient address of the Sieve script owner.\n  sieve_vacation_send_from_recipient = yes\n}/g" /etc/dovecot/conf.d/90-sieve.conf
		fi
	fi
fi
