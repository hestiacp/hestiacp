#!/bin/bash
# info: Configure APT repositories, SSH, Bind, Dovecot, Exim and ProFTPd to work with Hestia on Debian 13

#----------------------------------------------------------#
#                    Variables & Functions                 #
#----------------------------------------------------------#

[[ $EUID -eq 0 ]] || {
	echo "You must be root to run the script" >&2
	exit 1
}

source /etc/hestiacp/hestia.conf
source "$HESTIA/conf/hestia.conf"
source "$HESTIA/func/main.sh"

[[ -f /etc/os-release ]] && source /etc/os-release

if [[ "$ID" != "debian" || "$VERSION_ID" != "13" ]]; then
	echo "Error: this script requires Debian 13" >&2
	exit 1
fi

#----------------------------------------------------------#
#               APT repositories configuration             #
#----------------------------------------------------------#

echo "[ * ] Checking APT repositories:"
apt="/etc/apt/sources.list.d"
codename="trixie"
RHOST="apt.hestiacp.com"
mariadb_v="11.8"
node_v="24"
_apt_updated=false

case $(arch) in
	x86_64)
		ARCH="amd64"
		;;
	aarch64)
		ARCH="arm64"
		;;
	*)
		echo "Error: $(arch) is currently not supported" >&2
		exit 1
		;;
esac

ensure_repo() {
	local name="$1"
	local list_file="$2"
	local expected_line="$3"
	local keyring_file="$4"
	local key_url="$5"
	local changed=false

	# Clean up .disabled, .distUpgrade, and .save files if present
	for ext in disabled distUpgrade save; do
		if [[ -f "${list_file}.${ext}" ]]; then
			echo "[ + ] Removing disabled repository file: ${list_file}.${ext}"
			rm -f "${list_file}.${ext}"
			changed=true
		fi
	done

	if [[ ! -f "$list_file" ]] || [[ "$(cat "$list_file" 2> /dev/null)" != "$expected_line" ]]; then
		echo "[ + ] Configuring $name APT repository"
		echo "$expected_line" > "$list_file"
		changed=true
	else
		echo "[ - ] $name APT repository already configured, nothing to do"
	fi

	if [[ ! -s "$keyring_file" ]]; then
		echo "[ + ] Installing $name GPG keyring"
		mkdir -p "$(dirname "$keyring_file")"
		_tmp_key="$(mktemp)"
		if curl -fsSL "$key_url" 2> /dev/null | gpg --dearmor 2> /dev/null > "$_tmp_key" && [[ -s "$_tmp_key" ]]; then
			mv -f "$_tmp_key" "$keyring_file"
			chmod 644 "$keyring_file"
			changed=true
		else
			rm -f "$_tmp_key"
			echo "[ ! ] Error downloading/installing GPG key for $name" >&2
		fi
	fi

	if [[ "$changed" = true ]]; then
		_apt_updated=true
	fi
}

# Hestia Control Panel repository
ensure_repo "Hestia Control Panel" \
	"$apt/hestia.list" \
	"deb [arch=$ARCH signed-by=/usr/share/keyrings/hestia-keyring.gpg] https://$RHOST/ $codename main" \
	"/usr/share/keyrings/hestia-keyring.gpg" \
	"https://$RHOST/pubkey.gpg"

# NGINX repository
ensure_repo "NGINX" \
	"$apt/nginx.list" \
	"deb [arch=$ARCH signed-by=/usr/share/keyrings/nginx-keyring.gpg] https://nginx.org/packages/mainline/debian/ $codename nginx" \
	"/usr/share/keyrings/nginx-keyring.gpg" \
	"https://nginx.org/keys/nginx_signing.key"

# Sury PHP repository
ensure_repo "PHP (Sury)" \
	"$apt/php.list" \
	"deb [arch=$ARCH signed-by=/usr/share/keyrings/sury-keyring.gpg] https://packages.sury.org/php/ $codename main" \
	"/usr/share/keyrings/sury-keyring.gpg" \
	"https://packages.sury.org/php/apt.gpg"

