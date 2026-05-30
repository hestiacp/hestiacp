#!/usr/bin/env bash

# Shared Laravel Toolkit helpers.

laravel_conf_file() {
	echo "$USER_DATA/laravel.conf"
}

laravel_ensure_conf() {
	local conf
	conf="$(laravel_conf_file)"
	if [ ! -f "$conf" ]; then
		touch "$conf"
		chmod 660 "$conf"
	fi
}

laravel_validate_conf_value() {
	local value="$1"

	if [[ "$value" == *"'"* ]] || [[ "$value" == *$'\n'* ]] || [[ "$value" == *$'\r'* ]]; then
		check_result "$E_INVALID" "Laravel value contains invalid characters"
	fi
}

laravel_is_app_root() {
	local app_root="$1"

	[ -f "$app_root/artisan" ] \
		&& [ -f "$app_root/composer.json" ] \
		&& [ -f "$app_root/public/index.php" ]
}

laravel_default_app_root() {
	echo "$HOMEDIR/$user/web/$domain/public_html"
}

laravel_realpath_existing() {
	local path="$1"

	if [ ! -d "$path" ]; then
		check_result "$E_NOTEXIST" "Laravel application path does not exist: $path"
	fi

	(cd "$path" && pwd -P)
}

laravel_validate_app_root() {
	local app_root="$1"
	local domain_root="$HOMEDIR/$user/web/$domain"
	local real_app_root
	local real_domain_root

	real_app_root="$(laravel_realpath_existing "$app_root")"
	real_domain_root="$(laravel_realpath_existing "$domain_root")"

	if [[ "$real_app_root" != "$real_domain_root" && "$real_app_root" != "$real_domain_root/"* ]]; then
		check_result "$E_FORBIDEN" "Laravel application path must be inside $domain_root"
	fi

	echo "$real_app_root"
}

laravel_generate_secret() {
	LC_ALL=C head /dev/urandom | LC_ALL=C tr -dc 'A-Za-z0-9' | head -c 40
}

laravel_get_app_record() {
	local lookup_domain="$1"
	local conf

	laravel_ensure_conf
	conf="$(laravel_conf_file)"
	grep -F "DOMAIN='$lookup_domain'" "$conf" | head -n1
}

laravel_require_app() {
	local lookup_domain="$1"
	local record

	record="$(laravel_get_app_record "$lookup_domain")"
	if [ -z "$record" ]; then
		check_result "$E_NOTEXIST" "Laravel app for domain $lookup_domain doesn't exist"
	fi

	parse_object_kv_list "$record"
}

laravel_write_record() {
	local record="$1"
	local conf tmp

	laravel_ensure_conf
	conf="$(laravel_conf_file)"
	tmp="$(mktemp)"

	grep -vF "DOMAIN='$domain'" "$conf" > "$tmp" || true
	echo "$record" >> "$tmp"
	mv -f "$tmp" "$conf"
	chmod 660 "$conf"
}

laravel_add_app_record() {
	local record_domain="$1"
	local app_root="$2"
	local php_version="$3"
	local source_type="${4:-local}"
	local repo_url="${5:-}"
	local branch="${6:-}"
	local webhook_secret
	local record

	domain="$record_domain"

	laravel_validate_conf_value "$record_domain"
	laravel_validate_conf_value "$app_root"
	laravel_validate_conf_value "$php_version"
	laravel_validate_conf_value "$source_type"
	laravel_validate_conf_value "$repo_url"
	laravel_validate_conf_value "$branch"

	webhook_secret="$(laravel_generate_secret)"

	record="DOMAIN='$record_domain' APP_ROOT='$app_root' PHP_VERSION='$php_version'"
	record="$record SOURCE_TYPE='$source_type' REPO_URL='$repo_url' BRANCH='$branch'"
	record="$record SCHEDULER='no' QUEUE='no' QUEUE_CONNECTION='database'"
	record="$record QUEUE_TIMEOUT='60' QUEUE_MAX_JOBS='0' QUEUE_MAX_TIME='0'"
	record="$record QUEUE_STOP_WHEN_EMPTY='no' WEBHOOK_SECRET='$webhook_secret'"
	record="$record TIME='$(date +%T)' DATE='$(date +%F)'"

	laravel_write_record "$record"
}

laravel_delete_app_record() {
	local record_domain="$1"
	local conf tmp

	laravel_ensure_conf
	conf="$(laravel_conf_file)"
	tmp="$(mktemp)"

	grep -vF "DOMAIN='$record_domain'" "$conf" > "$tmp" || true
	mv -f "$tmp" "$conf"
	chmod 660 "$conf"
}

laravel_php_bin() {
	local php_version="$1"
	local php_bin="/usr/bin/php$php_version"

	if [ -x "$php_bin" ]; then
		echo "$php_bin"
		return
	fi

	if [ -x "/usr/bin/php" ]; then
		echo "/usr/bin/php"
		return
	fi

	check_result "$E_NOTEXIST" "PHP executable not found for Laravel app"
}

laravel_systemd_unit_name() {
	local safe_domain

	safe_domain="${domain//[^A-Za-z0-9]/-}"
	echo "hestia-laravel-$user-$safe_domain.service"
}
