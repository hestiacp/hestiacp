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

# Allow tnftp (used by FTP backups) to read/write local backup archives under AppArmor (#6003)
if [[ -e /etc/apparmor.d/tnftp || -L /etc/apparmor.d/tnftp ]]; then
	_tnftp_local="/etc/apparmor.d/local/tnftp"
	if [[ -L /etc/apparmor.d/tnftp || -L /etc/apparmor.d/local || -L "$_tnftp_local" ]]; then
		echo "[ ! ] Warning: Refusing to follow symlink under /etc/apparmor.d for tnftp" >&2
	elif mkdir -p /etc/apparmor.d/local && [[ ! -L /etc/apparmor.d/local ]] && _tnftp_tmp="$(umask 077 && mktemp /etc/apparmor.d/local/.tnftp.XXXXXX)"; then
		_tnftp_extra=""
		if [[ -f "$_tnftp_local" ]]; then
			_tnftp_extra="$(awk '
				/^# Begin configuration added by Hestia$/ { skip = 1; next }
				/^# End configuration added by Hestia$/ { skip = 0; next }
				!skip { print }
			' "$_tnftp_local")"
		fi
		if {
			if [[ "$_tnftp_extra" == *[^[:space:]]* ]]; then
				printf '%s\n' "$_tnftp_extra"
			fi
			{
				printf '# Begin configuration added by Hestia\n'
				printf 'capability dac_read_search,\n'
				printf 'capability dac_override,\n'
				printf '/backup/ r,\n'
				printf '/backup/** rwk,\n'
				for _tnftp_cand in \
					"${BACKUP:-/backup}" \
					"$(realpath -e /backup 2> /dev/null || true)" \
					"$(realpath -e "${BACKUP:-/backup}" 2> /dev/null || true)"; do
					_bdir="$_tnftp_cand"
					while [[ "$_bdir" == */ && "$_bdir" != "/" ]]; do
						_bdir="${_bdir%/}"
					done
					if [[ -n "$_bdir" && "$_bdir" != "/" && "$_bdir" != "/backup" ]] \
						&& [[ "$_bdir" != *$'\n'* && "$_bdir" != *$'\r'* ]] \
						&& [[ "$_bdir" =~ ^/[A-Za-z0-9._/-]+$ ]] \
						&& [[ "$_bdir" != *//* ]] \
						&& [[ ! "$_bdir" =~ (^|/)\.\.(/|$) ]]; then
						printf '%s/ r,\n' "$_bdir"
						printf '%s/** rwk,\n' "$_bdir"
					fi
				done
				printf '# End configuration added by Hestia\n'
			} | awk '!seen[$0]++'
		} > "$_tnftp_tmp" && chmod 644 "$_tnftp_tmp" && [[ ! -L "$_tnftp_local" ]]; then
			if [[ ! -f "$_tnftp_local" ]] || ! cmp -s "$_tnftp_tmp" "$_tnftp_local"; then
				echo "[ + ] Updating AppArmor rules for tnftp"
				mv -f "$_tnftp_tmp" "$_tnftp_local"
				if command -v apparmor_parser > /dev/null 2>&1; then
					apparmor_parser -r /etc/apparmor.d/tnftp > /dev/null 2>&1 || true
				fi
			else
				rm -f "$_tnftp_tmp"
			fi
		else
			rm -f "$_tnftp_tmp"
			echo "[ ! ] Warning: Failed to update AppArmor rules for tnftp" >&2
		fi
	else
		echo "[ ! ] Warning: Failed to update AppArmor rules for tnftp" >&2
	fi
	unset _tnftp_local _tnftp_tmp _tnftp_extra _tnftp_cand _bdir
fi
