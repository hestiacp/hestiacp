#!/bin/bash
# info: Configure sudoers, SSH, Bind, Dovecot and Exim to work with Hestia on Ubuntu 26.04

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

	# named.conf.default-zones was removed in Ubuntu 26.04
	# The include must go regardless of whether the file exists on disk
	if grep -qE '^[[:space:]]*include.*named\.conf\.default-zones' /etc/bind/named.conf 2> /dev/null; then
		echo "[ + ] Removing the default-zones include from named.conf"
		sed -i -E '/^[[:space:]]*include.*named\.conf\.default-zones/d' /etc/bind/named.conf
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
	mkdir -p /etc/apparmor.d/local
	if [[ ! -f /etc/apparmor.d/local/proftpd ]]; then
		echo "[ + ] Updating AppArmor rules for ProFTPd"
		cat > /etc/apparmor.d/local/proftpd << 'EOF'
# Configuration added by Hestia
/usr/local/hestia/ssl/certificate.crt r,
/usr/local/hestia/ssl/certificate.key r,
/run/proftpd.sock rw,
EOF
		apparmor_parser -r /etc/apparmor.d/proftpd 2> /dev/null
		restart_proftpd=true
	elif ! grep -qF "# Configuration added by Hestia" /etc/apparmor.d/local/proftpd; then
		echo "[ + ] Updating AppArmor rules for ProFTPd"
		cat >> /etc/apparmor.d/local/proftpd << 'EOF'
# Configuration added by Hestia
/usr/local/hestia/ssl/certificate.crt r,
/usr/local/hestia/ssl/certificate.key r,
/run/proftpd.sock rw,
EOF
		apparmor_parser -r /etc/apparmor.d/proftpd 2> /dev/null
		restart_proftpd=true
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

#----------------------------------------------------------#
#              tnftp AppArmor configuration                #
#----------------------------------------------------------#

echo
echo "[ * ] Checking tnftp AppArmor configuration:"
# func/main.sh resets BACKUP='/backup' when sourced, so read the configured
# BACKUP path directly from hestia.conf before falling back to $BACKUP.
_tnftp_cfg_backup="$(grep "^BACKUP=" "$HESTIA/conf/hestia.conf" 2> /dev/null | cut -d "'" -f 2)"
_tnftp_cfg_backup="${_tnftp_cfg_backup:-${BACKUP:-/backup}}"
_tnftp_local="/etc/apparmor.d/local/tnftp"

if [[ -L /etc/apparmor.d/tnftp || -L /etc/apparmor.d/local || -L "$_tnftp_local" ]]; then
	echo "[ ! ] Error: Refusing to follow symlink under /etc/apparmor.d for tnftp" >&2
elif [[ -f /etc/apparmor.d/tnftp ]]; then
	if mkdir -p /etc/apparmor.d/local && [[ ! -L /etc/apparmor.d/local ]] && _tnftp_tmp="$(umask 077 && mktemp /etc/apparmor.d/local/.tnftp.XXXXXX)"; then
		_tnftp_extra=""
		if [[ -f "$_tnftp_local" ]]; then
			_tnftp_extra="$(awk '
				/^# Begin configuration added by Hestia$/ { skip = 1; next }
				/^# End configuration added by Hestia$/ { skip = 0; next }
				!skip { print }
			' "$_tnftp_local")"
		fi
		if {
			if [[ "$_tnftp_extra" == *[^[:space:]]* ]]; then
				printf '%s\n' "$_tnftp_extra"
			fi
			{
				printf '# Begin configuration added by Hestia\n'
				printf 'capability dac_read_search,\n'
				printf 'capability dac_override,\n'
				printf '/backup/ r,\n'
				printf '/backup/** rwk,\n'
				for _tnftp_cand in \
					"$_tnftp_cfg_backup" \
					"$(realpath -e /backup 2> /dev/null || true)" \
					"$(realpath -e "$_tnftp_cfg_backup" 2> /dev/null || true)"; do
					_bdir="$_tnftp_cand"
					while [[ "$_bdir" == */ && "$_bdir" != "/" ]]; do
						_bdir="${_bdir%/}"
					done
					if [[ -n "$_bdir" && "$_bdir" != "/" && "$_bdir" != "/backup" ]] \
						&& [[ "$_bdir" != *$'\n'* && "$_bdir" != *$'\r'* ]] \
						&& [[ "$_bdir" =~ ^/[A-Za-z0-9._/-]+$ ]] \
						&& [[ "$_bdir" != *//* ]] \
						&& [[ ! "$_bdir" =~ (^|/)\.\.(/|$) ]]; then
						printf '%s/ r,\n' "$_bdir"
						printf '%s/** rwk,\n' "$_bdir"
					fi
				done
				printf '# End configuration added by Hestia\n'
			} | awk '!seen[$0]++'
		} > "$_tnftp_tmp" && chmod 644 "$_tnftp_tmp" && [[ ! -L "$_tnftp_local" ]]; then
			if [[ ! -f "$_tnftp_local" ]] || ! cmp -s "$_tnftp_tmp" "$_tnftp_local"; then
				echo "[ + ] Updating AppArmor rules for tnftp"
				mv -f "$_tnftp_tmp" "$_tnftp_local"
				if command -v apparmor_parser > /dev/null 2>&1; then
					apparmor_parser -r /etc/apparmor.d/tnftp > /dev/null 2>&1 || true
				fi
			else
				rm -f "$_tnftp_tmp"
				echo "[ - ] tnftp AppArmor rules already configured, nothing to do"
			fi
		else
			rm -f "$_tnftp_tmp"
			echo "[ ! ] Error: Failed to update AppArmor rules for tnftp" >&2
		fi
	else
		echo "[ ! ] Error: Failed to update AppArmor rules for tnftp" >&2
	fi
else
	echo "[ - ] tnftp AppArmor profile not found, skipping"
fi
unset _tnftp_cfg_backup _tnftp_local _tnftp_tmp _tnftp_extra _tnftp_cand _bdir

echo
echo "[ * ] Configuration finished!"
