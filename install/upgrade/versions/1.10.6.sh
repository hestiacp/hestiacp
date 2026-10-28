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

# Fix phpmyadmin
pma_config="/etc/phpmyadmin"

if [[ -f "$pma_config/hestia-sso.inc.php" && -f "$pma_config/config.inc.php" ]] \
	&& ! grep -qF "if (isset(\$cfg['Servers'][\$i])) {" "$pma_config/config.inc.php"; then
	echo "[ + ] Fixing PhpMyAdmin SSO config..."

	# Save current blowfish secret
	blowfish_secret=$(sed -nE 's/^[[:space:]]*\$cfg\["blowfish_secret"\][[:space:]]*=[[:space:]]*"([^"]*)".*/\1/p' "$pma_config/config.inc.php" | head -n1)

	if cp -f "$HESTIA/install/deb/phpmyadmin/config.inc.php" "$pma_config/config.inc.php"; then
		if grep -q "//Add Hestia SSO code here" "$pma_config/config.inc.php"; then
			sed -i \
				-e "s|//Add Hestia SSO code here|//Add Hestia SSO code here\n        if (isset(\$cfg['Servers'][\$i])) {\n                include ('$pma_config/hestia-sso.inc.php');|" \
				-e "s|^\(\s*\)\$i++;|\1        \$i++;\n\1}|" \
				"$pma_config/config.inc.php"

			# Restore blowfish secret
			if [[ -n "$blowfish_secret" ]]; then
				blowfish_escaped=$(printf '%s' "$blowfish_secret" | sed -e 's/[\\&|]/\\&/g')
				sed -i "s|%blowfish_secret%|$blowfish_escaped|g" "$pma_config/config.inc.php"
			fi
		else
			echo "[ ! ] Info: SSO marker not found in $pma_config/config.inc.php, skipping patch" >&2
		fi
	else
		echo "[ ! ] Error: failed to copy base config.inc.php to $pma_config" >&2
	fi
fi

# Ensure that Hestia's certificates are owned by root:mail to avoid conflicts
# with the AppArmor profile for ProFTPD
for cert in certificate.crt certificate.key; do
	if [[ -f "$HESTIA/ssl/$cert" ]]; then
		chown root:mail "$HESTIA/ssl/$cert"
		chmod 640 "$HESTIA/ssl/$cert"
	fi
done

# Start fix ProFTPD
ftp_system="$(/usr/local/hestia/bin/v-list-sys-config json | jq -r '.[].FTP_SYSTEM')"
if [[ "$ftp_system" =~ proftpd ]]; then
	# Add an Include directive to load configuration files from /etc/proftpd/conf.d/
	# Hestia currently adds conf.d/external_ip.conf to configure the
	# MasqueradeAddress directive for NAT environments, but the file is never loaded.
	proftpd_conf="/etc/proftpd/proftpd.conf"
	backup_file=""
	restart_proftpd=false

	if [[ -f "$proftpd_conf" ]] && ! grep -qE "^[[:space:]]*Include[[:space:]]+/etc/proftpd/conf.d/" "$proftpd_conf"; then
		echo "[ + ] Fixing ProFTPD not loading conf files from /etc/proftpd/conf.d/"
		# Timestamped name: the backup is always created by this run and never overwrites a previous one
		backup_file="${proftpd_conf}.hestia-backup-$(date +%Y-%m-%d-%H%M%S)"

		if cp -p "$proftpd_conf" "$backup_file"; then
			sed -i -E '/^[[:space:]]*Include[[:space:]]+\/etc\/proftpd\/tls\.conf([[:space:]]|$)/a Include /etc/proftpd/conf.d/' "$proftpd_conf"

			# Check that the directive was actually added
			if grep -qE "^[[:space:]]*Include[[:space:]]+/etc/proftpd/conf.d/" "$proftpd_conf"; then
				restart_proftpd=true
			else
				echo "[ ! ] Include directive not added (tls.conf line not found), skipping" >&2
				# Safe to remove: this backup was created by this run and the file is unchanged
				rm -f "$backup_file"
			fi
		else
			echo "[ ! ] Could not create backup of $proftpd_conf, skipping" >&2
		fi
	fi

	if $restart_proftpd; then
		echo "[ + ] Restarting ProFTPD service"
		if proftpd -t &> /dev/null && systemctl restart proftpd &> /dev/null; then
			echo "[ + ] ProFTPD successfully restarted"
		else
			echo "[ ! ] ProFTPD failed to restart, restoring backup" >&2
			systemctl status proftpd --no-pager -l >&2

			if cp -p "$backup_file" "$proftpd_conf"; then
				if systemctl restart proftpd &> /dev/null; then
					echo "[ + ] Backup restored and ProFTPD restarted"
				else
					echo "[ ! ] ProFTPD still fails after restoring backup" >&2
					systemctl status proftpd --no-pager -l >&2
				fi
			else
				echo "[ ! ] Could not restore $backup_file to $proftpd_conf" >&2
			fi
		fi
	fi
fi
# End fix ProFTPD

# Trim whitespace from IP values in existing web domain configuration files (#5543)
if compgen -G "$HESTIA/data/users/*/web.conf" > /dev/null; then
	echo "[ + ] Trimming whitespace from web domain IP addresses"
	"$HESTIA_PHP" -- "$HESTIA/data/users" << 'EOPHP'
<?php
foreach (glob($argv[1] . '/*/web.conf') as $file) {
	$contents = file_get_contents($file);
	if ($contents === false) {
		continue;
	}

	$trimmed = preg_replace_callback(
		"/\bIP='([^']*)'/",
		static fn(array $match) => "IP='" . trim($match[1]) . "'",
		$contents,
	);
	if ($trimmed !== null && $trimmed !== $contents && file_put_contents($file, $trimmed, LOCK_EX) !== false) {
		echo "      - $file\n";
	}
}
EOPHP
fi
# End trimming web domain IP addresses
