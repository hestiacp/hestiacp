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

# shellcheck source=/etc/hestiacp/hestia.conf
source /etc/hestiacp/hestia.conf

# Selecting remote hosts
IFS=$'\n'
if [ -z $host ]; then
	hosts=$(cat $HESTIA/conf/dns-cluster.conf | grep "SUSPENDED='no'")
else
	hosts=$(grep "HOST='$host'" $HESTIA/conf/dns-cluster.conf)
fi

# Starting cluster loop
for cluster in $hosts; do
	# Reset user, password and hash vars
	clear_dns_cluster_settings

	# Parsing host values
	parse_object_kv_list "$cluster"

	# Wiping remote domains
	cluster_ip = $(dig +short $HOST)
	v-create-cluster-config $HOST $cluster_ip
done
