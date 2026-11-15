#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.8

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

# Update hestia-sso.php for phpMyAdmin so get_user_ip() matches the IP the SSO
# token was created with (#5068). Only the script itself is replaced and the
# values of the deployed copy are kept: v-delete-sys-pma-sso + v-add-sys-pma-sso
# would also rewrite hestia-sso.inc.php and silently drop v-add-sys-pma-restrict.
pma_sso_file="/usr/share/phpmyadmin/hestia-sso.php"
pma_sso_template="$HESTIA_INSTALL_DIR/phpmyadmin/hestia-sso.php"
if [[ -n "$PHPMYADMIN_KEY" ]] && [[ -f "$pma_sso_file" ]] && [[ -f "$pma_sso_template" ]] \
	&& ! grep -q 'get_real_user_ip' "$pma_sso_file"; then
	echo "[ * ] Updating hestia-sso for phpMyAdmin..."

	pma_sso_ok="yes"
	pma_sso_sed=()
	for pma_sso_var in PHPMYADMIN_KEY API_HOST_NAME API_HESTIA_PORT API_KEY; do
		pma_sso_value="$(grep -Po "define\([\"']${pma_sso_var}[\"'],\s*[\"']\K[^\"']*" "$pma_sso_file" | head -n1)"
		if [[ -z "$pma_sso_value" ]] || [[ "$pma_sso_value" =~ [|\&\\%] ]]; then
			pma_sso_ok="no"
			break
		fi
		pma_sso_sed+=(-e "s|%${pma_sso_var}%|${pma_sso_value}|g")
	done

	pma_sso_tmp="$(mktemp)"
	if [[ "$pma_sso_ok" == "yes" ]] \
		&& sed "${pma_sso_sed[@]}" "$pma_sso_template" > "$pma_sso_tmp" \
		&& ! grep -q '%[A-Z_]*%' "$pma_sso_tmp" \
		&& install -m 640 -o root -g hestiamail "$pma_sso_tmp" "$pma_sso_file"; then
		echo "[ + ] phpMyAdmin SSO updated"
	else
		echo "[ ! ] Failed to update $pma_sso_file"
		add_upgrade_message "Updating phpMyAdmin SSO failed: $pma_sso_file still uses the old IP detection, so SSO logins behind a reverse proxy or Cloudflare may fail with a token mismatch. Reinstall it with v-delete-sys-pma-sso and v-add-sys-pma-sso. If phpMyAdmin access was restricted, run v-delete-sys-pma-restrict and v-add-sys-pma-restrict afterwards."
	fi
	rm -f "$pma_sso_tmp"
fi
