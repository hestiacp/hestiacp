#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.11

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

# Spread out Hestia automatic updates: only touch the job when it is active
# and still on the old fixed default time (41 4), so custom times are kept.
hestiaweb_cron="/var/spool/cron/crontabs/hestiaweb"
if [ -f "$hestiaweb_cron" ] && grep -qE '^41 4 \* \* \* sudo /usr/local/hestia/bin/v-update-sys-hestia-all' "$hestiaweb_cron"; then
	echo "[ * ] Spreading out Hestia automatic update schedule..."
	min=$(generate_password '012345' '2')
	hour=$(generate_password '123456' '1')
	sed -i -E "s|^41 4 (\* \* \* sudo /usr/local/hestia/bin/v-update-sys-hestia-all)|$min $hour \1|" "$hestiaweb_cron"
fi

# Override sury ProtectHome=read-only so Apache can write to /home
if [ "$WEB_SYSTEM" = "apache2" ] && ! grep -qs "^ProtectHome=no" /etc/systemd/system/apache2.service.d/hestia.conf; then
	echo "[ * ] Adding ProtectHome=no override to Apache2 service..."
	mkdir -p /etc/systemd/system/apache2.service.d
	echo -e "[Service]\nProtectHome=no" > /etc/systemd/system/apache2.service.d/hestia.conf
	systemctl daemon-reload > /dev/null 2>&1
	systemctl restart apache2 > /dev/null 2>&1
fi

# Ensure ProFTPD AppArmor local rules allow listing ~/web and capability chown (#6047)
if [[ "$FTP_SYSTEM" =~ proftpd ]] && [[ -e /etc/apparmor.d/proftpd || -L /etc/apparmor.d/proftpd ]]; then
	_proftpd_local="/etc/apparmor.d/local/proftpd"
	if [[ -L /etc/apparmor.d/proftpd || -L /etc/apparmor.d/local || -L "$_proftpd_local" ]]; then
		echo "[ ! ] Warning: Refusing to follow symlink under /etc/apparmor.d for proftpd" >&2
	elif mkdir -p /etc/apparmor.d/local && [[ ! -L /etc/apparmor.d/local ]] && _proftpd_tmp="$(umask 077 && mktemp /etc/apparmor.d/local/.proftpd.XXXXXX)"; then
		_proftpd_ok=true
		if [[ -f "$_proftpd_local" ]]; then
			if ! cat "$_proftpd_local" > "$_proftpd_tmp"; then
				_proftpd_ok=false
			elif [[ -s "$_proftpd_tmp" ]] && [[ -n "$(tail -c 1 "$_proftpd_tmp")" ]]; then
				printf '\n' >> "$_proftpd_tmp" || _proftpd_ok=false
			fi
		fi
		if [[ "$_proftpd_ok" = true ]] && ! grep -qxF "# Configuration added by Hestia" "$_proftpd_tmp"; then
			printf '# Configuration added by Hestia\n' >> "$_proftpd_tmp" || _proftpd_ok=false
		fi
		for _proftpd_rule in \
			"/usr/local/hestia/ssl/certificate.crt r," \
			"/usr/local/hestia/ssl/certificate.key r," \
			"/run/proftpd.sock rw," \
			"@{HOME}/web/ r," \
			"capability chown,"; do
			if [[ "$_proftpd_ok" = true ]] && ! grep -qxF "$_proftpd_rule" "$_proftpd_tmp"; then
				printf '%s\n' "$_proftpd_rule" >> "$_proftpd_tmp" || _proftpd_ok=false
			fi
		done
		if [[ "$_proftpd_ok" = true ]] && chmod 644 "$_proftpd_tmp" && [[ ! -L "$_proftpd_local" ]]; then
			if [[ ! -f "$_proftpd_local" ]] || ! cmp -s "$_proftpd_tmp" "$_proftpd_local"; then
				if mv -f "$_proftpd_tmp" "$_proftpd_local"; then
					echo "[ + ] Updating AppArmor rules for ProFTPd"
					if command -v apparmor_parser > /dev/null 2>&1; then
						apparmor_parser -r /etc/apparmor.d/proftpd > /dev/null 2>&1 || true
					fi
					if command -v systemctl > /dev/null 2>&1; then
						systemctl restart proftpd > /dev/null 2>&1 || true
					fi
				else
					rm -f "$_proftpd_tmp"
					echo "[ ! ] Warning: Failed to update AppArmor rules for ProFTPd" >&2
				fi
			else
				rm -f "$_proftpd_tmp"
			fi
		else
			rm -f "$_proftpd_tmp"
			echo "[ ! ] Warning: Failed to update AppArmor rules for ProFTPd" >&2
		fi
	else
		echo "[ ! ] Warning: Failed to update AppArmor rules for ProFTPd" >&2
	fi
	unset _proftpd_local _proftpd_tmp _proftpd_rule _proftpd_ok
fi
