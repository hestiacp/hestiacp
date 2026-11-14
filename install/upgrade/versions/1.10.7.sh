#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.7

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

# Migrate a distribution-packaged Roundcube to the Hestia-managed upstream
# installation. Keep this near the top, before the later revert/upgrade steps.
if [[ -d "/usr/share/roundcube" ]] && grep -qw 'roundcube' <<< "${WEBMAIL_SYSTEM:-}"; then
	rc_migration="$HESTIA/install/upgrade/migrate_roundcube.sh"

	if [[ -x "$rc_migration" ]]; then
		if ! HESTIA_BACKUP="${HESTIA_BACKUP:-}" "$rc_migration" --yes --defer-package-removal; then
			echo "[ ! ] Roundcube migration failed; see the log above and restore from the reported backup if needed"
		fi
	else
		echo "[ ! ] Roundcube migration script not found: $rc_migration"
	fi
fi

# Add actionflush to Hestia Fail2Ban action for existing installations (#5022 / #5936)
f2b_action="/etc/fail2ban/action.d/hestia.conf"
hestia_f2b_action="$HESTIA_INSTALL_DIR/fail2ban/action.d/hestia.conf"

if [[ -f "$f2b_action" ]] && [[ -f "$hestia_f2b_action" ]] && ! grep -qE '^[[:space:]]*actionflush[[:space:]]*=' "$f2b_action"; then
	echo "[ + ] Updating Hestia actions for Fail2Ban"

	f2b_was_active=false
	if systemctl is-active --quiet fail2ban; then
		f2b_was_active=true
	fi

	backup_action="$(mktemp)"

	if ! cp -p "$f2b_action" "$backup_action"; then
		echo "[ ! ] Failed to create temporary backup"
		rm -f "$backup_action"
	elif ! cp -f "$hestia_f2b_action" "$f2b_action"; then
		echo "[ ! ] Failed to update Hestia Fail2Ban action"
		rm -f "$backup_action"
	elif ! fail2ban-client -t &> /dev/null; then
		echo "[ ! ] Error validating Fail2Ban configuration, restoring previous action"

		if cp -p "$backup_action" "$f2b_action" && fail2ban-client -t; then
			echo "[ + ] Previous Fail2Ban action restored successfully"
			rm -f "$backup_action"
		else
			echo "[ ! ] CRITICAL: Failed to restore previous Fail2Ban configuration"
			echo "[ ! ] Temporary backup preserved at: $backup_action"
		fi
	elif $f2b_was_active; then
		if systemctl restart fail2ban && systemctl is-active --quiet fail2ban; then
			echo "[ + ] Service Fail2Ban restarted successfully"
			rm -f "$backup_action"
		else
			echo "[ ! ] Error restarting Fail2Ban, restoring previous action"

			if cp -p "$backup_action" "$f2b_action" \
				&& fail2ban-client -t \
				&& systemctl restart fail2ban \
				&& systemctl is-active --quiet fail2ban; then

				echo "[ + ] Previous Fail2Ban action restored successfully"
				rm -f "$backup_action"
			else
				echo "[ ! ] CRITICAL: Failed to restore previous Fail2Ban configuration"
				echo "[ ! ] Temporary backup preserved at: $backup_action"
			fi
		fi
	else
		rm -f "$backup_action"
	fi
fi

# Roundcube 1.7.6 requires symfony/polyfill-php84 for Pdo\Mysql on PHP < 8.4.
# installto.sh does not update a Composer-managed vendor directory, so install
# it before upgrade_roundcube runs (roundcube/roundcubemail#10399).
rc_install_dir="/var/lib/roundcube"
rc_composer_bin="$HOMEDIR/$ROOT_USER/.composer/composer"

if [[ -f "$rc_install_dir/composer.json" ]] \
	&& ! grep -q '"roundcube/roundcubemail"' "$rc_install_dir/composer.json" 2> /dev/null; then
	echo "[ ! ] $rc_install_dir/composer.json does not look like a Roundcube composer.json"
	add_upgrade_message "Roundcube was not upgraded: $rc_install_dir/composer.json is not a Roundcube composer.json."
elif roundcube_polyfill_missing; then
	if [[ ! -x "$rc_composer_bin" ]]; then
		"$BIN/v-add-user-composer" "$ROOT_USER" &> /dev/null
	fi

	if [[ -x "$rc_composer_bin" ]]; then
		rc_composer_home="$(mktemp -d)"
		rc_composer_log="$(mktemp)"

		if COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_HOME="$rc_composer_home" /usr/bin/php -d disable_functions= \
			"$rc_composer_bin" --working-dir="$rc_install_dir" require 'symfony/polyfill-php84:^1.34' \
			--no-interaction --no-progress --update-no-dev > "$rc_composer_log" 2>&1; then
			echo "[ + ] Installed symfony/polyfill-php84 for Roundcube"
		else
			echo "[ ! ] Failed to install symfony/polyfill-php84 for Roundcube"
			sed 's/^/  /' "$rc_composer_log"
			add_upgrade_message "Roundcube was not upgraded: failed to install symfony/polyfill-php84. See the upgrade log."
		fi

		rm -rf "$rc_composer_home" "$rc_composer_log"
	else
		echo "[ ! ] Composer is unavailable, unable to install symfony/polyfill-php84 for Roundcube"
		add_upgrade_message "Roundcube was not upgraded: Composer is unavailable. See the upgrade log."
	fi
fi
