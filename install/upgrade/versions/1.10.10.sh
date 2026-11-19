#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.10

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

# Fix per-domain SMTP relay lookup for authenticated null-sender mail on existing Exim installations
exim_conf="${exim_conf:-/etc/exim4/exim4.conf.template}"
exim_conf_bak="${exim_conf}.before_smtp_relay_null_sender.bak"
pattern_dsearch='SMTP_RELAY_FILE = ${if exists{/etc/exim4/domains/${lookup{$sender_address_domain}dsearch{/etc/exim4/domains}}/smtp_relay.conf}{/etc/exim4/domains/${lookup{$sender_address_domain}dsearch{/etc/exim4/domains}}/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}'
pattern_legacy='SMTP_RELAY_FILE = ${if exists{/etc/exim4/domains/${sender_address_domain}/smtp_relay.conf}{/etc/exim4/domains/$sender_address_domain/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}'
# NOTE: no ":" may appear in the new block (SMTP_RELAY_FILE is used inside colon-separated
# lists such as require_files), hence eq{}{} instead of def:.
new_block='RELAY_SENDER_DOMAIN = ${if eq{$sender_address_domain}{}{${extract{2}{@}{$authenticated_id}}}{$sender_address_domain}}
RELAY_DOMAIN_DIR = /etc/exim4/domains/${lookup{RELAY_SENDER_DOMAIN}dsearch{/etc/exim4/domains}}
SMTP_RELAY_FILE = ${if exists{RELAY_DOMAIN_DIR/smtp_relay.conf}{RELAY_DOMAIN_DIR/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}'

if [[ -f "$exim_conf" ]] \
	&& ! grep -qE '^RELAY_(SENDER_DOMAIN|DOMAIN_DIR)[[:space:]]*=' "$exim_conf" \
	&& { grep -qFx "$pattern_dsearch" "$exim_conf" || grep -qFx "$pattern_legacy" "$exim_conf"; }; then
	echo "[ + ] Fixing per-domain SMTP relay lookup for null-sender mail in Exim"

	cp -p "$exim_conf" "$exim_conf_bak"
	exim_conf_tmp="$(mktemp "${exim_conf}.XXXXXX")"

	# Exact whole-line match in awk (no regex/escaping issues); values go through
	# ENVIRON so backslashes and special characters are kept untouched.
	PAT1="$pattern_dsearch" PAT2="$pattern_legacy" NEW="$new_block" \
		awk '$0 == ENVIRON["PAT1"] || $0 == ENVIRON["PAT2"] { print ENVIRON["NEW"]; next } { print }' \
		"$exim_conf" > "$exim_conf_tmp"
	cat "$exim_conf_tmp" > "$exim_conf" # keep original owner/permissions
	rm -f "$exim_conf_tmp"

	if systemctl restart exim4; then
		echo "[ + ] Exim4 restarted successfully"
	else
		echo "[ ! ] Exim4 failed to restart, rolling back configuration"

		if [[ -f "$exim_conf_bak" ]]; then
			mv "$exim_conf_bak" "$exim_conf"

			if systemctl restart exim4; then
				echo "[ + ] Exim4 restarted successfully after rollback"
			else
				echo "[ ! ] Exim4 failed to restart even after rollback"
			fi
		else
			echo "[ ! ] Backup file not found, unable to rollback"
		fi
	fi
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
