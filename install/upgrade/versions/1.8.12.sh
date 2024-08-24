#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.8.11

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
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'true'

# Folder paths
SM_INSTALL_DIR="/var/lib/snappymail"
SM_CONFIG_DIR="/etc/snappymail"
SM_LOG="/var/log/snappymail"

if [ -d "/var/lib/snappymail" ]; then
	chown hestiamail:hestiamail /var/lib/snappymail
	chown hestiamail:hestiamail /etc/snappymail
fi

#Roundube folder paths
RC_INSTALL_DIR="/var/lib/roundcube"
RC_CONFIG_DIR="/etc/roundcube"
RC_LOG="/var/log/roundcube"

if [ -d "$RC_INSTALL_DIR" ]; then
	chown -R hestiamail:www-data "$RC_INSTALL_DIR"
fi
if [ -d "$RC_CONFIG_DIR" ]; then
	chown -R hestiamail:www-data "$RC_CONFIG_DIR"
fi
if [ -f "$RC_CONFIG_DIR/config.inc.php" ]; then
	chmod 640 "$RC_CONFIG_DIR/config.inc.php"
fi
if [ -d "$RC_LOG" ]; then
	chown -R hestiamail:www-data "$RC_LOG"
fi

sed -i "s/disable_functions =.*/disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority/g" /etc/php/*/cli/php.ini

# Ensures proper permissions for Hestia service interactions.
/usr/sbin/adduser hestiamail hestia-users
