#!/bin/bash
# Internal listener transaction for an eligible dedicated-IP alias.

ip="$1"
target_user="$2"
domain="$3"

# shellcheck source=/etc/hestiacp/hestia.conf
source /etc/hestiacp/hestia.conf
# shellcheck source=/usr/local/hestia/func/main.sh
source "$HESTIA/func/main.sh"
# shellcheck source=/usr/local/hestia/func/ip.sh
source "$HESTIA/func/ip.sh"
source_conf "$HESTIA/conf/hestia.conf"

check_args '3' "$#" 'IP TARGET_USER DOMAIN'
is_format_valid 'ip'
check_hestia_demo_mode

# Require a local IP record, not its NAT spelling or a path.
record="$HESTIA/data/ips/$ip"
[ -f "$record" ] || check_result "$E_NOTEXIST" "IP $ip doesn't exist"
if [ "$PROXY_SYSTEM" != nginx ] && { [ -n "$PROXY_SYSTEM" ] || [ "$WEB_SYSTEM" != nginx ]; }; then
	check_result "$E_DISABLED" "Default HTTPS certificate selection requires an nginx frontend"
fi
conf="/etc/nginx/conf.d/$ip.conf"
[ -f "$conf" ] || check_result "$E_NOTEXIST" "Default nginx listener for $ip doesn't exist"

baseline="$HESTIA/data/ip-default-ssl/$ip.conf"
backend=''
site_conf=''
backend_baseline="$HESTIA/data/ip-default-ssl/$ip.backend.conf"
if [ "$PROXY_SYSTEM" = nginx ] && [[ "$WEB_SYSTEM" = apache2 || "$WEB_SYSTEM" = httpd ]]; then
	backend="/etc/$WEB_SYSTEM/conf.d/$ip.conf"
	[ -f "$backend" ] || check_result "$E_NOTEXIST" "Apache fallback listener is missing"
fi
if [ "$target_user" = none ] && [ "$domain" = none ]; then
	# Reset is idempotent, but never guess how to restore a missing backup.
	if [ -z "$(get_ip_default_ssl_value "$record" DEFAULT_SSL_DOMAIN)" ]; then
		exit 0
	fi
	[ -f "$baseline" ] || check_result "$E_NOTEXIST" "Original default certificate configuration is missing"
	if [ -n "$backend" ]; then
		[ -f "$backend_baseline" ] || check_result "$E_NOTEXIST" "Original Apache fallback configuration is missing"
		previous_user=$(get_ip_default_ssl_value "$record" DEFAULT_SSL_USER)
		previous_domain=$(get_ip_default_ssl_value "$record" DEFAULT_SSL_DOMAIN)
		site_conf="$HOMEDIR/$previous_user/conf/web/$previous_domain/nginx.ssl.conf"
		[ -f "$site_conf" ] || site_conf=''
	fi
	cert=$(awk '$1 == "ssl_certificate" {print $2}' "$baseline")
	key=$(awk '$1 == "ssl_certificate_key" {print $2}' "$baseline")
	target_user=''
	domain=''
else
	user="$target_user"
	USER_DATA="$HESTIA/data/users/$user"
	is_format_valid 'user' 'domain'
	is_object_valid 'user' 'USER' "$user"
	is_object_unsuspended 'user' 'USER' "$user"
	is_object_valid 'web' 'DOMAIN' "$domain"
	is_object_unsuspended 'web' 'DOMAIN' "$domain"
	is_object_value_exist 'web' 'DOMAIN' "$domain" '$SSL'
	site_ip=$(get_object_value 'web' 'DOMAIN' "$domain" '$IP')
	public_ip=$(get_ip_default_ssl_value "$record" NAT)
	public_ip=${public_ip:-$ip}
	if [ "$site_ip" != "$ip" ] && [ "$site_ip" != "$public_ip" ]; then
		check_result "$E_INVALID" "Selected site is not on IP $ip"
	fi
	if ! dedicated_ip_record "$ip" "$user" > /dev/null; then
		check_result "$E_FORBIDEN" "Selected site does not own dedicated IP $ip"
	fi
	aliases=$(get_object_value 'web' 'DOMAIN' "$domain" '$ALIAS')
	[[ ",$domain,$aliases," == *,"$public_ip",* ]]
	check_result $? "Selected site must use IP $public_ip as its name or alias"
	cert="$USER_DATA/ssl/$domain.pem;"
	key="$USER_DATA/ssl/$domain.key;"
	[ -s "${cert%;}" ] && [ -s "${key%;}" ]
	check_result $? "Selected certificate or private key is missing"
	certificate_covers_ip "${cert%;}" "$public_ip"
	check_result $? "Selected certificate must include IP SAN $public_ip"
	openssl x509 -in "${cert%;}" -noout -checkend 0 > /dev/null 2>&1
	check_result $? "Selected certificate has expired"
	if [ -n "$backend" ]; then
		domain_idn=$domain
		format_domain_idn
		site_conf="$HOMEDIR/$user/conf/web/$domain/nginx.ssl.conf"
		[ -f "$site_conf" ] || check_result "$E_NOTEXIST" "Selected nginx SSL site configuration is missing"
	fi
