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

# Start migrating PPA
# For Ubuntu: Migrate existing Apache2 repo from PPA to sury repository
# and add sury Apache2 repo if Apache2 is the web system and no sury repo is configured
_running_os="$(lsb_release -s -i)"
_running_os="${_running_os,,}"
_arch="$(dpkg --print-architecture)"
_codename="$(lsb_release -s -c)"
_apt_dir="/etc/apt/sources.list.d"
_apt_file="$_apt_dir/apache2.list"
_ppa_regex='ppa\.launchpad(content)?\.net/ondrej/apache2/ubuntu'
_sury_regex='packages\.sury\.org/apache2'
_keyring="/usr/share/keyrings/apache2-keyring.gpg"

# Download sury Apache2 key and add repo
_add_sury_apache2_repo() {
	local _tmp_key
	_tmp_key="$(mktemp)"
	if curl -fsL --connect-timeout 15 --max-time 60 --retry 3 --retry-delay 5 --retry-max-time 30 https://packages.sury.org/apache2/apt.gpg | gpg --dearmor --yes > "$_tmp_key" 2> /dev/null && [[ -s "$_tmp_key" ]]; then
		install -m 644 "$_tmp_key" "$_keyring"
		echo "[ + ] Key installed: $_keyring"
		echo "deb [arch=$_arch signed-by=$_keyring] https://packages.sury.org/apache2/ $_codename main" > "$_apt_file"
		echo "[ + ] Repo added: $_apt_file"
	else
		echo "[ ! ] Key download failed; repo not added" >&2
	fi
	rm -f "$_tmp_key"
}

