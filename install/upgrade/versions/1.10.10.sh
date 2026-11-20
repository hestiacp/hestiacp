#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.10

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

# Fix per-domain SMTP relay lookup for authenticated null-sender mail on existing Exim installations
exim_conf="${exim_conf:-/etc/exim4/exim4.conf.template}"
exim_conf_bak="${exim_conf}.before_smtp_relay_null_sender.bak"
pattern_dsearch='SMTP_RELAY_FILE = ${if exists{/etc/exim4/domains/${lookup{$sender_address_domain}dsearch{/etc/exim4/domains}}/smtp_relay.conf}{/etc/exim4/domains/${lookup{$sender_address_domain}dsearch{/etc/exim4/domains}}/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}'
pattern_legacy='SMTP_RELAY_FILE = ${if exists{/etc/exim4/domains/${sender_address_domain}/smtp_relay.conf}{/etc/exim4/domains/$sender_address_domain/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}'
# NOTE: no ":" may appear in the new block (SMTP_RELAY_FILE is used inside colon-separated
# lists such as require_files), hence eq{}{} instead of def:.
new_block='RELAY_SENDER_DOMAIN = ${if eq{$sender_address_domain}{}{${extract{2}{@}{$authenticated_id}}}{$sender_address_domain}}
RELAY_DOMAIN_DIR = /etc/exim4/domains/${lookup{RELAY_SENDER_DOMAIN}dsearch{/etc/exim4/domains}}
SMTP_RELAY_FILE = ${if exists{RELAY_DOMAIN_DIR/smtp_relay.conf}{RELAY_DOMAIN_DIR/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}'

if [[ -f "$exim_conf" ]] \
	&& ! grep -qE '^RELAY_(SENDER_DOMAIN|DOMAIN_DIR)[[:space:]]*=' "$exim_conf" \
	&& { grep -qFx "$pattern_dsearch" "$exim_conf" || grep -qFx "$pattern_legacy" "$exim_conf"; }; then
	echo "[ + ] Fixing per-domain SMTP relay lookup for null-sender mail in Exim"

	cp -p "$exim_conf" "$exim_conf_bak"
	exim_conf_tmp="$(mktemp "${exim_conf}.XXXXXX")"

	# Exact whole-line match in awk (no regex/escaping issues); values go through
	# ENVIRON so backslashes and special characters are kept untouched.
	PAT1="$pattern_dsearch" PAT2="$pattern_legacy" NEW="$new_block" \
		awk '$0 == ENVIRON["PAT1"] || $0 == ENVIRON["PAT2"] { print ENVIRON["NEW"]; next } { print }' \
		"$exim_conf" > "$exim_conf_tmp"
	cat "$exim_conf_tmp" > "$exim_conf" # keep original owner/permissions
	rm -f "$exim_conf_tmp"

	if systemctl restart exim4; then
		echo "[ + ] Exim4 restarted successfully"
	else
		echo "[ ! ] Exim4 failed to restart, rolling back configuration"

		if [[ -f "$exim_conf_bak" ]]; then
			mv "$exim_conf_bak" "$exim_conf"

			if systemctl restart exim4; then
				echo "[ + ] Exim4 restarted successfully after rollback"
			else
				echo "[ ! ] Exim4 failed to restart even after rollback"
			fi
		else
			echo "[ ! ] Backup file not found, unable to rollback"
		fi
	fi
fi

# HTTP/3 (QUIC): set "reuseport" once per IP in /etc/nginx/conf.d/<ip>.conf instead of in
# the first domain config on that IP. Before, the option disappeared whenever that domain
# was deleted, suspended or moved to another template, and nginx workers then dropped
# QUIC packets meant for each other, so HTTP/3 page loads stalled.
if { [ "$WEB_SYSTEM" = "nginx" ] || [ "$PROXY_SYSTEM" = "nginx" ]; } \
	&& nginx -V 2>&1 | grep -q 'http_v3_module'; then
	echo "[ * ] Moving HTTP/3 (QUIC) reuseport option to the nginx IP configuration"
	# shellcheck source=/usr/local/hestia/func/domain.sh
	source $HESTIA/func/domain.sh

	# Remove the old per-template hooks that added reuseport to a single domain
	rm -f "$HESTIA"/data/templates/web/nginx/php-fpm/wordpress-http3.sh \
		"$HESTIA"/data/templates/web/nginx/php-fpm/wordpress-disable-xmlrpc-http3.sh \
		"$HESTIA"/data/templates/web/nginx/php-fpm/wordpress_mu_subdir-http3.sh

	quic_bak_dir="$(mktemp -d)"
	quic_changed=()
	while IFS= read -r ip; do
		ip_conf="/etc/nginx/conf.d/$ip.conf"
		[ -f "$ip_conf" ] || continue
		for conf in "$ip_conf" $(grep -lE "^\s*listen\s+${ip//./\\.}:[0-9]+\s+quic\s+reuseport\b" "$HOMEDIR"/*/conf/web/*/nginx.ssl.conf 2> /dev/null); do
			cp -p "$conf" "$quic_bak_dir/${#quic_changed[@]}"
			quic_changed+=("$conf")
		done
		process_quic_reuseport_ip "$ip_conf"
		for conf in "${quic_changed[@]}"; do
			[ "$conf" = "$ip_conf" ] || process_quic_reuseport_domain "$conf" "$ip"
		done
	done < <(ls "$HESTIA"/data/ips/ 2> /dev/null)

	if nginx -t > /dev/null 2>&1; then
		# A restart, not a reload, so nginx opens one UDP socket per worker
		systemctl restart nginx
	else
		echo "[ ! ] nginx configuration test failed, restoring previous HTTP/3 configuration"
		for i in "${!quic_changed[@]}"; do
			cp -p "$quic_bak_dir/$i" "${quic_changed[$i]}"
		done
		add_upgrade_message "HTTP/3: the QUIC reuseport option could not be moved to /etc/nginx/conf.d/<ip>.conf because the nginx configuration test failed. Your previous configuration was restored. Run 'nginx -t' for details."
	fi
	rm -rf "$quic_bak_dir"
	unset ip ip_conf conf quic_bak_dir quic_changed i
fi
