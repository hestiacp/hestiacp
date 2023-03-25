#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.7.1

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
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# Moved from 1.6.15.sh
if ! (grep -q 'v-change-user-password' $HESTIA/data/api/billing); then
	sed -i "s|v-make-tmp-file'|v-make-tmp-file,v-change-user-password'|g" $HESTIA/data/api/billing
fi

# Apply update for path change of built-in IPset blacklist.sh
if [ "$FIREWALL_SYSTEM" = "iptables" ]; then
	rm -f $HESTIA/data/firewall/ipset/blacklist.sh

	if ($BIN/v-list-firewall-ipset plain | grep -q '/install/deb/firewall/ipset/blacklist.sh'); then
		echo "[ * ] Update the path of IPset blacklist.sh..."
		sed -i 's|/install/deb/firewall/ipset/blacklist.sh|/install/common/firewall/ipset/blacklist.sh|g' $HESTIA/data/firewall/ipset.conf
	fi
fi
