#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.8

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
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'

# Remove sudo and polkit administrator rights from the Hestia user "admin" (#5919).
# Ubuntu's %admin sudoers rule and polkit admin identity would grant them. The helper is
# idempotent, so run it every time: it also repairs a damaged or partial installation.
if [[ -d "$HESTIA/data/users/admin" ]]; then
	# Only report the first successful run: the step runs on every 1.10.7-N update
	admin_deny_existed=no
	[[ -f /etc/sudoers.d/zz-hestia-deny-admin ]] && admin_deny_existed=yes
	echo "[ * ] Ensuring the admin user has no sudo or polkit administrator rights"
	if protect_admin_user; then
		# 40-hestia-admin-identities.rules only existed in unreleased builds
		rm -f /etc/polkit-1/rules.d/40-hestia-admin-identities.rules
		if [[ "$admin_deny_existed" = "no" ]]; then
			add_upgrade_message "Ensured the Hestia user admin has no sudo or polkit administrator rights. On Ubuntu its primary group admin is granted both by the default configuration. Hestia installed /etc/sudoers.d/zz-hestia-deny-admin and, on Ubuntu, /etc/polkit-1/rules.d/49-hestia-admin-identities.rules. The sudoers entry also overrides a leftover /etc/sudoers.d/admin on older servers. If you used the admin account for shell administration, use a separate account in the sudo group instead: adduser myadmin and usermod -aG sudo myadmin. See https://github.com/hestiacp/hestiacp/issues/5919"
		fi
	else
		echo "[ ! ] Failed to remove sudo and polkit administrator rights from the admin user"
		add_upgrade_message "Unable to remove sudo and polkit administrator rights from the admin user (Ubuntu grants them through the admin group). Check /etc/sudoers.d and /etc/polkit-1/rules.d, then run the upgrade again. See https://github.com/hestiacp/hestiacp/issues/5919"
	fi
fi
