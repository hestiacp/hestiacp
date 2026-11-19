#!/bin/bash

# Serialize the default-certificate selection with operations that can remove
# its site or replace its certificate. Children inherit the locked descriptor:
# checking the inode and flock avoids trusting an environment flag.
lock_ip_default_ssl() {
	local lock="$HESTIA/data/queue/ip-default-ssl.lock"
	if ! { [ "$lock" -ef /proc/self/fd/198 ] && flock -n 198; }; then
		exec 198> "$lock" || exit 1
		flock -x 198 || exit 1
	fi
	local recovery
	for recovery in "$HESTIA"/data/ip-default-ssl/*.recovery; do
		[ -e "$recovery" ] || continue
		echo "Manual recovery required; inspect $recovery before changing TLS dependencies" >&2
		exit 1
	done
}

# Read just one quoted field, without evaluating IP metadata as shell code.
get_ip_default_ssl_value() {
	sed -n "s/^$2='\([^']*\)'$/\1/p" "$1"
}

# A web domain can store either a local address or its public NAT address.
dedicated_ip_record() {
	local record="$HESTIA/data/ips/$1"
	if [ ! -f "$record" ]; then
		record=$(grep -lFx "NAT='$1'" "$HESTIA"/data/ips/* 2> /dev/null) || return 1
	fi
	[ -f "$record" ] && grep -Fxq "STATUS='dedicated'" "$record" && grep -Fxq "OWNER='$2'" "$record" && echo "$record"
}

certificate_covers_ip() {
	openssl x509 -in "$1" -noout -checkip "$2" 2> /dev/null | grep -Fxq "IP $2 does match certificate"
}

is_ipv4_web_name() {
	[[ "$1" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]
}

check_primary_web_ip() {
	local record public
	is_ip_format_valid "$1"
	is_ip_format_valid "$2"
	record=$(dedicated_ip_record "$2" "$3") || check_result "$E_FORBIDEN" "An IP website requires a dedicated address owned by $3"
	public=$(get_ip_default_ssl_value "$record" NAT)
	[ "$1" = "${public:-${record##*/}}" ] || check_result "$E_INVALID" "Website address must match the dedicated public IP"
}

check_no_primary_web_ip() {
	local record="$HESTIA/data/ips/$1" public owner
	owner=$(get_ip_default_ssl_value "$record" OWNER)
	public=$(get_ip_default_ssl_value "$record" NAT)
	if grep -Fq "DOMAIN='${public:-$1}'" "$HESTIA/data/users/$owner/web.conf" 2> /dev/null; then
		check_result "$E_INUSE" "Remove the IP website before changing its address or assignment"
	fi
}

# Apache's first HTTP vhost already names the local IP. Free just that
# ServerName while a primary-IP site exists; NAT sites use a different Host.
primary_ip_http_fallback() {
	local record from to conf="/etc/$WEB_SYSTEM/conf.d/$domain.conf"
	record=$(dedicated_ip_record "$domain" "$user") || return 0
	[ "${record##*/}" = "$domain" ] && [ -f "$conf" ] || return 0
	from=$domain
	to="ip-default-http-${domain//./-}.invalid"
	if [ "$1" = disable ]; then
		from=$to
		to=$domain
	fi
	sed -i -E "/^[[:space:]]*<VirtualHost ${domain//./\\.}:$WEB_PORT>/,/<\\/VirtualHost>/s/^([[:space:]]*ServerName[[:space:]]+)${from//./\\.}([[:space:]]*)$/\\1$to\\2/" "$conf"
	check_result $? "Unable to update Apache HTTP listener for $domain"
}

