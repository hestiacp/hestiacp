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

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'
# Set UPGRADE_UPDATE_MAIL_TEMPLATES and UPGRADE_REBUILD_USERS tp true to update mail
# templates and rebuild mail domains to apply the new templates to add support to
# Roundcube 1.7 (same templates work for Roundcube 1.6)
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'

ensure_utf8_locale() {
	local locale_file="/etc/default/locale"

	if locale | grep -qi 'utf-8'; then
		return
	fi

	echo "[ * ] Enabling UTF-8 locale support via C.UTF-8"
	if ! locale-gen C.UTF-8; then
		echo "[ ! ] Failed to generate C.UTF-8 locale. Leaving existing locale untouched."
		return
	fi

	if ! update-locale LANG=C.UTF-8; then
		echo "[ ! ] Failed to update LANG in $locale_file. Leaving existing locale untouched."
		return
	fi

	export LANG=C.UTF-8
}

ensure_utf8_locale

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
		"$BIN/v-change-sys-config-value" "ANTISPAM_SYSTEM" "spamassassin"
	elif [[ $installed_services == *spamd.service* ]]; then
		"$BIN/v-change-sys-config-value" "ANTISPAM_SYSTEM" "spamd"
	fi
fi

# Fix: update quotas and cgroup for existing users
for user in $("$HESTIA"/bin/v-list-users list); do
	if [[ "$RESOURCES_LIMIT" == "yes" ]]; then
		"$HESTIA"/bin/v-update-user-cgroup "$user"
	fi

	if [[ "$DISK_QUOTA" == "yes" ]]; then
		"$HESTIA"/bin/v-update-user-quota "$user"
	fi
done

# Fix: migrate SnappyMail data directory /etc/snappymail/data to /var/lib/snappymail/data
SNAPPYMAIL_ETC_DIR="/etc/snappymail"
SNAPPYMAIL_ETC_DATA="/etc/snappymail/data"
SNAPPYMAIL_VAR_DATA="/var/lib/snappymail/data"
if [[ -d "$SNAPPYMAIL_ETC_DATA" ]] && ! [[ -L "$SNAPPYMAIL_ETC_DATA" ]]; then
	echo "[ * ] Migrating SnappyMail data directory to '$SNAPPYMAIL_VAR_DATA'"
	if [ -L "$SNAPPYMAIL_VAR_DATA" ]; then
		echo "[ * ] Removing existing symlink at '$SNAPPYMAIL_VAR_DATA'"
		rm -f "$SNAPPYMAIL_VAR_DATA"
		echo "[ * ] Moving '$SNAPPYMAIL_ETC_DATA' to '$SNAPPYMAIL_VAR_DATA'"
		if ! mv "$SNAPPYMAIL_ETC_DATA" "$SNAPPYMAIL_VAR_DATA"; then
			echo "[ ! ] Failed to move '$SNAPPYMAIL_ETC_DATA' to '$SNAPPYMAIL_VAR_DATA'. Skipping cleanup."
		else
			echo "[ * ] Removing '$SNAPPYMAIL_ETC_DIR' directory"
			if ! rm -rf "$SNAPPYMAIL_ETC_DIR"; then
				echo "[ ! ] Failed to remove '$SNAPPYMAIL_ETC_DIR'."
			else
				echo "[ * ] SnappyMail data directory migration completed successfully"
			fi
		fi
	else
		echo "[ * ] '$SNAPPYMAIL_VAR_DATA' is not a symlink. Skipping SnappyMail data migration."
	fi
fi
