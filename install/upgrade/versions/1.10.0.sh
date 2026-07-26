#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.0

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

# fix/file manager ignores user language
echo "[ * ] Fix File Manager ignoring user language"
cp -f "$HESTIA"/install/deb/filemanager/filegator/configuration.php "$HESTIA"/web/fm/configuration.php

if [ -f /etc/os-release ]; then
	source /etc/os-release
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

# Apply SSH config if running on Debian 13 or Ubuntu 26.04
if $IS_DEBIAN13_OR_UBUNTU2604; then
	_KEX_CONF="/etc/ssh/sshd_config.d/hestia-kex.conf"
	_KEX_LINE="KexAlgorithms +diffie-hellman-group-exchange-sha256"

	# Only create/modify the file if it doesn't already contain the correct config
	if [[ ! -f "$_KEX_CONF" ]] || ! grep -qxF "$_KEX_LINE" "$_KEX_CONF"; then
		echo "[ * ] Creating $_KEX_CONF"
		echo "$_KEX_LINE" > "$_KEX_CONF"
		"$BIN"/v-restart-service ssh
	fi
fi

# Fix: migrate SnappyMail data directory /etc/snappymail/data to /var/lib/snappymail/data
SNAPPYMAIL_ETC_DIR="/etc/snappymail"
SNAPPYMAIL_ETC_DATA="/etc/snappymail/data"
SNAPPYMAIL_VAR_DATA="/var/lib/snappymail/data"
if [[ -d "$SNAPPYMAIL_ETC_DATA" ]] && ! [[ -L "$SNAPPYMAIL_ETC_DATA" ]]; then
	echo "[ * ] Migrating SnappyMail data directory to '$SNAPPYMAIL_VAR_DATA'"
	if [ -L "$SNAPPYMAIL_VAR_DATA" ]; then
		echo "[ * ] Removing existing symlink at '$SNAPPYMAIL_VAR_DATA'"
		rm -f "$SNAPPYMAIL_VAR_DATA"
		echo "[ * ] Moving '$SNAPPYMAIL_ETC_DATA' to '$SNAPPYMAIL_VAR_DATA'"
		if ! mv "$SNAPPYMAIL_ETC_DATA" "$SNAPPYMAIL_VAR_DATA"; then
			echo "[ ! ] Failed to move '$SNAPPYMAIL_ETC_DATA' to '$SNAPPYMAIL_VAR_DATA'. Skipping cleanup."
		else
			echo "[ * ] Removing '$SNAPPYMAIL_ETC_DIR' directory"
			if ! rm -rf "$SNAPPYMAIL_ETC_DIR"; then
				echo "[ ! ] Failed to remove '$SNAPPYMAIL_ETC_DIR'."
			else
				echo "[ * ] SnappyMail data directory migration completed successfully"
			fi
		fi
	else
		echo "[ * ] '$SNAPPYMAIL_VAR_DATA' is not a symlink. Skipping SnappyMail data migration."
	fi
fi

# If Dovecot is version 2.4 and OS is Trixie (13) or Ubuntu 26.04, replace Dovecot's configuration and rebuild users
if command -v dovecot &> /dev/null; then
	dovecot_version="$(dovecot --version | cut -f -2 -d .)"
else
	dovecot_version=false
fi

