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
