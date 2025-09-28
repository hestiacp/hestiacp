#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.x.x

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
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

if [ -f /etc/os-release ]; then
	source /etc/os-release
fi

# If Dovecot is version 2.4 and Debian is Trixie (13), replace Dovecot's configuration and rebuild users
dovecot_version="$(dovecot --version | cut -f -2 -d .)"
if [[ "$ID" == "debian" && "$VERSION_ID" == "13" && "$dovecot_version" = "2.4" ]]; then
	if grep -q 'modified by Hestia' /etc/dovecot/dovecot.conf \
		&& grep -q 'ssl_server_cert_file = /usr/local/hestia' /etc/dovecot/conf.d/10-ssl.conf; then
		# as Dovecot is already using the new conf, do nothing"
		upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
	else
		cp -f "$HESTIA_COMMON_DIR"/dovecot-24/dovecot.conf /etc/dovecot/
		cp -f "$HESTIA_COMMON_DIR"/dovecot-24/conf.d/* /etc/dovecot/conf.d/
		# rebuild users to apply new dovecot conf
		upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
		# if sieve is installed, replace dovecot conf files
		HAS_DOVECOT_SIEVE_INSTALLED=$(dpkg --get-selections dovecot-managesieved | grep -c dovecot-managesieved)
		if [ "$HAS_DOVECOT_SIEVE_INSTALLED" = "0" ]; then
			# dovecot.conf install
			sed -i -E 's/protocols = imap/protocols = sieve imap/' /etc/dovecot/dovecot.conf
			#  10-master.conf
			sed -i -E -z 's/    user = dovecot\n  \}\n\}/    user = dovecot\n  \}\n\n  unix_listener auth-master {\n    group = mail\n    mode = 0660\n    user = dovecot\n  }\n\}/' /etc/dovecot/conf.d/10-master.conf
			#  15-lda.conf
			sed -i '/^protocol lda {$/a\  mail_plugins = mail_compress quota sieve' /etc/dovecot/conf.d/15-lda.conf
			#  20-imap.conf
			sed -i "s/quota imap_quota/quota imap_quota imap_sieve/g" /etc/dovecot/conf.d/20-imap.conf
			# replace dovecot-sieve config files
			cp -f "$HESTIA_COMMON_DIR"/dovecot-24/sieve/* /etc/dovecot/conf.d
		fi
		chown -R root:root /etc/dovecot/
	fi
else
	upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
fi