fi

# Only touch recognized, single-certificate fallback configurations. In
# particular, never edit other IPs or the global certificate.
if [ "$(grep -Ec '^[[:space:]]*ssl_certificate[[:space:]]' "$conf")" != 1 ] \
	|| [ "$(grep -Ec '^[[:space:]]*ssl_certificate_key[[:space:]]' "$conf")" != 1 ] \
	|| ! grep -Eq 'listen[[:space:]].*default_server.*ssl' "$conf"; then
	check_result "$E_INVALID" "Unsupported default nginx listener configuration"
fi
[[ "$cert" =~ ^/[a-zA-Z0-9_./-]+\;$ ]] && [[ "$key" =~ ^/[a-zA-Z0-9_./-]+\;$ ]]
check_result $? "Unsupported default certificate path"

# Root-private durable baseline; transient rollback files never contain keys.
mkdir -p "$HESTIA/data/ip-default-ssl" "$HESTIA/tmp" && chmod 700 "$HESTIA/data/ip-default-ssl"
check_result $? "Unable to prepare default HTTPS configuration storage"
transaction=$(mktemp -d "$HESTIA/tmp/ip-default-ssl.XXXXXXXX")
check_result $? "Unable to create default HTTPS transaction"
chmod 700 "$transaction"
recovery_file="$HESTIA/data/ip-default-ssl/$ip.recovery"
baseline_created=no
backend_baseline_created=no
# Until rollback backups are complete no live file has changed.
cleanup_default_ssl_setup() {
	local status=$?
	trap - EXIT HUP INT TERM
	[ "$baseline_created" != yes ] || rm -f "$baseline"
	[ "$backend_baseline_created" != yes ] || rm -f "$backend_baseline"
	rm -f "$recovery_file"
	rm -f "$transaction/nginx.conf" "$transaction/ip" "$transaction/backend.conf" "$transaction/site.conf"
	rmdir "$transaction"
	exit "$status"
}
trap cleanup_default_ssl_setup EXIT
trap 'exit 1' HUP INT TERM
cp -p "$conf" "$transaction/nginx.conf" && cp -p "$record" "$transaction/ip"
check_result $? "Unable to back up default HTTPS configuration"
if [ -n "$backend" ]; then
	cp -p "$backend" "$transaction/backend.conf" || exit 1
fi
if [ -n "$site_conf" ]; then
	cp -p "$site_conf" "$transaction/site.conf" || exit 1
fi
if [ ! -e "$baseline" ]; then
	baseline_created=yes
	cp -p "$conf" "$baseline" || exit 1
fi
if [ -n "$backend" ] && [ ! -e "$backend_baseline" ]; then
	backend_baseline_created=yes
	cp -p "$backend" "$backend_baseline" || exit 1
fi
# Reserve the recovery marker before changing live files, even if a later
# filesystem failure prevents writing anything else.
printf 'TRANSACTION=%s\nNGINX=%s\nIP_RECORD=%s\nBACKEND=%s\nSITE=%s\n' \
	"$transaction" "$conf" "$record" "$backend" "$site_conf" > "$recovery_file"
check_result $? "Unable to prepare default HTTPS recovery record"
committed=no
replace_default_ssl_file() {
	local staged="$2.ip-default-ssl.$$"
	# IP listing treats every non-hidden file in data/ips as an address. A
	# hidden sibling is ignored and stays on the same filesystem for rename.
	if [ "$2" = "$record" ]; then
		staged="${record%/*}/.ip-default-ssl.$$"
	fi
	cp -p "$2" "$staged" && cat "$1" > "$staged" && mv -f "$staged" "$2"
}
rollback_default_ssl() {
	local status=$? rollback_failed=no
	trap - EXIT HUP INT TERM
	if [ "$committed" != yes ]; then
		replace_default_ssl_file "$transaction/nginx.conf" "$conf" || rollback_failed=yes
		replace_default_ssl_file "$transaction/ip" "$record" || rollback_failed=yes
		if [ -n "$backend" ]; then
			replace_default_ssl_file "$transaction/backend.conf" "$backend" || rollback_failed=yes
		fi
		if [ -n "$site_conf" ]; then
			replace_default_ssl_file "$transaction/site.conf" "$site_conf" || rollback_failed=yes
		fi
		# Restore every file before testing/reloading either service.
		if [ -n "$backend" ]; then
			apachectl configtest > /dev/null 2>&1 && apachectl graceful > /dev/null 2>&1 || rollback_failed=yes
		fi
		nginx -t > /dev/null 2>&1 && nginx -s reload > /dev/null 2>&1 || rollback_failed=yes
		if [ "$rollback_failed" = yes ]; then
			# A daemon may still hold the new certificate. Retain all recovery
			# material and block lifecycle changes until an admin repairs it.
			echo "Manual recovery required for $ip; backups retained in $transaction and $recovery_file" >&2
			exit 1
		fi
		[ "$baseline_created" != yes ] || rm -f "$baseline"
		[ "$backend_baseline_created" != yes ] || rm -f "$backend_baseline"
	fi
	rm -f "$recovery_file"
	rm -f "$conf.ip-default-ssl.$$" "$record.ip-default-ssl.$$"
	rm -f "${record%/*}/.ip-default-ssl.$$"
	if [ -n "$backend" ]; then
		rm -f "$backend.ip-default-ssl.$$"
	fi
	rm -f "$transaction/nginx.conf" "$transaction/ip" "$transaction/new.conf" "$transaction/new.ip" \
		"$transaction/backend.conf" "$transaction/new.backend" "$transaction/site.conf" "$transaction/staged.ip"
	rmdir "$transaction"
	exit "$status"
}
trap rollback_default_ssl EXIT
trap 'exit 1' HUP INT TERM
if [ -z "$domain" ]; then
	awk '
		FNR == NR {
			if ($1 == "ssl_certificate") cert = $0
			if ($1 == "ssl_certificate_key") key = $0
			next
		}
		$1 == "ssl_certificate" {print cert; next}
		$1 == "ssl_certificate_key" {print key; next}
		{print}
	' "$baseline" "$conf" > "$transaction/new.conf" || exit 1
