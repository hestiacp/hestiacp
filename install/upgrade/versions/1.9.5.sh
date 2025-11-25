#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.9.5

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
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

#Fix: avoid spamd execution in Exim when reject_spam is off for current installations
if [ "$MAIL_SYSTEM" = "exim4" ]; then
	echo "[ * ] Fixing spamd execution in Exim when reject_spam is off"
	#shellcheck disable=SC2016
	sed -i -E '/^\s*spam\s*=\s*debian-spamd:true$/{N;/\n\s*condition\s*=\s*\$\{if eq\{\$acl_m3\}\{yes\}\{yes\}\{no\}\}$/{s/(.*)\n(.*)/\2\n\1/}}' /etc/exim4/exim4.conf.template
fi

#Add ESMTP to smtp_banner directive in Exim
if [ "$MAIL_SYSTEM" = "exim4" ]; then
	echo "[ * ] Adding ESMTP to Exim smtp banner"
	#shellcheck disable=SC2016
	sed -i 's/^smtp_banner = $smtp_active_hostname.*/smtp_banner = $smtp_active_hostname ESMTP/' /etc/exim4/exim4.conf.template
fi

# Ensure netplan configs with hestia in the name have restrictive permissions
for netplan_file in /etc/netplan/*hestia*; do
	[ -e "$netplan_file" ] || break
	echo "[ * ] Setting permissions on '$netplan_file' to 600"
	chmod 600 "$netplan_file"
done

# Fix: Hestia can't restart SpamAssassin from the Web UI because it tries to restart
# the 'spamassassin' service, but in Ubuntu 24.04 the service name is 'spamd'
release="$(lsb_release -s -r)"
distid="$(lsb_release -s -i)"
if [[ "$release" = "24.04" ]] && [[ "$distid" = "Ubuntu" ]] && [[ -n "$ANTISPAM_SYSTEM" ]]; then
	"$HESTIA"/bin/v-change-sys-config-value "ANTISPAM_SYSTEM" "spamd"
fi
