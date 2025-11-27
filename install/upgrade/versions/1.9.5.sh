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
if [[ -n "$ANTISPAM_SYSTEM" ]]; then
	installed_services="$(systemctl list-units --type=service 2>&1)"
	if [[ $installed_services == *spamassassin.service* ]]; then
		write_config_value "ANTISPAM_SYSTEM" "spamassassin"
	elif [[ $installed_services == *spamd.service* ]]; then
		write_config_value "ANTISPAM_SYSTEM" "spamd"
	fi
fi

# Ensure Node.js >=20.x (only if Node.js is already installed)
min_node_major_version=20

if command -v node > /dev/null 2>&1; then
	installed_node_major_version=$(node -v | sed 's/^v//' | cut -d'.' -f1)
	if [ "$installed_node_major_version" -lt "$min_node_major_version" ]; then
		MESSAGE="Detected Node.js version is $installed_node_major_version.x, minimum required version is $min_node_major_version.x. Updating apt sources for nodejs>=20"
		echo "[ * ] $MESSAGE"
		add_upgrade_message "$MESSAGE"

		apt_sources_dir="/etc/apt/sources.list.d"
		mkdir -p "$apt_sources_dir"
		mkdir -p /usr/share/keyrings

		ARCH=$(dpkg --print-architecture)
		if [ -z "$ARCH" ]; then
			case "$(uname -m)" in
				x86_64) ARCH="amd64" ;;
				aarch64 | arm64) ARCH="arm64" ;;
				*) ARCH="amd64" ;;
			esac
		fi

		echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/nodejs.gpg] https://deb.nodesource.com/node_${min_node_major_version}.x nodistro main" > "$apt_sources_dir/nodejs.list"
		curl -s https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor | tee /usr/share/keyrings/nodejs.gpg
	fi
fi
