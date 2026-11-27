#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.11

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

# Spread out Hestia automatic updates: only touch the job when it is active
# and still on the old fixed default time (41 4), so custom times are kept.
hestiaweb_cron="/var/spool/cron/crontabs/hestiaweb"
if [ -f "$hestiaweb_cron" ] && grep -qE '^41 4 \* \* \* sudo /usr/local/hestia/bin/v-update-sys-hestia-all' "$hestiaweb_cron"; then
	echo "[ * ] Spreading out Hestia automatic update schedule..."
	min=$(generate_password '012345' '2')
	hour=$(generate_password '123456' '1')
	sed -i -E "s|^41 4 (\* \* \* sudo /usr/local/hestia/bin/v-update-sys-hestia-all)|$min $hour \1|" "$hestiaweb_cron"
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

	quic_bak_dir="$(mktemp -d)"
	quic_changed=()
	quic_keep_hooks='no'
	for ip_conf in "$HESTIA"/data/ips/*; do
		ip="$(basename "$ip_conf")"
		ip_conf="/etc/nginx/conf.d/$ip.conf"
		[ -f "$ip_conf" ] || continue
		quic_first="${#quic_changed[@]}"
		for conf in "$ip_conf" $(grep -lsE "^\s*listen\s+${ip//./\\.}:[0-9]+\s+quic\s+reuseport\b" "$HOMEDIR"/*/conf/web/*/nginx.ssl.conf); do
			cp -p "$conf" "$quic_bak_dir/${#quic_changed[@]}"
			quic_changed+=("$conf")
			if [ "$conf" != "$ip_conf" ]; then
				sed -i -E "s/^(\s*listen\s+${ip//./\\.}:[0-9]+\s+quic)\s+reuseport\b/\1/" "$conf"
			fi
		done
		process_quic_reuseport_ip "$ip_conf"
		# The IP config did not get the option: leave it in the domain config
		if ! grep -qE '^\s*listen\s+\S+\s+quic\s+reuseport\b' "$ip_conf"; then
			for ((i = quic_first; i < ${#quic_changed[@]}; i++)); do
				cp -p "$quic_bak_dir/$i" "${quic_changed[$i]}"
			done
			# Keep the old hooks while no other nginx config sets it for this IP
			# (a custom file in /etc/nginx; domain configs are linked from conf.d/domains)
			if ! grep -rqsE --exclude-dir=domains "^\s*listen\s+${ip//./\\.}:[0-9]+\s+quic\s+reuseport\b" /etc/nginx/; then
				quic_keep_hooks='yes'
			fi
		fi
	done

	if nginx -t > /dev/null 2>&1; then
		# Remove the old per-template hooks that added reuseport to a single domain,
		# once every IP has the option outside the domain configs
		if [ "$quic_keep_hooks" = 'no' ]; then
			for conf in wordpress-http3 wordpress-disable-xmlrpc-http3 wordpress_mu_subdir-http3; do
				conf="$HESTIA/data/templates/web/nginx/php-fpm/$conf.sh"
				if grep -qs 'quic reuseport' "$conf"; then
					rm -f "$conf"
				fi
			done
		else
			echo "[ ! ] Not every IP got the HTTP/3 reuseport option, keeping the old template hooks"
		fi
		systemctl reload nginx
	else
		echo "[ ! ] nginx configuration test failed, restoring previous HTTP/3 configuration"
		for i in "${!quic_changed[@]}"; do
			cp -p "$quic_bak_dir/$i" "${quic_changed[$i]}"
		done
		add_upgrade_message "HTTP/3: the QUIC reuseport option could not be moved to /etc/nginx/conf.d/<ip>.conf because the nginx configuration test failed. Your previous configuration was restored. Run 'nginx -t' for details."
	fi
	rm -rf "$quic_bak_dir"
	unset ip ip_conf conf quic_bak_dir quic_changed quic_first quic_keep_hooks i
fi

# HTTP/3 (QUIC) runs over UDP, but the firewall only opened TCP 443. Servers that
# already serve HTTP/3 (for example with the wordpress-http3 templates) had their
# QUIC packets dropped, so browsers showed QUIC errors before falling back to
# HTTP/2. Open UDP 443 when nginx serves HTTP/3 on any domain.
if [ -n "$FIREWALL_SYSTEM" ] && [ "$FIREWALL_SYSTEM" != "no" ] \
	&& nginx -V 2>&1 | grep -q 'http_v3_module' \
	&& grep -lsE '^\s*listen\s+\S+\s+quic\b' "$HOMEDIR"/*/conf/web/*/nginx.ssl.conf* > /dev/null; then
	# shellcheck source=/usr/local/hestia/func/domain.sh
	source $HESTIA/func/domain.sh
	echo "[ * ] Opening UDP port 443 in the firewall for HTTP/3"
	add_web_http3_firewall_rule 443
fi