if [[ "$_running_os" == "ubuntu" ]]; then

	# Find PPA files
	_ppa_files=()
	while IFS= read -r -d '' _file; do
		_ppa_files+=("$_file")
	done < <(grep -rlEZ --include='*.list' --include='*.sources' -- "$_ppa_regex" "$_apt_dir")

	# Run only if PPA files exist
	if [[ ${#_ppa_files[@]} -gt 0 ]]; then
		echo "[ + ] Migrating Apache2 PPA repo to sury"

		# Backup and remove PPA files (.save is ignored by apt)
		_ts="$(date +%Y%m%d_%H%M%S)"
		for _file in "${_ppa_files[@]}"; do
			_backup="${_file}.hestia-${_ts}.save"
			if cp -p -- "$_file" "$_backup" && rm -f -- "$_file"; then
				echo "[ + ] PPA backed up: $_backup"
			else
				echo "[ ! ] PPA backup failed: $_file" >&2
			fi
		done

		_add_sury_apache2_repo
	fi

	# Add sury repo if Apache2 is the web system and no sury Apache2 repo is configured
	_web_system="$("$BIN"/v-list-sys-config json | jq -r '.[]|.WEB_SYSTEM')"
	if [[ "$_web_system" == "apache2" && ! -e "$_apt_file" ]] \
		&& ! grep -rqE --include='*.list' --include='*.sources' -- "$_sury_regex" "$_apt_dir"; then
		echo "[ + ] Adding sury Apache2 repo"
		_add_sury_apache2_repo
	fi
fi

# The Launchpad PPA origin (ppa:ondrej/php) is no longer listed because the
# PPA has been deprecated.
_uu_conf="/etc/apt/apt.conf.d/60unattended-upgrades-hestia-php"
echo "[ + ] Update unattended-upgrades conf to install PHP updates"
cat > "$_uu_conf" << 'EOF'
Unattended-Upgrade::Origins-Pattern {
        "origin=deb.sury.org";
};
EOF

# Remove the unattended-upgrades conf that allowed Apache updates from the
# Sury PPA. That PPA has been deprecated, so the file is obsolete. Apache
# updates are still applied automatically: the PHP conf above allows the
# "deb.sury.org" origin, which also covers Sury's apache2 repository.
_uu_apache2_conf="/etc/apt/apt.conf.d/60unattended-upgrades-hestia-apache2"
if [ -f "$_uu_apache2_conf" ]; then
	echo "[ + ] Removing obsolete unattended-upgrades conf to install Apache updates"
	rm -f "$_uu_apache2_conf"
fi
# End migrating Apache2 PPA

# Catch-up migration for Ubuntu hosts whose legacy PHP PPA still uses
# ppa.launchpad.net (1.10.6.sh only matched ppa.launchpadcontent.net) or
# whose ondrej/php PPA survived in .list / .sources files.
_php_running_os="$(lsb_release -s -i 2> /dev/null)"
if [ "${_php_running_os,,}" = "ubuntu" ]; then
	_php_apt_dir="/etc/apt/sources.list.d"
	_php_ppa_regex='^[^#]*ppa\.launchpad(content)?\.net/ondrej/php/ubuntu'
	_php_sury_regex='^[^#]*packages\.sury\.org/php(/|[[:space:]]|$)'
	_php_keyring="/usr/share/keyrings/sury-keyring.gpg"
	_php_list="$_php_apt_dir/php.list"

	_php_ppa_files=()
	if [ -d "$_php_apt_dir" ]; then
		while IFS= read -r -d '' _php_file; do
			_php_ppa_files+=("$_php_file")
		done < <(grep -rlEZ --include='*.list' --include='*.sources' "$_php_ppa_regex" "$_php_apt_dir" 2> /dev/null)
	fi

	if [ "${#_php_ppa_files[@]}" -gt 0 ]; then
		_php_has_other_sury=0
		while IFS= read -r -d '' _php_sury_file; do
			_php_in_ppa=0
			for _php_file in "${_php_ppa_files[@]}"; do
				if [ "$_php_sury_file" = "$_php_file" ]; then
					_php_in_ppa=1
					break
				fi
			done
			if [ "$_php_in_ppa" -eq 0 ]; then
				_php_has_other_sury=1
				break
			fi
		done < <(grep -rlEZ --include='*.list' --include='*.sources' "$_php_sury_regex" "$_php_apt_dir" 2> /dev/null)

		_php_arch=""
		_php_codename=""
		_php_ready=1
		if [ "$_php_has_other_sury" -eq 0 ]; then
			_php_arch="$(dpkg --print-architecture 2> /dev/null)"
			_php_codename="$(lsb_release -s -c 2> /dev/null)"
			if [ -z "$_php_arch" ] || [ -z "$_php_codename" ]; then
				echo "[ ! ] Unable to determine architecture or Ubuntu codename; keeping existing PHP PPA sources"
				_php_ready=0
			fi
		fi

		_php_need_key=1
		if [ "$_php_has_other_sury" -eq 1 ] && [ -s "$_php_keyring" ]; then
			_php_need_key=0
		fi

		_php_tmp_raw=""
		_php_tmp_key=""
		if [ "$_php_ready" -eq 1 ] && [ "$_php_need_key" -eq 1 ]; then
			_php_tmp_raw="$(mktemp)"
			_php_tmp_key="$(mktemp)"
			if [ -z "$_php_tmp_raw" ] || [ -z "$_php_tmp_key" ] || ! curl -fsL --connect-timeout 15 --max-time 60 --retry 3 --retry-delay 5 --retry-max-time 30 -o "$_php_tmp_raw" https://packages.sury.org/php/apt.gpg || [ ! -s "$_php_tmp_raw" ] || ! gpg --batch --yes --dearmor -o "$_php_tmp_key" "$_php_tmp_raw" || [ ! -s "$_php_tmp_key" ]; then
				echo "[ ! ] Failed to download Sury PHP archive keyring; keeping existing PHP PPA sources"
				rm -f "$_php_tmp_raw" "$_php_tmp_key"
				_php_ready=0
			elif ! mkdir -p "$(dirname "$_php_keyring")" || ! install -m 644 "$_php_tmp_key" "$_php_keyring"; then
				echo "[ ! ] Failed to install Sury PHP archive keyring; keeping existing PHP PPA sources"
				rm -f "$_php_tmp_raw" "$_php_tmp_key"
				_php_ready=0
			else
				rm -f "$_php_tmp_raw" "$_php_tmp_key"
			fi
		fi

		if [ "$_php_ready" -eq 1 ]; then
			_php_backup_dir="${HESTIA_BACKUP:-/root/hst_backups}/conf/apt-php-ppa"
			for _php_file in "${_php_ppa_files[@]}"; do
				echo "[ + ] Migrating legacy Ubuntu PHP PPA source: $_php_file"
				_php_rel="${_php_file#"$_php_apt_dir"/}"
				mkdir -p "$_php_backup_dir/$(dirname "$_php_rel")"
				mv -f "$_php_file" "$_php_backup_dir/$_php_rel"
			done

			if ! grep -rlEq --include='*.list' --include='*.sources' "$_php_sury_regex" "$_php_apt_dir" 2> /dev/null; then
				echo "deb [arch=$_php_arch signed-by=$_php_keyring] https://packages.sury.org/php/ $_php_codename main" > "$_php_list"
				chmod 644 "$_php_list"
			fi
		fi
	fi
fi
unset _php_running_os _php_apt_dir _php_ppa_regex _php_sury_regex _php_keyring _php_list \
	_php_ppa_files _php_file _php_has_other_sury _php_sury_file _php_in_ppa \
	_php_arch _php_codename _php_ready _php_need_key _php_tmp_raw _php_tmp_key _php_backup_dir _php_rel
# End migrating legacy PHP PPA
