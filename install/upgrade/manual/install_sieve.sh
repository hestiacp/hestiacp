#!/bin/bash
# info: Install / remove  sieve / manage-sieve for Dovecot
#
# Thos function installs manage-sieve functionality for dovecot.

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

# Includes
# shellcheck source=/etc/hestiacp/hestia.conf
source /etc/hestiacp/hestia.conf
# shellcheck source=/usr/local/hestia/func/main.sh
source $HESTIA/func/main.sh
# load config file
source_conf "$HESTIA/conf/hestia.conf"
source_conf "$HESTIA/install/upgrade/upgrade.conf"

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

#check if string already exists
if grep "dovecot_virtual_delivery" /etc/exim4/exim4.conf.template; then
	echo "Plugin allready enabled"
	exit 0
fi

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

HAS_DOVECOT_SIEVE_INSTALLED=$(dpkg --get-selections dovecot-sieve | grep dovecot-sieve | wc -l)

# Folder paths
RC_INSTALL_DIR="/var/lib/roundcube"
RC_CONFIG_DIR="/etc/roundcube"

# If we want to install sieve
if [ "$HAS_DOVECOT_SIEVE_INSTALLED" = "0" ]; then

	# if sieve is not installed... install it.
	apt-get -qq install dovecot-sieve dovecot-managesieved -y

	# dovecot.conf install
	sed -i "s/namespace/service stats \{\n  unix_listener stats-writer \{\n    group = mail\n    mode = 0660\n    user = dovecot\n  \}\n\}\n\nnamespace/g" /etc/dovecot/dovecot.conf

	# dovecot conf files
	#  10-master.conf
	sed -i -E -z "s/  }\n  user = dovecot\n}/  \}\n  unix_listener auth-master \{\n    group = mail\n    mode = 0660\n    user = dovecot\n  \}\n  user = dovecot\n\}/g" /etc/dovecot/conf.d/10-master.conf
	#  15-lda.conf
	sed -i "s/\#mail_plugins = \\\$mail_plugins/mail_plugins = \$mail_plugins quota sieve\n  auth_socket_path = \/var\/run\/dovecot\/auth-master/g" /etc/dovecot/conf.d/15-lda.conf
	#  20-imap.conf
	sed -i "s/mail_plugins = quota imap_quota/mail_plugins = quota imap_quota imap_sieve/g" /etc/dovecot/conf.d/20-imap.conf

	# replace dovecot-sieve config files
	cp -f $HESTIA_COMMON_DIR/dovecot/sieve/* /etc/dovecot/conf.d

	# dovecot default file install
	mkdir -p /etc/dovecot/sieve
	echo -e "require [\"fileinto\"];\n# rule:[SPAM]\nif header :contains \"X-Spam-Flag\" \"YES\" {\n    fileinto \"INBOX.Spam\";\n}\n" > /etc/dovecot/sieve/default

	# exim4 install
	sed -i "s/\stransport = local_delivery/ transport = dovecot_virtual_delivery/" /etc/exim4/exim4.conf.template

	sed -i "s/address_pipe:/dovecot_virtual_delivery:\n  driver = pipe\n  command = \/usr\/lib\/dovecot\/dovecot-lda -e -d \${extract{1}{:}{\${lookup{\$local_part}lsearch{\/etc\/exim4\/domains\/\${lookup{\$domain}dsearch{\/etc\/exim4\/domains\/}}\/accounts}}}}@\${lookup{\$domain}dsearch{\/etc\/exim4\/domains\/}}\n  delivery_date_add\n  envelope_to_add\n  return_path_add\n  log_output = true\n  log_defer_output = true\n  user = \${extract{2}{:}{\${lookup{\$local_part}lsearch{\/etc\/exim4\/domains\/\${lookup{\$domain}dsearch{\/etc\/exim4\/domains\/}}\/passwd}}}}\n  group = mail\n  return_output\n\naddress_pipe:/g" /etc/exim4/exim4.conf.template

	# roundcube install
	mkdir -p $RC_CONFIG_DIR/plugins/managesieve

	cp -f $HESTIA_COMMON_DIR/roundcube/plugins/config_managesieve.inc.php $RC_CONFIG_DIR/plugins/managesieve/config.inc.php
	ln -s $RC_CONFIG_DIR/plugins/managesieve/config.inc.php $RC_INSTALL_DIR/plugins/managesieve/config.inc.php

	# permission changes
	chown -R dovecot:mail /var/log/dovecot.log
	chmod 660 /var/log/dovecot.log

	chown -R root:www-data $RC_CONFIG_DIR/
	chmod 751 -R $RC_CONFIG_DIR

	chmod 644 $RC_CONFIG_DIR/plugins/managesieve/config.inc.php

	sed -i "s/\"archive\"/\"archive\", \"managesieve\"/g" $RC_CONFIG_DIR/config.inc.php

	#restart dovecot and exim4
	systemctl restart dovecot > /dev/null 2>&1
	systemctl restart exim4 > /dev/null 2>&1
else
	# Uninstall sieve if it exist
	if [ -f "/etc/dovecot/conf.d/90-sieve.conf" ]; then

		# dovecot.conf multiline sed
		sed -i -E -z "s/service stats \{\n  unix_listener stats-writer \{\n    group = mail\n    mode = 0660\n    user = dovecot\n  \}\n\}\n\n//g" /etc/dovecot/dovecot.conf

		# dovecot conf files
		#  10-master.conf
		sed -i -E -z "s/  \}\n  unix_listener auth-master \{\n    group = mail\n    mode = 0660\n    user = dovecot\n  \}\n  user = dovecot\n\}/  \}\n  user = dovecot\n\}/g" /etc/dovecot/conf.d/10-master.conf
		#  15-lda.conf
		sed -i -E -z "s/mail_plugins = \\\$mail_plugins sieve\n  auth_socket_path = \/run\/dovecot\/auth-master/\#mail_plugins = \$mail_plugins/g" /etc/dovecot/conf.d/15-lda.conf
		#  20-imap.conf
		sed -i "s/mail_plugins = quota imap_quota imap_sieve/mail_plugins = quota imap_quota/g" /etc/dovecot/conf.d/20-imap.conf

		# Delete dovecot-sieve config files
		rm -f /etc/dovecot/conf.d/20-managesieve.conf
		rm -f /etc/dovecot/conf.d/90-sieve-extprograms.conf
		rm -f /etc/dovecot/conf.d/90-sieve.conf

		# Dovecot default file
		rm -r -f /etc/dovecot/sieve

		# If sieve is installed... remove it.
		apt-get -qq remove --purge dovecot-sieve -y

		# Exim4
		sed -i "s/\stransport = dovecot_virtual_delivery/ transport = local_delivery/" /etc/exim4/exim4.conf.template
		sed -i "s/dovecot_virtual_delivery:\n  driver = pipe\n  command = \/usr\/lib\/dovecot\/dovecot-lda -e -d \${extract{1}{:}{\${lookup{\$local_part}lsearch{\/etc\/exim4\/domains\/\${lookup{\$domain}dsearch{\/etc\/exim4\/domains/}}\/accounts}}}}@\${lookup{\$domain}dsearch{\/etc\/exim4\/domains\/}}\n  delivery_date_add\n  envelope_to_add\n  return_path_add\n  log_output = true\n  log_defer_output = true\n  user = \${extract{2}{:}{\${lookup{\$local_part}lsearch{\/etc\/exim4\/domains\/\${lookup{\$domain}dsearch{\/etc\/exim4\/domains\/}}\/passwd}}}}\n  group = mail\n  return_output\n//g" /etc/exim4/exim4.conf.template

		# Roundcube
		rm -f -r $RC_CONFIG_DIR/plugins/managesieve
		rm -f $RC_INSTALL_DIR/plugins/managesieve/config.inc.php
		sed -i "s/\"archive\", \"managesieve\"/\"archive\"/g" $RC_CONFIG_DIR/config.inc.php

		#restart dovecot and exim4
		systemctl restart dovecot > /dev/null 2>&1
		systemctl restart exim4 > /dev/null 2>&1
	fi
fi
