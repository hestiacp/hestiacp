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
		fwrite(STDERR, "      ! Unable to read $file\n");
		continue;
	}

	$trimmed = preg_replace_callback(
		"/\bIP='([^']*)'/",
		static fn(array $match) => "IP='" . trim($match[1]) . "'",
		$contents,
	);
	if ($trimmed === null || $trimmed === $contents) {
		continue;
	}

	// Write to a temp file and rename, so a short write never leaves a
	// partially written web.conf behind.
	$temp = "$file.hestia-tmp";
	if (file_put_contents($temp, $trimmed) !== strlen($trimmed)) {
		@unlink($temp);
		fwrite(STDERR, "      ! Unable to write $temp\n");
		continue;
	}
	if (
		!@chmod($temp, fileperms($file) & 07777) ||
		!@chown($temp, fileowner($file)) ||
		!@chgrp($temp, filegroup($file)) ||
		!rename($temp, $file)
	) {
		@unlink($temp);
		fwrite(STDERR, "      ! Unable to replace $file\n");
		continue;
	}
	echo "      - $file\n";
}
EOPHP
fi
# End trimming web domain IP addresses

# Start fixing Exim HELO checks
fix_exim_helo_checks() {
	local _xhf_conf="${1:-/etc/exim4/exim4.conf.template}"
	local _xhf_old _xhf_new _xhf_pat _xhf_newpat _xhf_rep _xhf_bak

	if [ ! -f "$_xhf_conf" ]; then
		return 1
	fi

	# Already fixed: nothing to apply, so stay silent
	if grep -q 'No valid forward-confirmed reverse DNS for' "$_xhf_conf"; then
		return 0
	fi

	echo "[ + ] Fixing Exim HELO checks"

	# ---------- ORIGINAL TEXT ----------
	# Only the tokens and their order matter; whitespace (spaces, tabs,
	# newlines, blank lines) is matched flexibly.
	_xhf_old=$(
		cat << 'EOF'
acl_check_mail:
  deny    condition     = ${if eq{$sender_helo_name}{}}
          message       = HELO required before MAIL

  drop    !authenticated = *
          message       =  Helo name contains an IP address (HELO was $sender_helo_name) and not is valid
          condition     = ${if match{$sender_helo_name}{\N((\d{1,3}[.-]\d{1,3}[.-]\d{1,3}[.-]\d{1,3})|([0-9a-f]{8})|([0-9A-F]{8}))\N}{yes}{no}}
          condition     = ${if match {${lookup dnsdb{>: defer_never,ptr=$sender_host_address}}\}{$sender_helo_name}{no}{yes}}
          delay         = 45s

  drop    !authenticated = *
          condition     = ${if isip{$sender_helo_name}}
          message       = Access denied - Invalid HELO name (See RFC2821 4.1.3)

  drop    !authenticated = *
          condition    = ${if eq{[$interface_address]}{$sender_helo_name}}
          message       = $interface_address is _my_ address

  accept
EOF
	)

	# ---------- NEW TEXT (written literally) ----------
	_xhf_new=$(
		cat << 'EOF'
acl_check_mail:

  # 1. HELO is mandatory (applies to authenticated clients too)
  deny    condition = ${if eq{$sender_helo_name}{}}
          message   = HELO required before MAIL

  # 2. Reject HELO that is a bare IP address
  drop    !authenticated = *
          condition = ${if isip{$sender_helo_name}}
          message   = HELO is a bare IP address: $sender_helo_name (see RFC 5321 4.1.3)

  # 3. Temporary error if reverse DNS lookup failed
  defer   !authenticated = *
          condition = ${if eq{$host_lookup_deferred}{1}}
          message   = Temporary DNS verification failure

  # 4. Reject our own IP used as HELO
  drop    !authenticated = *
          condition = ${if or{{eq{[$interface_address]}{$sender_helo_name}}{eq{$interface_address}{$sender_helo_name}}}}
          message   = $interface_address is our address

  # 5. Require forward-confirmed reverse DNS (FCrDNS) (LOG-ONLY for now)
  #    To enforce: change "warn" to "deny" and uncomment "message".
  warn    !authenticated = *
          condition   = ${if eq{$sender_host_name}{}}
          log_message = NO_FCRDNS host=$sender_host_address helo=$sender_helo_name
          # message   = No valid forward-confirmed reverse DNS for $sender_host_address

  # 6. Reject IP-like HELOs (e.g. x.1-2-3-4.domain.com, x.1.2.3.4.domain.com,
  #    and IPv4 address literals like [1.2.3.4]) unless they match the verified PTR
  drop    !authenticated = *
          condition = ${if match{$sender_helo_name}{\N\d{1,3}[.-]\d{1,3}[.-]\d{1,3}[.-]\d{1,3}\N}}
          condition = ${if eqi{$sender_helo_name}{$sender_host_name}{no}{yes}}
          message   = HELO contains an IP address and does not match PTR: $sender_helo_name

  # 7. HELO should match the verified PTR (LOG-ONLY for now)
  #    To enforce: change "warn" to "deny" and uncomment "message".
  warn    !authenticated = *
          condition   = ${if def:sender_host_name}
          condition   = ${if eqi{$sender_helo_name}{$sender_host_name}{no}{yes}}
          log_message = HELO_PTR_MISMATCH helo=$sender_helo_name ptr=$sender_host_name host=$sender_host_address
          # message   = HELO does not match verified PTR hostname

  accept
EOF
	)

	# ---------- Build a whitespace-tolerant ERE from a block of text ----------
	_xhf_build_pattern() {
		local _xhf_body
		_xhf_body=$(printf '%s\n' "$1" \
			| tr -s '[:space:]' '\n' \
			| sed 's/[][\\.*^$(){}|+?\/]/\\&/g' \
			| awk 'NF { printf "%s%s", (n++ ? "[[:space:]]+" : ""), $0 }')
		printf '(^|\\n)%s([[:space:]]|$)' "$_xhf_body"
	}

	# ---------- Apply the config and restart Exim ----------
	# Returns 0 only if everything (generate config, validate, restart) works.
	_xhf_apply_and_restart_exim() {
		if ! update-exim4.conf &> /dev/null; then
			echo "[ ! ] Exim configuration update failed"
			return 1
		fi
		if ! exim4 -bV &> /dev/null; then
			echo "[ ! ] Exim configuration validation failed"
			return 1
		fi
		if ! systemctl restart exim4 &> /dev/null; then
			echo "[ ! ] Exim restart failed"
			return 1
		fi
		sleep 2
		if ! systemctl is-active --quiet exim4 &> /dev/null; then
			echo "[ ! ] Exim is not active after restart"
			return 1
		fi
		return 0
	}

	_xhf_pat=$(_xhf_build_pattern "$_xhf_old")
	_xhf_newpat=$(_xhf_build_pattern "$_xhf_new")

	# Replacement: keep the captured prefix (\1) and suffix (\2),
	# escape \, & and / in the new text and turn newlines into \n
	_xhf_rep=$(printf '%s' "$_xhf_new" | sed -z 's/[\\&\/]/\\&/g; s/\n/\\n/g')
	_xhf_rep="\\1${_xhf_rep}\\2"

	# ---------- Check that the original block exists ----------
	if ! sed -zE "/$_xhf_pat/!q1" "$_xhf_conf" > /dev/null 2>&1; then
		echo "[ ! ] Exim original block does NOT match in $_xhf_conf"
		echo "[ ! ] Nothing was modified."
		return 1
	fi

	# ---------- Backup with date and acl_check_mail ----------
	_xhf_bak="${_xhf_conf}.$(date +%Y%m%d-%H%M%S).acl_check_mail.bak"
	if ! cp -p -- "$_xhf_conf" "$_xhf_bak"; then
		echo "[ ! ] Could not create backup. Nothing was modified."
		return 1
	fi
	echo "[ + ] Backup created: $_xhf_bak"

	# ---------- Replacement (first match only) ----------
	if ! sed -zE -i "s/$_xhf_pat/$_xhf_rep/" "$_xhf_conf"; then
		echo "[ ! ] Exim replacement failed, restoring backup"
		cp -p -- "$_xhf_bak" "$_xhf_conf" && echo "[ + ] Exim backup restored"
		return 1
	fi

	# ---------- Verify the new block was applied ----------
	if sed -zE "/$_xhf_newpat/!q1" "$_xhf_conf" > /dev/null 2>&1; then
		echo "[ + ] Exim replacement done successfully"
	else
		echo "[ ! ] Exim verification failed, restoring backup"
		cp -p -- "$_xhf_bak" "$_xhf_conf" && echo "[ + ] Exim backup restored"
		return 1
	fi

	# ---------- Regenerate config and restart Exim ----------
	echo "[ + ] Regenerating configuration and restarting Exim"
	if _xhf_apply_and_restart_exim; then
		echo "[ + ] Exim restarted successfully with the new configuration"
		return 0
	fi

	# ---------- Failure: restore backup and retry ----------
	echo "[ ! ] Could not restart Exim with the new configuration"
	echo "[ + ] Restoring backup"
	if cp -p -- "$_xhf_bak" "$_xhf_conf"; then
		echo "[ + ] Exim backup restored: $_xhf_bak"
	else
		echo "[ ! ] Could not restore backup: $_xhf_bak"
		return 1
	fi

	echo "[ + ] Retrying Exim restart with the original configuration"
	if _xhf_apply_and_restart_exim; then
		echo "[ + ] Exim restarted successfully with the original configuration"
	else
		echo "[ ! ] Exim still fails to restart with the original configuration"
		echo "[ ! ] Check: journalctl -u exim4 / /var/log/exim4/mainlog"
	fi
	return 1
}

fix_exim_helo_checks || echo "[ ! ] Exim HELO checks fix was not applied"
unset -f fix_exim_helo_checks _xhf_build_pattern _xhf_apply_and_restart_exim 2> /dev/null || true

# End fixing Exim HELO checks

# Refresh phpMyAdmin/phpPgAdmin nginx includes so existing installations get
# the named-capture fix (#5863)
if [ -n "$DB_PMA_ALIAS" ] && [ -f "/etc/nginx/conf.d/phpmyadmin.inc" ]; then
	echo "[ + ] Updating phpMyAdmin nginx configuration"
	"$BIN/v-change-sys-db-alias" pma "$DB_PMA_ALIAS"
fi
if [ -n "$DB_PGA_ALIAS" ] && [ -f "/etc/nginx/conf.d/phppgadmin.inc" ]; then
	echo "[ + ] Updating phpPgAdmin nginx configuration"
	"$BIN/v-change-sys-db-alias" pga "$DB_PGA_ALIAS"
fi