# Called after the alias, certificate, or site configuration has been saved.
# A DNS-only certificate is normal while the IP certificate is being issued.
sync_dedicated_ip_https() (
	[ "${PROXY_SYSTEM:-$WEB_SYSTEM}" = nginx ] || return 0
	get_domain_values web
	[ "$SSL" = yes ] && [ "$SUSPENDED" = no ] || return 0
	[ -z "$PROXY_SYSTEM" ] || [ -n "$PROXY" ] || return 0
	[[ "$domain,$ALIAS" =~ (^|,)[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+(,|$) ]] || return 0
	local record public pem
	record=$(dedicated_ip_record "$IP" "$user") || return 0
	[ -z "${1-}" ] || [ "$record" = "$HESTIA/data/ips/$1" ] || return 0
	public=$(get_ip_default_ssl_value "$record" NAT)
	public=${public:-${record##*/}}
	[[ ",$domain,$ALIAS," == *,"$public",* ]] || return 0
	grep -Fxq "DEFAULT_SSL_DOMAIN='$domain'" "$record" && grep -Fxq "DEFAULT_SSL_USER='$user'" "$record" && return 0
	pem="$USER_DATA/ssl/$domain.pem"
	certificate_covers_ip "$pem" "$public" || return 0
	openssl x509 -in "$pem" -noout -checkend 0 > /dev/null 2>&1 || return 0
	bash "$HESTIA/func/apply-ip-https.sh" "${record##*/}" "$user" "$domain"
)

# The site may already have its alias and certificate when an admin assigns the IP.
sync_dedicated_ip_owner_sites() (
	local record="$HESTIA/data/ips/$1"
	grep -Fxq "STATUS='dedicated'" "$record" || return 0
	local user domain USER_DATA
	user=$(get_ip_default_ssl_value "$record" OWNER)
	USER_DATA="$HESTIA/data/users/$user"
	[ -f "$USER_DATA/web.conf" ] && [ "$(get_user_value '$SUSPENDED')" = no ] || return 0
	for domain in $(search_objects web SSL yes DOMAIN); do
		sync_dedicated_ip_https "$1" || return $?
	done
)

# A lifecycle operation must restore and reload the original fallback before
# removing anything nginx may still need. Do not defer this reload for RESTART=no.
release_ip_default_ssl() {
	local target_user="$1" target_domain="${2-}" target_alias="${3-}"
	local record selected_user selected_domain public_ip
	for record in "$HESTIA"/data/ips/*; do
		[ -f "$record" ] || continue
		selected_user=$(get_ip_default_ssl_value "$record" DEFAULT_SSL_USER)
		selected_domain=$(get_ip_default_ssl_value "$record" DEFAULT_SSL_DOMAIN)
		[ "$selected_user" = "$target_user" ] || continue
		[ -z "$target_domain" ] || [ "$selected_domain" = "$target_domain" ] || continue
		if [ -n "$target_alias" ]; then
			public_ip=$(get_ip_default_ssl_value "$record" NAT)
			public_ip=${public_ip:-${record##*/}}
			[ "$target_alias" = "$public_ip" ] || continue
		fi
		bash "$HESTIA/func/apply-ip-https.sh" "${record##*/}" none none
		check_result $? "Unable to release default HTTPS certificate for ${record##*/}"
	done
}

release_ip_default_ssl_address() {
	local record="$HESTIA/data/ips/$1"
	if [ -n "$(get_ip_default_ssl_value "$record" DEFAULT_SSL_DOMAIN)" ]; then
		bash "$HESTIA/func/apply-ip-https.sh" "$1" none none
		check_result $? "Unable to release default HTTPS certificate for $1"
	fi
}

# A manually uploaded certificate must not silently break bare-IP HTTPS.
check_ip_default_ssl_certificate() {
	local record public_ip
	for record in "$HESTIA"/data/ips/*; do
		[ -f "$record" ] || continue
		[ "$(get_ip_default_ssl_value "$record" DEFAULT_SSL_USER)" = "$user" ] || continue
		[ "$(get_ip_default_ssl_value "$record" DEFAULT_SSL_DOMAIN)" = "$domain" ] || continue
		public_ip=$(get_ip_default_ssl_value "$record" NAT)
		public_ip=${public_ip:-${record##*/}}
		certificate_covers_ip "$ssl_dir/$domain.crt" "$public_ip"
		check_result $? "Default HTTPS certificate must include IP SAN $public_ip"
		openssl x509 -in "$ssl_dir/$domain.crt" -noout -checkend 0 > /dev/null 2>&1
		check_result $? "Default HTTPS certificate has expired"
	done
}

# Apache's IP fallback competes with an IP alias, and numeric IPs are not valid
# TLS SNI hostnames. Use the site's canonical DNS name for backend TLS only;
# preserve the original HTTP Host and all inherited proxy headers.
prepare_ip_alias_backend_sni() {
	[ "$PROXY_SYSTEM" = nginx ] || return 0
	[[ "$WEB_SYSTEM" = apache2 || "$WEB_SYSTEM" = httpd ]] || return 0
	local alias name="$2"
	if is_ipv4_web_name "$name"; then
		name="ip-site-${name//./-}.invalid"
	fi
	for alias in "$2" ${3//,/ }; do
		if [[ "$alias" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
			sed -i "s|\(proxy_ssl_name[[:space:]]\+\)\$host;|\1$name; # hestia-ip-https|g" "$1"
			return $?
		fi
	done
}

case "$SCRIPT" in
	apply-ip-https.sh | v-add-web-domain | v-delete-web-domain | v-delete-web-domain-ssl | \
		v-suspend-web-domain | v-delete-web-domain-alias | v-change-web-domain-ip | \
		v-update-web-domain-ssl | v-add-web-domain-ssl | v-rebuild-web-domain | \
		v-change-web-domain-name | v-change-web-domain-sslcert | \
		v-delete-user | v-suspend-user | v-change-sys-ip-owner | \
		v-change-sys-ip-nat | v-change-sys-ip-status | v-update-sys-ip | \
		v-add-mail-domain-ssl | v-update-mail-domain-ssl | \
		v-change-mail-domain-sslcert | v-delete-mail-domain-ssl | \
		v-delete-mail-domain | v-delete-sys-ip | v-change-domain-owner | \
		v-restore-user | v-restore-web-domain-restic | v-restore-mail-domain-restic | \
		v-add-web-domain-alias | v-change-web-domain-tpl | \
		v-change-web-domain-proxy-tpl | v-change-web-domain-backend-tpl | \
		v-add-web-domain-proxy | v-delete-web-domain-proxy | v-unsuspend-web-domain)
		lock_ip_default_ssl
		;;
esac