# Apache2 repository (if installed or previously configured on Debian)
if [[ -f "$apt/apache2.list" ]] || [[ -f "$apt/apache2.list.disabled" ]] || [[ -f "$apt/apache2.list.distUpgrade" ]] \
	|| [[ "$WEB_SYSTEM" =~ apache2 ]] || dpkg -s apache2 &> /dev/null; then
	ensure_repo "Apache2 (Sury)" \
		"$apt/apache2.list" \
		"deb [arch=$ARCH signed-by=/usr/share/keyrings/apache2-keyring.gpg] https://packages.sury.org/apache2/ $codename main" \
		"/usr/share/keyrings/apache2-keyring.gpg" \
		"https://packages.sury.org/apache2/apt.gpg"
fi

# MariaDB repository (if installed or previously configured)
if [[ -f "$apt/mariadb.list" ]] || [[ -f "$apt/mariadb.list.disabled" ]] || [[ -f "$apt/mariadb.list.distUpgrade" ]] \
	|| { [[ "$DB_SYSTEM" =~ mysql ]] && (dpkg -s mariadb-server &> /dev/null || (command -v mariadb &> /dev/null && ! dpkg -s mysql-server &> /dev/null)); }; then
	ensure_repo "MariaDB $mariadb_v" \
		"$apt/mariadb.list" \
		"deb [arch=$ARCH signed-by=/usr/share/keyrings/mariadb-keyring.gpg] https://dlm.mariadb.com/repo/mariadb-server/$mariadb_v/repo/debian $codename main" \
		"/usr/share/keyrings/mariadb-keyring.gpg" \
		"https://mariadb.org/mariadb_release_signing_key.asc"
fi

# MySQL repository (if previously configured on Debian)
for ext in disabled distUpgrade save; do
	if [[ -f "$apt/mysql.list.${ext}" ]]; then
		echo "[ + ] Re-enabling MySQL repository file: $apt/mysql.list"
		mv -f "$apt/mysql.list.${ext}" "$apt/mysql.list"
		_apt_updated=true
	fi
done

# PostgreSQL repository (if installed or previously configured)
if [[ -f "$apt/postgresql.list" ]] || [[ -f "$apt/postgresql.list.disabled" ]] || [[ -f "$apt/postgresql.list.distUpgrade" ]] \
	|| [[ "$DB_SYSTEM" =~ pgsql ]] || dpkg -s postgresql &> /dev/null; then
	ensure_repo "PostgreSQL" \
		"$apt/postgresql.list" \
		"deb [arch=$ARCH signed-by=/usr/share/keyrings/postgresql-keyring.gpg] https://apt.postgresql.org/pub/repos/apt/ $codename-pgdg main" \
		"/usr/share/keyrings/postgresql-keyring.gpg" \
		"https://www.postgresql.org/media/keys/ACCC4CF8.asc"
fi

# Node.js repository (if installed or previously configured)
if [[ -f "$apt/nodejs.list" ]] || [[ -f "$apt/nodejs.list.disabled" ]] || [[ -f "$apt/nodejs.list.distUpgrade" ]] \
	|| dpkg -s hestia-web-terminal &> /dev/null || dpkg -s nodejs &> /dev/null; then
	ensure_repo "Node.js $node_v" \
		"$apt/nodejs.list" \
		"deb [arch=$ARCH signed-by=/usr/share/keyrings/nodejs.gpg] https://deb.nodesource.com/node_$node_v.x nodistro main" \
		"/usr/share/keyrings/nodejs.gpg" \
		"https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key"
fi

if [[ "$_apt_updated" = true ]]; then
	echo "[ + ] Updating APT package cache..."
	if apt-get update; then
		echo "[ + ] APT package cache updated successfully"
		echo "[ ! ] Notice: Please run 'apt-get full-upgrade' to complete upgrading packages from updated repositories"
	else
		echo "[ ! ] Warning: 'apt-get update' encountered errors" >&2
	fi
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
			grep -q 'imap_quota imap_sieve' /etc/dovecot/conf.d/20-imap.conf \
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

#----------------------------------------------------------#
#                    Exim configuration                    #
#----------------------------------------------------------#

echo "[ * ] Checking Exim configuration:"
if [[ $MAIL_SYSTEM == exim4 ]] \
	&& [[ $(dovecot --version) == 2.4* ]] \
	&& [[ -f /etc/exim4/exim4.conf.template ]]; then
	sed -i.bak '
