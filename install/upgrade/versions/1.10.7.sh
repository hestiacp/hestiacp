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

# Allow the phpMyAdmin SSO API key to also call the new "log in with access to
# all databases" commands used by the generic phpMyAdmin button
if [[ -f "$HESTIA/data/api/phpmyadmin-sso" ]] && ! grep -q 'v-add-database-temp-user-all' "$HESTIA/data/api/phpmyadmin-sso"; then
	echo "[ * ] Updating phpMyAdmin SSO API permissions"
	sed -i "s|COMMANDS='v-add-database-temp-user,v-delete-database-temp-user'|COMMANDS='v-add-database-temp-user,v-delete-database-temp-user,v-add-database-temp-user-all,v-delete-database-temp-user-all'|g" "$HESTIA/data/api/phpmyadmin-sso"
fi

# hestia-sso.php is only ever written once, by v-add-sys-pma-sso at initial
# setup -- a plain package upgrade never touches it again. Servers that
# enabled SSO before this version would otherwise keep running the old
# script forever, silently missing the "__all__" sentinel handling the
# updated generic phpMyAdmin button (list_db.php) now relies on. Re-deploy
# it here, reusing the already-generated keys instead of rotating them.
if [[ -f "/usr/share/phpmyadmin/hestia-sso.php" ]] && ! grep -q '"__all__"' "/usr/share/phpmyadmin/hestia-sso.php"; then
	echo "[ * ] Updating phpMyAdmin SSO script"
	pma_key=$(grep -oP 'define\("PHPMYADMIN_KEY", "\K[^"]+' /usr/share/phpmyadmin/hestia-sso.php)
	api_key=$(grep -oP 'define\("API_KEY", "\K[^"]+' /usr/share/phpmyadmin/hestia-sso.php)
	api_host=$(grep -oP 'define\("API_HOST_NAME", "\K[^"]+' /usr/share/phpmyadmin/hestia-sso.php)
	api_port=$(grep -oP 'define\("API_HESTIA_PORT", "\K[^"]+' /usr/share/phpmyadmin/hestia-sso.php)
	if [[ -n "$pma_key" && -n "$api_key" && -n "$api_host" && -n "$api_port" ]]; then
		cp -f "$HESTIA_INSTALL_DIR/phpmyadmin/hestia-sso.php" /usr/share/phpmyadmin/hestia-sso.php
		chmod 640 /usr/share/phpmyadmin/hestia-sso.php
		chown root:hestiamail /usr/share/phpmyadmin/hestia-sso.php
		sed -i "s/%PHPMYADMIN_KEY%/$pma_key/g" /usr/share/phpmyadmin/hestia-sso.php
		sed -i "s/%API_KEY%/$api_key/g" /usr/share/phpmyadmin/hestia-sso.php
		sed -i "s/%API_HOST_NAME%/$api_host/g" /usr/share/phpmyadmin/hestia-sso.php
		sed -i "s/%API_HESTIA_PORT%/$api_port/g" /usr/share/phpmyadmin/hestia-sso.php
	else
		add_upgrade_message "Could not update the phpMyAdmin SSO script (/usr/share/phpmyadmin/hestia-sso.php) automatically. Please run 'v-delete-sys-pma-sso' followed by 'v-add-sys-pma-sso' to pick up the latest version."
	fi
fi

# Pin phpMyAdmin's configuration storage connection to localhost, where the
# 'phpmyadmin' database and 'pma' control user actually live. Without this,
# a remote-host SSO login (see above) makes phpMyAdmin's own signon plugin
# override $cfg['Servers'][$i]['host'] for the whole session, so config
# storage tries to use that same remote host too and randomly fails with
# "INSERT command denied ... for table `phpmyadmin`.`pma__userconfig`".
if [[ -f "/etc/phpmyadmin/conf.d/01-localhost.php" ]] && ! grep -q "controlhost" "/etc/phpmyadmin/conf.d/01-localhost.php"; then
	echo "[ * ] Pinning phpMyAdmin configuration storage to localhost"
	echo "\$cfg['Servers'][\$i]['controlhost'] = 'localhost';" >> "/etc/phpmyadmin/conf.d/01-localhost.php"
fi
