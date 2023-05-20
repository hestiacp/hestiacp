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
	if grep -qw "^extra_groups = mail$" /etc/dovecot/conf.d/10-master.conf 2> /dev/null; then
		sed -i "s/^service auth {/service auth {\n  extra_groups = mail\n/g" /etc/dovecot/conf.d/10-master.conf
	fi
fi

if [ "$MAIL_SYSTEM" = "exim4" ]; then
	echo "[ * ] Disable SMTPUTF8 for Exim for now"
	if grep -qw "^smtputf8_advertise_hosts =" /etc/exim4/exim4.conf.template 2> /dev/null; then
		sed -i "/^domainlist local_domains = dsearch;\/etc\/exim4\/domains\/i smtputf8_advertise_hosts =" /etc/exim4/exim4.conf.template
	fi
fi
