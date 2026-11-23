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

# SMTP relay configs contain the relay password and were created world-readable
if [[ -f /etc/exim4/smtp_relay.conf ]]; then
	echo "[ + ] Restricting permissions of SMTP relay configuration"
	chmod 640 /etc/exim4/smtp_relay.conf
	chown root:Debian-exim /etc/exim4/smtp_relay.conf
fi
for relay_conf in /etc/exim4/domains/*/smtp_relay.conf; do
	if [[ -f "$relay_conf" ]]; then
		chmod 660 "$relay_conf"
		chown Debian-exim:mail "$relay_conf"
	fi
done

# Add the mail_compress plugin to the global mail_plugins setting in Dovecot 2.4
# so that doveadm can work with compressed emails.
_config_file="/etc/dovecot/conf.d/10-mail.conf"
_backup_file="${_config_file}.hestia_$(date +%Y%m%d_%H%M).bak"
_service_name="dovecot"
_required_version="2.4"
_old_line_regex='^\s*mail_plugins\s*=\s*quota\s*$'
_new_line="mail_plugins = mail_compress quota"
_dovecot_version=$(dovecot --version 2> /dev/null | awk '{print $1}' | cut -d. -f1,2)

if [ "$_dovecot_version" = "$_required_version" ]; then
	echo "[ * ] Adding mail_compress to Dovecot's global mail_plugins"

	if [ ! -f "$_config_file" ]; then
		echo "[ ! ] File not found: $_config_file" >&2
	elif ! grep -Eq "$_old_line_regex" "$_config_file"; then
		echo "[ * ] Line 'mail_plugins = quota' not found in $_config_file. Nothing to do"
	elif ! cp -p "$_config_file" "$_backup_file"; then
		echo "[ ! ] Could not create backup: $_backup_file" >&2
	else
		echo "[ + ] Backup created: $_backup_file"

		# Replace the line
		if sed -i -E "s/$_old_line_regex/$_new_line/" "$_config_file"; then
			echo "[ + ] Line replaced with: $_new_line"

			# Validate the configuration
			if doveconf -n > /dev/null 2>&1; then

				# Restart the service and check that it is running
				if systemctl restart "$_service_name" && sleep 2 && systemctl is-active --quiet "$_service_name"; then
					echo "[ + ] $_service_name is running with the new configuration"
				else
					# Failure: restore the backup and restart with the old configuration
					echo "[ ! ] $_service_name failed to start with the new configuration" >&2
					if cp -p "$_backup_file" "$_config_file"; then
						echo "[ + ] Backup restored: $_config_file"

						# Restart again and check that the original configuration works
						if systemctl restart "$_service_name" && sleep 2 && systemctl is-active --quiet "$_service_name"; then
							echo "[ * ] $_service_name is running again with the original configuration"
						else
							echo "[ ! ] $_service_name still fails to start after restoring the backup" >&2
						fi
					else
						echo "[ ! ] Could not restore the backup, it is still available at: $_backup_file" >&2
					fi
				fi
			else
				# Invalid configuration: restore the backup
				echo "[ ! ] Invalid Dovecot configuration after modification" >&2
				if cp -p "$_backup_file" "$_config_file"; then
					echo "[ + ] Backup restored: $_config_file"
				else
					echo "[ ! ] Could not restore the backup, it is still available at: $_backup_file" >&2
				fi
			fi
		else
			# Replacement failed: restore the backup
			echo "[ ! ] Could not modify $_config_file" >&2
			if cp -p "$_backup_file" "$_config_file"; then
				echo "[ + ] Backup restored: $_config_file"
			else
				echo "[ ! ] Could not restore the backup, it is still available at: $_backup_file" >&2
			fi
		fi
	fi
fi

# HTTP/3 (QUIC) runs over UDP, but the firewall only opened TCP 443. Servers that
# already serve HTTP/3 (for example with the wordpress-http3 templates) had their
# QUIC packets dropped, so browsers showed QUIC errors before falling back to
# HTTP/2. Open UDP 443 when nginx serves HTTP/3 on any domain.
if [ -n "$FIREWALL_SYSTEM" ] && [ "$FIREWALL_SYSTEM" != "no" ] \
	&& nginx -V 2>&1 | grep -q 'http_v3_module' \
	&& grep -lsE '^\s*listen\s+\S+\s+quic\b' "$HOMEDIR"/*/conf/web/*/nginx.ssl.conf* > /dev/null; then
	# shellcheck source=/usr/local/hestia/func/domain.sh
	source $HESTIA/func/domain.sh
	echo "[ * ] Opening UDP port 443 in the firewall for HTTP/3"
	add_web_http3_firewall_rule 443
fi
