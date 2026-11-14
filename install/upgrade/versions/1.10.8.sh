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

# Fix dovecot-iptables jail never banning any IP: the stock fail2ban dovecot
# filter does not match the log format produced by Hestia's Dovecot config
# (new "Login aborted" messages, auth(user,ip,sasl:...) lines from Exim's
# SASL backend and "passwd-file: missing passwd file" errors). Install the
# Hestia-specific filter and point the jail to it.
f2b_jail="/etc/fail2ban/jail.local"
f2b_filter="/etc/fail2ban/filter.d/dovecot-hestia.conf"
hestia_f2b_filter="$HESTIA_INSTALL_DIR/fail2ban/filter.d/dovecot-hestia.conf"

dovecot_jail_section() {
	sed -n '/^\[dovecot-iptables\]/,/^\[/p' "$f2b_jail"
}

if [[ -f "$f2b_jail" ]] && [[ -f "$hestia_f2b_filter" ]] \
	&& dovecot_jail_section | grep -qE '^[[:space:]]*filter[[:space:]]*=[[:space:]]*dovecot[[:space:]]*(#.*)?$'; then

	echo "[ + ] Updating Dovecot filter for Fail2Ban"

	f2b_was_active=false
	if systemctl is-active --quiet fail2ban; then
		f2b_was_active=true
	fi

	backup_jail="$(mktemp)"

	if ! cp -p "$f2b_jail" "$backup_jail"; then
		echo "[ ! ] Failed to create temporary backup"
		rm -f "$backup_jail"
	elif ! cp -f "$hestia_f2b_filter" "$f2b_filter"; then
		echo "[ ! ] Failed to install Dovecot Fail2Ban filter"
		rm -f "$backup_jail"
	else
		sed -i '/^\[dovecot-iptables\]/,/^\[/ s/^\([[:space:]]*filter[[:space:]]*=[[:space:]]*\)dovecot[[:space:]]*\(#.*\)\?$/\1dovecot-hestia/' "$f2b_jail"

		if ! dovecot_jail_section | grep -qE '^[[:space:]]*maxretry'; then
			sed -i '/^\[dovecot-iptables\]/,/^\[/ s/^\([[:space:]]*filter[[:space:]]*=[[:space:]]*dovecot-hestia[[:space:]]*\)$/\1\nmaxretry = 10/' "$f2b_jail"
		fi

		if ! fail2ban-client -t &> /dev/null; then
			echo "[ ! ] Error validating Fail2Ban configuration, restoring previous jail.local"

			if cp -p "$backup_jail" "$f2b_jail" && fail2ban-client -t; then
				echo "[ + ] Previous jail.local restored successfully"
				rm -f "$backup_jail"
			else
				echo "[ ! ] CRITICAL: Failed to restore previous Fail2Ban configuration"
				echo "[ ! ] Temporary backup preserved at: $backup_jail"
			fi
		elif $f2b_was_active; then
			if systemctl restart fail2ban && systemctl is-active --quiet fail2ban; then
				echo "[ + ] Service Fail2Ban restarted successfully"
				rm -f "$backup_jail"
			else
				echo "[ ! ] Error restarting Fail2Ban, restoring previous jail.local"

				if cp -p "$backup_jail" "$f2b_jail" \
					&& fail2ban-client -t \
					&& systemctl restart fail2ban \
					&& systemctl is-active --quiet fail2ban; then

					echo "[ + ] Previous jail.local restored successfully"
					rm -f "$backup_jail"
				else
					echo "[ ! ] CRITICAL: Failed to restore previous Fail2Ban configuration"
					echo "[ ! ] Temporary backup preserved at: $backup_jail"
				fi
			fi
		else
			rm -f "$backup_jail"
		fi
	fi
fi