else
	awk -v cert="$cert" -v key="$key" '
		$1 == "ssl_certificate" {print "\tssl_certificate " cert; next}
		$1 == "ssl_certificate_key" {print "\tssl_certificate_key " key; next}
		{print}
	' "$conf" > "$transaction/new.conf" || exit 1
fi
replace_default_ssl_file "$transaction/new.conf" "$conf" || exit 1
if [ -n "$backend" ]; then
	# Only the TLS fallback's ServerName changes. It remains the first/default
	# vhost, but no longer steals the selected site's exact IP alias.
	original_backend_name=$(awk -v ip="$ip" -v port="$WEB_SSL_PORT" '
		$1 == "<VirtualHost" {tls = ($2 == ip ":" port ">")}
		tls && $1 == "ServerName" {print $2}
	' "$backend_baseline")
	[ "$original_backend_name" = "$ip" ]
	check_result $? "Unsupported Apache TLS fallback ServerName"
	original_backend_line=$(awk -v ip="$ip" -v port="$WEB_SSL_PORT" '
		$1 == "<VirtualHost" {tls = ($2 == ip ":" port ">")}
		tls && $1 == "ServerName" {print}
	' "$backend_baseline")
	backend_name="ip-default-${ip//./-}.invalid"
	[ -n "$domain" ] || backend_name="$original_backend_name"
	awk -v ip="$ip" -v port="$WEB_SSL_PORT" -v name="$backend_name" \
		-v reset="${domain:+no}" -v original="$original_backend_line" '
		$1 == "<VirtualHost" {tls = ($2 == ip ":" port ">")}
		tls && $1 == "ServerName" && reset == "" {print original; next}
		tls && $1 == "ServerName" {sub(/ServerName[[:space:]]+.*/, "ServerName " name)}
		{print}
	' "$backend" > "$transaction/new.backend" || exit 1
	replace_default_ssl_file "$transaction/new.backend" "$backend" || exit 1
	if [ -n "$site_conf" ]; then
		if [ -z "$domain" ]; then
			sed -i '/; # hestia-ip-https$/s/\(proxy_ssl_name[[:space:]]\+\).*; # hestia-ip-https$/\1$host;/' "$site_conf"
		else
			prepare_ip_alias_backend_sni "$site_conf" "$domain_idn" "$aliases"
		fi
		check_result $? "Unable to prepare backend TLS SNI"
	fi
	apachectl configtest
	check_result $? "Apache configuration test failed; default HTTPS selection rolled back"
fi
nginx -t
check_result $? "Nginx configuration test failed; default HTTPS selection rolled back"
if [ -n "$backend" ]; then
	apachectl graceful
	check_result $? "Apache reload failed; default HTTPS selection rolled back"
fi
nginx -s reload
check_result $? "Nginx reload failed; default HTTPS selection rolled back"

sed '/^DEFAULT_SSL_USER=/d; /^DEFAULT_SSL_DOMAIN=/d' "$record" > "$transaction/new.ip" || exit 1
printf "DEFAULT_SSL_USER='%s'\nDEFAULT_SSL_DOMAIN='%s'\n" "$target_user" "$domain" >> "$transaction/new.ip" || exit 1
replace_default_ssl_file "$transaction/new.ip" "$record" || exit 1
committed=yes
if [ -z "$domain" ]; then
	rm -f "$baseline"
	rm -f "$backend_baseline"
fi
"$BIN/v-log-action" "system" "Info" "System" "Default HTTPS certificate changed (IP: $ip, User: $target_user, Domain: $domain)."
log_event "$OK" "$ARGUMENTS"
exit 0
