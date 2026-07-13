#!/bin/bash
# info: Configure sudoers, SSH, Bind and Dovecot to work with Hestia on Ubuntu 26.04

#----------------------------------------------------------#
#                    Variables & Functions                 #
#----------------------------------------------------------#

[[ $EUID -eq 0 ]] || {
	echo "You must be root to run the script" >&2
	exit 1
}

source /etc/hestiacp/hestia.conf
source $HESTIA/conf/hestia.conf
source $HESTIA/func/main.sh

[[ -f /etc/os-release ]] && source /etc/os-release

if [[ "$ID" != "ubuntu" || "$VERSION_ID" != "26.04" ]]; then
	echo "Error: this script requires Ubuntu 26.04" >&2
	exit 1
fi

#----------------------------------------------------------#
#                  sudoers configuration                   #
#----------------------------------------------------------#

echo "[ * ] Checking sudoers config:"
if [[ -f /etc/sudoers.d/hestiaweb ]] && grep -q '^Defaults:root !requiretty$' /etc/sudoers.d/hestiaweb &> /dev/null; then
	echo "[ + ] Configuring sudoers to remove unsupported requiretty option"
	chmod 640 /etc/sudoers.d/hestiaweb
	sed -i '/^Defaults:root !requiretty$/d' /etc/sudoers.d/hestiaweb
	chmod 440 /etc/sudoers.d/hestiaweb
else
	echo "[ - ] sudoers already configured, nothing to do"
fi
echo

#----------------------------------------------------------#
#                    SSH configuration                     #
#----------------------------------------------------------#

_KEX_CONF="/etc/ssh/sshd_config.d/hestia-kex.conf"
_KEX_LINE="KexAlgorithms +diffie-hellman-group-exchange-sha256"

echo "[ * ] Checking SSH KexAlgorithms config:"
if [[ ! -f "$_KEX_CONF" ]] || ! grep -qxF "$_KEX_LINE" "$_KEX_CONF"; then
	echo "[ + ] Configuring SSH KexAlgorithms"
	echo "$_KEX_LINE" > "$_KEX_CONF"
	"$BIN"/v-restart-service ssh
else
	echo "[ - ] SSH KexAlgorithms already configured, nothing to do"
fi
echo

#----------------------------------------------------------#
#                   Dovecot configuration                  #
#----------------------------------------------------------#

if command -v dovecot &> /dev/null; then
	dovecot_version="$(dovecot --version | cut -f -2 -d .)"
else
	dovecot_version=false
fi

echo "[ * ] Checking Dovecot configuration:"
if [[ "$dovecot_version" = "2.4" ]]; then
	if ! grep -q 'modified by Hestia' /etc/dovecot/dovecot.conf \
		|| ! grep -q 'ssl_server_cert_file = /usr/local/hestia' /etc/dovecot/conf.d/10-ssl.conf; then
		echo "[ + ] Updating Dovecot $dovecot_version configuration"
		cp -f "$HESTIA_COMMON_DIR"/dovecot/2.4/dovecot.conf /etc/dovecot/
		cp -f "$HESTIA_COMMON_DIR"/dovecot/2.4/conf.d/* /etc/dovecot/conf.d/

		if [[ -n "$MAIL_SYSTEM" ]]; then
			echo "[ + ] Rebuilding mail domains"
			export restart="no"
			"$BIN/v-list-users" list | while read -r user; do
				echo "      - $user..."
				$BIN/v-rebuild-mail-domains "$user" &> /dev/null
			done
		fi

		HAS_DOVECOT_SIEVE_INSTALLED=$(dpkg --get-selections dovecot-managesieved 2> /dev/null | grep -c dovecot-managesieved)
		if [[ "$HAS_DOVECOT_SIEVE_INSTALLED" = "1" ]]; then
			echo "[ * ] Updating Sieve $dovecot_version configuration"
			grep -q 'protocols = sieve' /etc/dovecot/dovecot.conf \
				|| sed -i -E 's/protocols = imap/protocols = sieve imap/' /etc/dovecot/dovecot.conf
			grep -q 'unix_listener auth-master' /etc/dovecot/conf.d/10-master.conf \
				|| sed -i -E -z 's/    user = dovecot\n  \}\n\}/    user = dovecot\n  \}\n\n  unix_listener auth-master {\n    group = mail\n    mode = 0660\n    user = dovecot\n  }\n\}/' /etc/dovecot/conf.d/10-master.conf
			grep -q 'mail_plugins.*sieve' /etc/dovecot/conf.d/15-lda.conf \
				|| sed -i '/^protocol lda {$/a\  mail_plugins = mail_compress quota sieve' /etc/dovecot/conf.d/15-lda.conf
			grep -q 'imap_sieve' /etc/dovecot/conf.d/20-imap.conf \
				|| sed -i "s/quota imap_quota/quota imap_quota imap_sieve/g" /etc/dovecot/conf.d/20-imap.conf
			cp -f "$HESTIA_COMMON_DIR"/dovecot/2.4/sieve/* /etc/dovecot/conf.d
		fi

		chown -R root:root /etc/dovecot/
		if systemctl restart dovecot &> /dev/null; then
			echo "[ + ] Dovecot successfully restarted"
		else
			echo "[ ! ] Error restarting dovecot" >&2
			systemctl status dovecot --no-pager -l >&2
		fi
	else
		echo "[ - ] Dovecot already configured by Hestia, nothing to do"
	fi
elif [[ "$dovecot_version" = "false" ]]; then
	echo "[ - ] Dovecot not installed, skipping"
else
	echo "[ - ] Dovecot $dovecot_version not supported, skipping"
fi
echo

#----------------------------------------------------------#
#                    Bind configuration                    #
#----------------------------------------------------------#

echo "[ * ] Checking Bind DNS configuration:"
if [[ "$DNS_SYSTEM" =~ named|bind ]]; then
	_bind_changed=false

	# named.conf.default-zones was removed in Debian 13
	if [[ ! -f /etc/bind/named.conf.default-zones ]] \
		&& grep -q "include.*named.conf.default-zones" /etc/bind/named.conf 2> /dev/null; then
		echo "[ + ] Removing the default-zones include from named.conf"
		sed -i "/^include.*named.conf.default-zones/d" /etc/bind/named.conf
		_bind_changed=true
	else
		echo "[ - ] default-zones include already clean, nothing to do"
	fi

	# Add root-hints include after named.conf.local if missing
	if [[ -f /etc/bind/named.conf.root-hints ]] \
		&& ! grep -q 'include.*named.conf.root-hints' /etc/bind/named.conf 2> /dev/null; then
		echo "[ + ] Adding the root-hints include to named.conf"
		sed -i '/include.*named\.conf\.local/a include "\/etc\/bind\/named.conf.root-hints";' /etc/bind/named.conf
		_bind_changed=true
	else
		echo "[ - ] root-hints include already present, nothing to do"
	fi

	if [[ "$_bind_changed" = true ]]; then
		if systemctl restart named &> /dev/null; then
			echo "[ + ] Bind 9 successfully restarted"
		else
			echo "[ ! ] Error restarting Bind 9" >&2
			systemctl status named --no-pager -l >&2
		fi
	fi
else
	echo "[ - ] Bind/named not in use (DNS_SYSTEM=$DNS_SYSTEM), skipping"
fi
echo

echo "[ * ] Configuration finished!"
