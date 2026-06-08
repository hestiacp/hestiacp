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

# Apply SSH config if running on Debian 13
if [[ "$ID" == "debian" && "$VERSION_ID" == "13" ]]; then
	_KEX_CONF="/etc/ssh/sshd_config.d/hestia-kex.conf"
	_KEX_LINE="KexAlgorithms +diffie-hellman-group-exchange-sha256"

	# Only create/modify the file if it doesn't already contain the correct config
	if [[ ! -f "$_KEX_CONF" ]] || ! grep -qxF "$_KEX_LINE" "$_KEX_CONF"; then
		echo "[ * ] Creating $_KEX_CONF"
		echo "$_KEX_LINE" > "$_KEX_CONF"
		"$BIN"/v-restart-service ssh
	fi
fi

# If Dovecot is version 2.4 and Debian is Trixie (13), replace Dovecot's configuration and rebuild users
dovecot_version="$(dovecot --version | cut -f -2 -d .)"
if [[ "$ID" == "debian" && "$VERSION_ID" == "13" && "$dovecot_version" = "2.4" ]]; then
	if ! grep -q 'modified by Hestia' /etc/dovecot/dovecot.conf \
		&& ! grep -q 'ssl_server_cert_file = /usr/local/hestia' /etc/dovecot/conf.d/10-ssl.conf; then
		echo "[ * ] Updating Dovecot $dovecot_version configuration"
		cp -f "$HESTIA_COMMON_DIR"/dovecot/2.4/dovecot.conf /etc/dovecot/
		cp -f "$HESTIA_COMMON_DIR"/dovecot/2.4/conf.d/* /etc/dovecot/conf.d/
		# rebuild users to apply new dovecot conf
		upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
		# if sieve is installed, replace dovecot conf files
		HAS_DOVECOT_SIEVE_INSTALLED=$(dpkg --get-selections dovecot-managesieved | grep -c dovecot-managesieved)
		if [ "$HAS_DOVECOT_SIEVE_INSTALLED" = "1" ]; then
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

# Configure Bind for Debian 13
if [[ "$ID" == "debian" && "$VERSION_ID" == "13" ]]; then
	source "$HESTIA"/conf/hestia.conf
	if [[ "$DNS_SYSTEM" =~ named|bind ]]; then
		echo "[ * ] Configuring Bind DNS server for Debian 13"
		cp -f "$HESTIA_INSTALL_DIR"/bind/named.conf /etc/bind/
		cp -f "$HESTIA_INSTALL_DIR"/bind/named.conf.options /etc/bind/
		chown root:bind /etc/bind/named.conf
		chown root:bind /etc/bind/named.conf.options
		chown bind:bind /var/cache/bind
		chmod 640 /etc/bind/named.conf
		chmod 640 /etc/bind/named.conf.options
		aa-complain /usr/sbin/named 2> /dev/null
		if [[ $(dpkg-query -W -f='${Status}' apparmor 2> /dev/null | grep -c "ok installed") -eq 0 ]]; then
			apparmor="no"
		else
			apparmor="yes"
		fi
		if [[ "$apparmor" = 'yes' ]]; then
			echo "/home/** rwm," >> /etc/apparmor.d/local/usr.sbin.named 2> /dev/null
			systemctl status apparmor > /dev/null 2>&1
			if [ $? -ne 0 ]; then
				systemctl restart apparmor >> $LOG
			fi
		fi
		# Debian 13 removed the named.conf.default-zones file if doesn't exsists remove it from the config file
		if [ ! -f /etc/bind/named.conf.default-zones ]; then
			sed -i "/^include.*named.conf.default-zones/d" /etc/bind/named.conf
		fi
		update-rc.d bind9 defaults > /dev/null 2>&1
		systemctl start bind9
		check_result $? "bind9 start failed"

		# Workaround for OpenVZ/Virtuozzo
		if [ -e "/proc/vz/veinfo" ] && [ -e "/etc/rc.local" ]; then
			sed -i "s/^exit 0/service bind9 restart\nexit 0/" /etc/rc.local
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
	curl -s https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor | tee /usr/share/keyrings/nodejs.gpg > /dev/null 2>&1
fi
