#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.5

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

if [ -f /etc/os-release ]; then
	# /etc/os-release defines its own $VERSION, which would otherwise
	# clobber Hestia's $VERSION since this script is sourced by the caller
	_HESTIA_VERSION="$VERSION"
	source /etc/os-release
	VERSION="$_HESTIA_VERSION"
	unset _HESTIA_VERSION
fi

# Set running OS
IS_DEBIAN13=false
IS_UBUNTU2604=false
IS_DEBIAN13_OR_UBUNTU2604=false

if [[ "$ID" == "debian" && "$VERSION_ID" == "13" ]]; then
	IS_DEBIAN13=true
fi

if [[ "$ID" == "ubuntu" && "$VERSION_ID" == "26.04" ]]; then
	IS_UBUNTU2604=true
fi

if $IS_DEBIAN13 || $IS_UBUNTU2604; then
	IS_DEBIAN13_OR_UBUNTU2604=true
fi

# Start fix ProFTPD
# In Debiand 13 and Ubuntu 26.04 we need to:
#   Add modules.conf to proftpd.conf so tls mod is loaded.
#   Ensure the xfer module is loaded so the UseSendfile directive doesn't produce an error.
restart_proftpd=false
if $IS_DEBIAN13_OR_UBUNTU2604; then
	echo "[ + ] Checking whether ProFTPd needs to be fixed..."

	if [[ -f /etc/proftpd/modules.conf.proftpd-new ]] && [[ -f /etc/proftpd/modules.conf ]] && ! grep -qE "^[[:space:]]*LoadModule[[:space:]]+mod_xfer\.c" /etc/proftpd/modules.conf; then
		echo "[ + ] Fixing ProFTPd not loading module xfer"
		if grep -qE "^[[:space:]]*LoadModule[[:space:]]+mod_xfer\.c" /etc/proftpd/modules.conf.proftpd-new; then
			cp /etc/proftpd/modules.conf "/etc/proftpd/modules.conf.hestia-backup-$(date +%Y-%m-%d)"
			cp /etc/proftpd/modules.conf.proftpd-new /etc/proftpd/modules.conf
			restart_proftpd=true
		else
			echo "[ ! ] Error: mod_xfer.c is not enabled in either modules.conf or modules.conf.proftpd-new"
		fi
	fi

	if [[ -f /etc/proftpd/modules.conf ]] && grep -qF "Include /etc/proftpd/tls.conf" /etc/proftpd/proftpd.conf; then
		if ! grep -qF "Include /etc/proftpd/modules.conf" /etc/proftpd/proftpd.conf; then
			echo "[ + ] Fixing ProFTPd not loading modules.conf"
			sed -i '\|Include /etc/proftpd/tls.conf|i Include /etc/proftpd/modules.conf' /etc/proftpd/proftpd.conf
			restart_proftpd=true
		fi
	fi
fi

# In Ubuntu 26.04 we also need to:
#   Add rules to AppArmor to allow ProFTPD to read the certificates and create the socket.
if $IS_UBUNTU2604; then
	# Add ruleset for AppArmor
	mkdir -p /etc/apparmor.d/local
	if [[ ! -f /etc/apparmor.d/local/proftpd ]]; then
		echo "[ + ] Fixing ProFTPd rules for AppArmor"
		cat > /etc/apparmor.d/local/proftpd << 'EOF'
# Configuration added by Hestia
/usr/local/hestia/ssl/certificate.crt r,
/usr/local/hestia/ssl/certificate.key r,
/run/proftpd.sock rw,
EOF
		restart_proftpd=true
	elif ! grep -qF "# Configuration added by Hestia" /etc/apparmor.d/local/proftpd; then
		echo "[ + ] Fixing ProFTPd rules for AppArmor"
		cat >> /etc/apparmor.d/local/proftpd << 'EOF'
# Configuration added by Hestia
/usr/local/hestia/ssl/certificate.crt r,
/usr/local/hestia/ssl/certificate.key r,
/run/proftpd.sock rw,
EOF
		restart_proftpd=true
	fi
fi

if $restart_proftpd; then
	echo "[ + ] Restarting ProFTPd service"
	if systemctl restart proftpd &> /dev/null; then
		echo "[ + ] ProFTPd successfully restarted"
	else
		echo "[ ! ] Error restarting ProFTPd" >&2
		systemctl status proftpd --no-pager -l >&2
	fi
else
	echo "[ + ] ProFTPd doens't need to be fixed"
fi

# End fix ProFTPD

# Load the phpMyAdmin tempdir configuration last, leaving lower-numbered
# prefixes available for additional phpMyAdmin configuration files.
pma_old_tempdir_conf="/etc/phpmyadmin/conf.d/02-tempdir.php"
pma_new_tempdir_conf="/etc/phpmyadmin/conf.d/99-tempdir.php"

if [[ -f "$pma_old_tempdir_conf" ]]; then
	mv "$pma_old_tempdir_conf" "$pma_new_tempdir_conf"
fi