s#  directory = "${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/mail/${lookup{$domain}dsearch{/etc/exim4/domains/}}/${lookup{$local_part}dsearch{${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/mail/${lookup{$domain}dsearch{/etc/exim4/domains/}}}}"#  directory = "${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}"#

s#  directory = "${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/mail/${lookup{$domain}dsearch{/etc/exim4/domains/}}/${lookup{$local_part}dsearch{${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/mail/${lookup{$domain}dsearch{/etc/exim4/domains/}}}}/.Spam"#  directory = "${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/.Spam"#

s#  quota_directory = "${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/mail/${lookup{$domain}dsearch{/etc/exim4/domains/}}/${lookup{$local_part}dsearch{${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/mail/${lookup{$domain}dsearch{/etc/exim4/domains/}}}}"#  quota_directory = "${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}"#
' /etc/exim4/exim4.conf.template
	if ! cmp -s /etc/exim4/exim4.conf.template{.bak,}; then
		echo "[ + ] Local delivery directives fixed in Exim configuration"
		if systemctl restart exim4 &> /dev/null; then
			echo "[ + ] Exim successfully restarted"
		else
			echo "[ ! ] Error restarting Exim" >&2
			systemctl status exim4 --no-pager -l >&2
			echo "[ + ] Recovering configuration backup"
			cp -f /etc/exim4/exim4.conf.template.bak /etc/exim4/exim4.conf.template

			if systemctl restart exim4 &> /dev/null; then
				echo "[ + ] Exim successfully restarted after recovery"
			else
				echo "[ ! ] Error restarting Exim after recovery" >&2
				systemctl status exim4 --no-pager -l >&2
			fi
		fi
	else
		echo "[ * ] Exim configuration already up to date"
	fi

fi
echo

#----------------------------------------------------------#
#                   ProFTPd configuration                    #
#----------------------------------------------------------#

if [[ "$FTP_SYSTEM" =~ proftpd ]]; then
	restart_proftpd=false
	echo "[ * ] Checking ProFTPd:"
	if ! dpkg -s proftpd-mod-crypto &> /dev/null; then
		echo "[ + ] Installing proftpd-mod-crypto:"
		if apt-get install -y proftpd-mod-crypto &> /dev/null; then
			echo "[ + ] proftpd-mod-crypto successfully installed"
		else
			echo "[ ! ] Error installing proftpd-mod-crypto" >&2
		fi
	else
		echo "[ - ] proftpd-mod-crypto already installed, nothing to do"
	fi

	if [[ -f /etc/proftpd/modules.conf.proftpd-new ]] && [[ -f /etc/proftpd/modules.conf ]] && ! grep -qE "^[[:space:]]*LoadModule[[:space:]]+mod_xfer\.c" /etc/proftpd/modules.conf; then
		echo "[ + ] Ensuring xfer module is enabled"
		if grep -qE "^[[:space:]]*LoadModule[[:space:]]+mod_xfer\.c" /etc/proftpd/modules.conf.proftpd-new; then
			cp /etc/proftpd/modules.conf "/etc/proftpd/modules.conf.hestia-backup-$(date +%Y-%m-%d)"
			cp /etc/proftpd/modules.conf.proftpd-new /etc/proftpd/modules.conf
			restart_proftpd=true
		else
			echo "[ ! ] Error: mod_xfer.c is not enabled in modules.conf.proftpd-new either"
		fi
	fi

	if [[ -f /etc/proftpd/modules.conf ]] && grep -qF "Include /etc/proftpd/tls.conf" /etc/proftpd/proftpd.conf; then
		if ! grep -qF "Include /etc/proftpd/modules.conf" /etc/proftpd/proftpd.conf; then
			echo "[ + ] Updating ProFTPd configuration:"
			sed -i '\|Include /etc/proftpd/tls.conf|i Include /etc/proftpd/modules.conf' /etc/proftpd/proftpd.conf
			restart_proftpd=true
		else
			echo "[ - ] ProFTPd already configured, nothing to do"
		fi
	fi

	if $restart_proftpd; then
		if systemctl restart proftpd &> /dev/null; then
			echo "[ + ] ProFTPd successfully restarted"
		else
			echo "[ ! ] Error restarting ProFTPd" >&2
			systemctl status proftpd --no-pager -l >&2
		fi
	fi

fi

echo
echo "[ * ] Configuration finished!"
