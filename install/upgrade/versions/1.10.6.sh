#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.6

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
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'

# Add support for custom DKIM selector on existing Exim installations
exim_conf="/etc/exim4/exim4.conf.template"
exim_conf_bak="${exim_conf}.before_dkim_custom_selector.bak"
pattern='dkim_selector = mail'

if [[ -f "$exim_conf" ]] && grep -qF "$pattern" "$exim_conf"; then
	echo "[ + ] Adding support for custom DKIM selector in Exim"
	sed -i.before_dkim_custom_selector.bak 's#dkim_selector = mail#dkim_selector = ${if exists{/etc/exim4/domains/${lookup{$dkim_domain}dsearch{/etc/exim4/domains/}}/selector}{${lookup{selector}lsearch{/etc/exim4/domains/${lookup{$dkim_domain}dsearch{/etc/exim4/domains/}}/selector}{$value}{mail}}}{mail}}#' "$exim_conf"

	if systemctl restart exim4; then
		echo "[ + ] Exim4 restarted successfully with custom DKIM selector support"
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

# Start migrating PPA
# For Ubuntu: Migrate existing PHP repo from PPA to sury repository
_running_os="$(lsb_release -s -i)"
_running_os="${_running_os,,}"
_arch="$(dpkg --print-architecture)"
_codename="$(lsb_release -s -c)"
_apt_dir="/etc/apt/sources.list.d"
_apt_file="$_apt_dir/php.list"
_ppa_string="ppa.launchpadcontent.net/ondrej/php/ubuntu"
_keyring="/usr/share/keyrings/sury-keyring.gpg"

if [[ "$_running_os" == "ubuntu" ]]; then

	# Find PPA files
	_ppa_files=()
	while IFS= read -r -d '' _file; do
		_ppa_files+=("$_file")
	done < <(grep -rlFZ --include='*.list' --include='*.sources' -- "$_ppa_string" "$_apt_dir")

	# Run only if PPA files exist
	if [[ ${#_ppa_files[@]} -gt 0 ]]; then
		echo "[ + ] Migrating PPA repo to sury"

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

		# Download key to temp file first
		_tmp_key="$(mktemp)"
		if curl -fsSm60 https://packages.sury.org/php/apt.gpg | gpg --dearmor --yes > "$_tmp_key" 2> /dev/null && [[ -s "$_tmp_key" ]]; then
			install -m 644 "$_tmp_key" "$_keyring"
			echo "[ + ] Key installed: $_keyring"
			echo "deb [arch=$_arch signed-by=$_keyring] https://packages.sury.org/php/ $_codename main" > "$_apt_file"
			echo "[ + ] Repo added: $_apt_file"
		else
			echo "[ ! ] Key download failed; repo not added" >&2
		fi
		rm -f "$_tmp_key"
	fi
fi
# End migrating PPA
