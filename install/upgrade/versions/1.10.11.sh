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