if $IS_DEBIAN13_OR_UBUNTU2604 && [[ "$dovecot_version" = "2.4" ]]; then
	if ! grep -q 'modified by Hestia' /etc/dovecot/dovecot.conf \
		|| ! grep -q 'ssl_server_cert_file = /usr/local/hestia' /etc/dovecot/conf.d/10-ssl.conf; then
		echo "[ * ] Updating Dovecot $dovecot_version configuration"
		cp -f "$HESTIA_COMMON_DIR"/dovecot/2.4/dovecot.conf /etc/dovecot/
		cp -f "$HESTIA_COMMON_DIR"/dovecot/2.4/conf.d/* /etc/dovecot/conf.d/

		# rebuild users to apply new dovecot conf
		upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'

		# if sieve is installed, replace dovecot conf files
		HAS_DOVECOT_SIEVE_INSTALLED=$(dpkg --get-selections dovecot-managesieved 2> /dev/null | grep -c dovecot-managesieved)
		if [[ "$HAS_DOVECOT_SIEVE_INSTALLED" = "1" ]]; then
			echo "[ * ] Updating Sieve $dovecot_version configuration"
			# dovecot.conf install
			sed -i -E 's/protocols = imap/protocols = sieve imap/' /etc/dovecot/dovecot.conf
			#  10-master.conf
			sed -i -E -z 's/    user = dovecot\n  \}\n\}/    user = dovecot\n  \}\n\n  unix_listener auth-master {\n    group = mail\n    mode = 0660\n    user = dovecot\n  }\n\}/' /etc/dovecot/conf.d/10-master.conf
			#  15-lda.conf
			sed -i '/^protocol lda {$/a\  mail_plugins = mail_compress quota sieve' /etc/dovecot/conf.d/15-lda.conf
			#  20-imap.conf
			sed -i "s/quota imap_quota/quota imap_quota imap_sieve/g" /etc/dovecot/conf.d/20-imap.conf
			# replace dovecot-sieve config files
			cp -f "$HESTIA_COMMON_DIR"/dovecot/2.4/sieve/* /etc/dovecot/conf.d
		fi
		chown -R root:root /etc/dovecot/
	fi
fi

# Configure Bind for Debian 13 or Ubuntu 26.04
if $IS_DEBIAN13_OR_UBUNTU2604; then
	source "$HESTIA"/conf/hestia.conf
	if [[ "$DNS_SYSTEM" =~ named|bind ]]; then
		# named.conf.default-zones was removed in Debian 13
		if [[ ! -f /etc/bind/named.conf.default-zones ]] \
			&& grep -q "include.*named.conf.default-zones" /etc/bind/named.conf 2> /dev/null; then
			echo "[ + ] Removing the default-zones include from named.conf"
			sed -i "/^include.*named.conf.default-zones/d" /etc/bind/named.conf
		fi

		# Add root-hints include after named.conf.local if missing
		if [[ -f /etc/bind/named.conf.root-hints ]] \
			&& ! grep -q 'include.*named.conf.root-hints' /etc/bind/named.conf 2> /dev/null; then
			echo "[ + ] Adding the root-hints include to named.conf"
			sed -i '/include.*named\.conf\.local/a include "\/etc\/bind\/named.conf.root-hints";' /etc/bind/named.conf
		fi
	fi
fi

# Fix old source.list for Node.js and bump to version 24
apt="/etc/apt/sources.list.d"
node_v=24

if [ $(uname -m) = "x86_64" ]; then
	ARCH=amd64
elif [ $(uname -m) = "aarch64" ]; then
	ARCH=arm64
fi

if [[ -f "$apt/nodejs.list" ]]; then
	echo "[ * ] Modifying Node.js repo to version $node_v"
	echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/nodejs.gpg] https://deb.nodesource.com/node_$node_v.x nodistro main" > "$apt"/nodejs.list
	curl -fsSLm60 https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor | tee /usr/share/keyrings/nodejs.gpg > /dev/null 2>&1
fi

# Fixing Subsystem sftp config for old installations
echo "[ * ] Fixing Subsystem sftp config"
if grep -q "^Subsystem.*internal-sftp-.*" /etc/ssh/sshd_config; then
	sed -i 's/Subsystem sftp internal-sftp-.*/Subsystem sftp internal-sftp/' /etc/ssh/sshd_config
	systemctl reload ssh
fi

if grep -q "^Subsystem.*/usr/lib/sftp-server-" /etc/ssh/sshd_config; then
	sed -i 's/^Subsystem sftp \/usr\/lib\/sftp-server-.*/Subsystem sftp \/usr\/lib\/sftp-server/' /etc/ssh/sshd_config
	systemctl reload ssh
fi

# Move phpMyAdmin tmp dir to /var/lib/phpmyadmin/tmp
phpmyadmin_conf="/etc/phpmyadmin/conf.d/01-localhost.php"
phpmyadmin_tempdir_conf="/etc/phpmyadmin/conf.d/02-tempdir.php"

if [[ -f "$phpmyadmin_conf" ]]; then
	mkdir -p /var/lib/phpmyadmin/tmp/
	chown -R hestiamail:www-data /var/lib/phpmyadmin/tmp/
	if [[ ! -f "$phpmyadmin_tempdir_conf" ]]; then
		cat > "$phpmyadmin_tempdir_conf" << 'EOF'
<?php
$cfg['TempDir'] = '/var/lib/phpmyadmin/tmp';
EOF
	fi
fi

# Patch Spamhaus DQS key leak in existing exim templates
echo "[ * ] Patching Exim Spamhaus DQS configuration"
if [ -f "/etc/exim4/exim4.conf.template" ]; then
	sed -i 's|at $dnslist_domain\\n$dnslist_text|at ${if match{$dnslist_domain}{^[^.]+[.](.+dq[.]spamhaus.*)}{$1}{$dnslist_domain}}\\n$dnslist_text|g' /etc/exim4/exim4.conf.template
fi

# Configuring sudoers to remove unsupported requiretty option on Ubuntu 26.04
if $IS_UBUNTU2604; then
	if [[ -f /etc/sudoers.d/hestiaweb ]] && grep -q '^Defaults:root !requiretty$' /etc/sudoers.d/hestiaweb &> /dev/null; then
		echo "[ + ] Configuring sudoers to remove unsupported requiretty option"
		chmod 640 /etc/sudoers.d/hestiaweb
		sed -i '/^Defaults:root !requiretty$/d' /etc/sudoers.d/hestiaweb
		chmod 440 /etc/sudoers.d/hestiaweb
	fi
fi

# Updating logrotate conf for Hestia
echo "[ * ] Updating logrotate conf for Hestia"
cp -f "$HESTIA"/install/deb/logrotate/hestia /etc/logrotate.d/hestia
