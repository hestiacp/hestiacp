#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - Web Domain Application Function Library           #
#                                                                           #
#===========================================================================#

# A web domain application is a long-running process (Node.js, Python or any
# custom command) that belongs to a web domain. Every application:
#   - is stored as one line in $USER_DATA/app.conf
#   - runs as a systemd service under the domain owner (never as root)
#   - listens on a private port on 127.0.0.1 that Hestia allocates ($PORT)
#   - is published by the "app" nginx template, which proxies the domain to it
#   - gets its environment from $USER_DATA/app/$domain.env (EnvironmentFile)

APP_RUNTIMES='node python custom'
APP_NODE_DIR='/opt/hestia/nodejs'
APP_TEMPLATE='app'
APP_RESERVED_DIRS='public_html public_shtml document_errors cgi-bin stats logs private'
APP_RESERVED_ENV='PORT'
APP_MAX_ENV_SIZE='65536'
# shellcheck disable=SC2034 # used by v-update-web-domain-app-deps
APP_INSTALL_TIMEOUT='1800'

app_port_min() { echo "${APP_PORT_MIN:-30000}"; }
app_port_max() { echo "${APP_PORT_MAX:-39999}"; }

# Service, slice and file names
# Usernames may contain "-" and "_" but domains never contain "_", so the last
# "_" in the unit name always separates user and domain.
app_unit_name() {
	echo "hestia-app-${1}_${2}.service"
}

app_unit_file() {
	echo "/etc/systemd/system/$(app_unit_name "$1" "$2")"
}

app_slice_name() {
	echo "hestia-app-$(id -u "$1").slice"
}

app_env_file() {
	echo "$HESTIA/data/users/$1/app/$2.env"
}

# Absolute application directory for $user/$domain/$DIR
app_dir_path() {
	echo "$HOMEDIR/$1/web/$2/$3"
}

#----------------------------------------------------------#
#                    Validation                            #
#----------------------------------------------------------#

is_app_runtime_valid() {
	if [[ " $APP_RUNTIMES " != *" $1 "* ]]; then
		check_result "$E_INVALID" "invalid runtime :: $1 (supported: $APP_RUNTIMES)"
	fi
}

is_app_version_valid() {
	local runtime="$1" version="$2"
	if [ "$version" = 'system' ]; then
		if [ "$runtime" = 'node' ] && [ ! -x /usr/bin/node ]; then
			check_result "$E_NOTEXIST" "system Node.js is not installed (/usr/bin/node)"
		fi
		if [ "$runtime" = 'python' ] && [ ! -x /usr/bin/python3 ]; then
			check_result "$E_NOTEXIST" "system Python is not installed (/usr/bin/python3)"
		fi
		return 0
	fi
	if [ "$runtime" != 'node' ]; then
		check_result "$E_INVALID" "runtime $runtime only supports version 'system'"
	fi
	if ! [[ "$version" =~ ^[0-9]{1,3}$ ]]; then
		check_result "$E_INVALID" "invalid version format :: $version"
	fi
	if [ ! -x "$APP_NODE_DIR/$version/bin/node" ]; then
		check_result "$E_NOTEXIST" "Node.js $version is not installed (v-add-sys-nodejs $version)"
	fi
}

# Start and install commands end up in a systemd unit, so they must stay on
# one line. Everything else is fine: they run as the domain owner.
is_app_command_valid() {
	if [[ "$1" =~ [[:cntrl:]] ]]; then
		check_result "$E_INVALID" "invalid $2 :: control characters are not allowed"
	fi
	if [ "${#1}" -gt 1024 ]; then
		check_result "$E_INVALID" "invalid $2 :: longer than 1024 characters"
	fi
}

is_app_dir_valid() {
	local dir="$1" top
	if [ -z "$dir" ] || [[ "$dir" == /* ]]; then
		check_result "$E_INVALID" "invalid app directory :: must be relative to the domain folder"
	fi
	is_dir_path_format_valid "$dir" 'app directory'
	top="${dir%%/*}"
	if [[ " $APP_RESERVED_DIRS " == *" $top "* ]]; then
		check_result "$E_INVALID" "invalid app directory :: $top is reserved by Hestia"
	fi
}

is_app_valid() {
	if [ ! -f "$USER_DATA/app.conf" ] || ! grep -q "^DOMAIN='$1' " "$USER_DATA/app.conf"; then
		check_result "$E_NOTEXIST" "app for domain $1 doesn't exist"
	fi
}

is_app_new() {
	if [ -f "$USER_DATA/app.conf" ] && grep -q "^DOMAIN='$1' " "$USER_DATA/app.conf"; then
		check_result "$E_EXISTS" "app for domain $1 already exists"
	fi
}

app_exists() {
	[ -f "$HESTIA/data/users/$1/app.conf" ] \
		&& grep -q "^DOMAIN='$2' " "$HESTIA/data/users/$1/app.conf"
}

# Check whether the server can host applications behind nginx
is_app_system_supported() {
	if [ "$PROXY_SYSTEM" != 'nginx' ] && [ "$WEB_SYSTEM" != 'nginx' ]; then
		check_result "$E_NOTEXIST" "applications require nginx as web or proxy server"
	fi
}

#----------------------------------------------------------#
#                    Configuration                         #
#----------------------------------------------------------#

# Load app values into RUNTIME VERSION DIR CMD INSTALL PORT STATUS ...
get_app_values() {
	parse_object_kv_list "$(grep "^DOMAIN='$1' " "$USER_DATA/app.conf")"
	CMD="${CMD//%quote%/\'}"
	INSTALL="${INSTALL//%quote%/\'}"
}

# Encode a value for app.conf (single quotes would break the key/value format)
app_conf_encode() {
	local value="${1//\'/%quote%}"
	printf '%s' "$value"
}

update_app_value() {
	update_object_value 'app' 'DOMAIN' "$1" "$2" "$(app_conf_encode "$3")"
}

# Allocate the first free port in the APP_PORT_MIN-APP_PORT_MAX range.
# Callers must hold the port lock (see app_lock).
get_free_app_port() {
	local used listening port
	used=" $(grep -ho "PORT='[0-9]*'" "$HESTIA"/data/users/*/app.conf 2> /dev/null \
		| tr -dc '0-9\n' | tr '\n' ' ') "
	listening=" $(ss -Htln 2> /dev/null | awk '{print $4}' | sed 's/.*://' | sort -u | tr '\n' ' ') "
	for ((port = $(app_port_min); port <= $(app_port_max); port++)); do
		if [[ "$used" != *" $port "* ]] && [[ "$listening" != *" $port "* ]]; then
			echo "$port"
			return 0
		fi
	done
	return 1
}

app_lock() {
	mkdir -p "$HESTIA/data/queue"
	exec {APP_LOCK_FD}> "$HESTIA/data/queue/app.lock"
	flock -w 60 "$APP_LOCK_FD" || check_result "$E_DISK" "unable to lock app configuration"
}

#----------------------------------------------------------#
#                    Environment                           #
#----------------------------------------------------------#

# Escape a value for a double quoted string in a systemd EnvironmentFile.
# systemd only treats \ " ` and $ as escapable inside double quotes and does
# not expand variables, so this round-trips exactly.
app_env_escape() {
	local s="$1"
	s="${s//\\/\\\\}"
	s="${s//\"/\\\"}"
	s="${s//\`/\\\`}"
	s="${s//\$/\\\$}"
	printf '%s' "$s"
}

app_env_unescape() {
	printf '%s' "$1" | sed -E 's/\\(["\\`$])/\1/g'
}

# Parse dotenv text from file $1 and write the systemd EnvironmentFile $2.
# Accepts blank lines, # comments, optional "export " and quoted values.
write_app_env() {
	local src="$1" dst="$2" line key value count=0 tmp

	if [ "$(stat -c %s "$src")" -gt "$APP_MAX_ENV_SIZE" ]; then
		check_result "$E_INVALID" "environment file is larger than $APP_MAX_ENV_SIZE bytes"
	fi

	tmp=$(mktemp "${dst}.XXXXXX")
	chmod 600 "$tmp"
	while IFS= read -r line || [ -n "$line" ]; do
		line="${line%$'\r'}"
		# Trim leading whitespace
		line="${line#"${line%%[![:space:]]*}"}"
		[ -z "$line" ] && continue
		[[ "$line" == \#* ]] && continue
		line="${line#export }"
		if ! [[ "$line" =~ ^([A-Za-z_][A-Za-z0-9_]{0,127})[[:space:]]*=[[:space:]]*(.*)$ ]]; then
			rm -f "$tmp"
			check_result "$E_INVALID" "invalid environment line :: ${line:0:60}"
		fi
		key="${BASH_REMATCH[1]}"
		value="${BASH_REMATCH[2]}"
		if [[ " $APP_RESERVED_ENV " == *" $key "* ]]; then
			rm -f "$tmp"
			check_result "$E_INVALID" "$key is managed by Hestia and can't be set"
		fi
		# Strip matching surrounding quotes, otherwise trailing whitespace
		if [[ "$value" =~ ^\"(.*)\"$ ]] || [[ "$value" =~ ^\'(.*)\'$ ]]; then
			value="${BASH_REMATCH[1]}"
		else
			value="${value%"${value##*[![:space:]]}"}"
		fi
		if [[ "$value" =~ [[:cntrl:]] ]]; then
			rm -f "$tmp"
			check_result "$E_INVALID" "invalid value for $key :: control characters are not allowed"
		fi
		printf '%s="%s"\n' "$key" "$(app_env_escape "$value")" >> "$tmp"
		count=$((count + 1))
	done < "$src"

	mv -f "$tmp" "$dst"
	chown root:root "$dst"
	chmod 600 "$dst"
}

# Print the stored environment as KEY=VALUE lines
print_app_env() {
	local file="$1" line key value
	[ -f "$file" ] || return 0
	while IFS= read -r line; do
		[[ "$line" =~ ^([A-Za-z_][A-Za-z0-9_]*)=\"(.*)\"$ ]] || continue
		key="${BASH_REMATCH[1]}"
		value=$(app_env_unescape "${BASH_REMATCH[2]}")
		# Quote values the parser would otherwise trim or unquote, so the output
		# can be saved again unchanged (only the outer quotes are stripped)
		if [[ "$value" =~ ^[[:space:]] ]] || [[ "$value" =~ [[:space:]]$ ]] \
			|| [[ "$value" =~ ^[\"\'] ]]; then
			printf '%s="%s"\n' "$key" "$value"
		else
			printf '%s=%s\n' "$key" "$value"
		fi
	done < "$file"
}

#----------------------------------------------------------#
#                    systemd units                         #
#----------------------------------------------------------#

# Escape a string for a double quoted systemd unit argument
app_systemd_escape() {
	local s="$1"
	s="${s//\\/\\\\}"
	s="${s//\"/\\\"}"
	s="${s//%/%%}"
	s="${s//\$/\$\$}"
	printf '%s' "$s"
}

app_default_cmd() {
	case "$1" in
		node) echo 'npm start' ;;
		*) echo '' ;;
	esac
}

# PATH for the application process and its install command
app_path() {
	local user="$1" runtime="$2" version="$3" appdir="$4"
	local path="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
	case "$runtime" in
		node)
			path="$appdir/node_modules/.bin:$path"
			if [ "$version" != 'system' ]; then
				path="$APP_NODE_DIR/$version/bin:$path"
			fi
			;;
		python)
			path="$appdir/.venv/bin:$path"
			;;
	esac
	echo "$HOMEDIR/$user/.local/bin:$path"
}

# Write the per user slice that carries the package resource limits
write_app_slice() {
	local user="$1" slice file limits=''
	slice=$(app_slice_name "$user")
	file="/etc/systemd/system/$slice"

	if [ "$RESOURCES_LIMIT" = 'yes' ]; then
		local cpu_quota cpu_quota_period memory_limit swap_limit
		cpu_quota=$(grep -m1 "^CPU_QUOTA=" "$HESTIA/data/users/$user/user.conf" | cut -f 2 -d \')
		cpu_quota_period=$(grep -m1 "^CPU_QUOTA_PERIOD=" "$HESTIA/data/users/$user/user.conf" | cut -f 2 -d \')
		memory_limit=$(grep -m1 "^MEMORY_LIMIT=" "$HESTIA/data/users/$user/user.conf" | cut -f 2 -d \')
		swap_limit=$(grep -m1 "^SWAP_LIMIT=" "$HESTIA/data/users/$user/user.conf" | cut -f 2 -d \')
		[ -n "$cpu_quota" ] && [ "$cpu_quota" != 'unlimited' ] && limits+="CPUQuota=$cpu_quota"$'\n'
		[ -n "$cpu_quota_period" ] && [ "$cpu_quota_period" != 'unlimited' ] \
			&& limits+="CPUQuotaPeriodSec=$cpu_quota_period"$'\n'
		[ -n "$memory_limit" ] && [ "$memory_limit" != 'unlimited' ] && limits+="MemoryHigh=$memory_limit"$'\n'
		[ -n "$swap_limit" ] && [ "$swap_limit" != 'unlimited' ] && limits+="MemorySwapMax=$swap_limit"$'\n'
	fi

	cat > "$file.tmp" <<- EOF
		# Generated by Hestia Control Panel, do not edit
		[Unit]
		Description=Hestia applications of user $user

		[Slice]
		CPUAccounting=yes
		MemoryAccounting=yes
		${limits}
	EOF
	app_replace_if_changed "$file.tmp" "$file"
}

# Move $1 over $2 when it differs. Returns 0 when $2 changed.
app_replace_if_changed() {
	if [ -f "$2" ] && cmp -s "$1" "$2"; then
		rm -f "$1"
		return 1
	fi
	mv -f "$1" "$2"
	chmod 644 "$2"
	return 0
}

# Write the service unit for $user/$domain from app.conf.
# Returns 0 when the unit changed (a restart is needed to apply it).
write_app_unit() {
	local user="$1" domain="$2"
	local unit file envfile appdir cmd path extra_env=''
	unit=$(app_unit_name "$user" "$domain")
	file=$(app_unit_file "$user" "$domain")
	envfile=$(app_env_file "$user" "$domain")

	get_app_values "$domain"
	appdir=$(app_dir_path "$user" "$domain" "$DIR")
	cmd="$CMD"
	[ -z "$cmd" ] && cmd=$(app_default_cmd "$RUNTIME")
	path=$(app_path "$user" "$RUNTIME" "$VERSION" "$appdir")

	case "$RUNTIME" in
		node) extra_env='Environment="NODE_ENV=production"' ;;
		python)
			extra_env="Environment=\"VIRTUAL_ENV=$(app_systemd_escape "$appdir/.venv")\""$'\n'
			extra_env+='Environment="PYTHONUNBUFFERED=1"'
			;;
	esac

	write_app_slice "$user"

	cat > "$file.tmp" <<- EOF
		# Generated by Hestia Control Panel, do not edit
		# Manage with: v-start-web-domain-app / v-stop-web-domain-app $user $domain
		[Unit]
		Description=Hestia app $domain (user $user)
		After=network-online.target
		Wants=network-online.target
		StartLimitIntervalSec=300
		StartLimitBurst=10

		[Service]
		Type=simple
		User=$user
		Group=$user
		Slice=$(app_slice_name "$user")
		WorkingDirectory=$(app_systemd_escape "$appdir")
		Environment="PATH=$(app_systemd_escape "$path")"
		Environment="HOST=127.0.0.1"
		${extra_env}
		EnvironmentFile=-$envfile
		Environment="PORT=$PORT"
		ExecStart=/bin/bash -c "$(app_systemd_escape "$cmd")"
		Restart=always
		RestartSec=3
		TimeoutStopSec=20
		SyslogIdentifier=hestia-app-$domain
		UMask=0027
		CPUAccounting=yes
		MemoryAccounting=yes
		NoNewPrivileges=yes
		PrivateTmp=yes
		PrivateDevices=yes
		ProtectSystem=full
		ProtectKernelTunables=yes
		ProtectKernelModules=yes
		ProtectControlGroups=yes
		RestrictSUIDSGID=yes
		LockPersonality=yes

		[Install]
		WantedBy=multi-user.target
	EOF
	app_replace_if_changed "$file.tmp" "$file"
}

delete_app_unit() {
	local user="$1" domain="$2" unit
	unit=$(app_unit_name "$user" "$domain")
	systemctl disable --now "$unit" > /dev/null 2>&1
	rm -f "$(app_unit_file "$user" "$domain")"
	systemctl reset-failed "$unit" > /dev/null 2>&1
	# Drop the user slice once the last app is gone
	if [ ! -s "$HESTIA/data/users/$user/app.conf" ] \
		|| [ "$(grep -c "^DOMAIN=" "$HESTIA/data/users/$user/app.conf")" -le 1 ]; then
		rm -f "/etc/systemd/system/$(app_slice_name "$user")"
	fi
	systemctl daemon-reload
}

start_app_service() {
	local unit
	unit=$(app_unit_name "$1" "$2")
	systemctl reset-failed "$unit" > /dev/null 2>&1
	systemctl enable "$unit" > /dev/null 2>&1
	systemctl restart "$unit"
}

stop_app_service() {
	local unit
	unit=$(app_unit_name "$1" "$2")
	systemctl disable --now "$unit" > /dev/null 2>&1
	systemctl reset-failed "$unit" > /dev/null 2>&1
	return 0
}

# Regenerate the unit and bring the service to its desired state.
# Used by add/change and by domain rebuilds.
sync_app_service() {
	local user="$1" domain="$2" force_restart="${3:-no}" changed=no unit web_suspended
	unit=$(app_unit_name "$user" "$domain")
	if write_app_unit "$user" "$domain"; then
		changed=yes
	fi
	systemctl daemon-reload

	web_suspended=$(grep "DOMAIN='$domain'" "$HESTIA/data/users/$user/web.conf" \
		| grep -o "SUSPENDED='[a-z]*'" | cut -f 2 -d \')
	get_app_values "$domain"
	if [ "$STATUS" = 'running' ] && [ "$web_suspended" != 'yes' ]; then
		if [ "$changed" = 'yes' ] || [ "$force_restart" = 'yes' ] || ! systemctl is-active -q "$unit"; then
			start_app_service "$user" "$domain"
		fi
	else
		stop_app_service "$user" "$domain"
	fi
}

#----------------------------------------------------------#
#                    Status                                #
#----------------------------------------------------------#

# Read the live state of a unit into APP_STATE APP_SUBSTATE APP_PID APP_UPTIME
# APP_RESTARTS APP_MEMORY APP_CPU_NSEC
get_app_state() {
	local unit key value since now
	unit=$(app_unit_name "$1" "$2")
	APP_STATE='inactive'
	APP_SUBSTATE='dead'
	APP_PID='0'
	APP_UPTIME='0'
	APP_RESTARTS='0'
	APP_MEMORY='0'
	APP_CPU_NSEC='0'
	since=''
	while IFS='=' read -r key value; do
		case "$key" in
			ActiveState) APP_STATE="$value" ;;
			SubState) APP_SUBSTATE="$value" ;;
			MainPID) APP_PID="$value" ;;
			NRestarts) APP_RESTARTS="$value" ;;
			MemoryCurrent) [[ "$value" =~ ^[0-9]+$ ]] && APP_MEMORY="$value" ;;
			CPUUsageNSec) [[ "$value" =~ ^[0-9]+$ ]] && APP_CPU_NSEC="$value" ;;
			ActiveEnterTimestampMonotonic) since="$value" ;;
		esac
	done < <(systemctl show "$unit" -p ActiveState -p SubState -p MainPID -p NRestarts \
		-p MemoryCurrent -p CPUUsageNSec -p ActiveEnterTimestampMonotonic 2> /dev/null)

	if [ "$APP_STATE" = 'active' ] && [[ "$since" =~ ^[0-9]+$ ]] && [ "$since" -gt 0 ]; then
		now=$(awk '{printf "%d", $1 * 1000000}' /proc/uptime)
		APP_UPTIME=$(((now - since) / 1000000))
	fi
	[[ "$APP_RESTARTS" =~ ^[0-9]+$ ]] || APP_RESTARTS='0'
	[[ "$APP_PID" =~ ^[0-9]+$ ]] || APP_PID='0'
}

# CPU usage in percent of one core between two CPUUsageNSec samples taken
# $3 milliseconds apart
app_cpu_percent() {
	awk -v a="$1" -v b="$2" -v t="$3" 'BEGIN { if (t <= 0 || b < a) { print "0.0" } else { printf "%.1f", (b - a) / (t * 10000) } }'
}

#----------------------------------------------------------#
#                    nginx templates                       #
#----------------------------------------------------------#

# Copy the app templates into the active template folder when missing
# (servers that were upgraded rather than installed with this version)
app_ensure_templates() {
	local src dst
	for dst in "$WEBTPL/nginx" "$WEBTPL/nginx/php-fpm"; do
		src="$HESTIA_INSTALL_DIR/templates/web/nginx${dst#"$WEBTPL/nginx"}"
		[ -d "$dst" ] || continue
		for ext in tpl stpl; do
			if [ ! -e "$dst/$APP_TEMPLATE.$ext" ] && [ -e "$src/$APP_TEMPLATE.$ext" ]; then
				cp "$src/$APP_TEMPLATE.$ext" "$dst/$APP_TEMPLATE.$ext"
			fi
		done
	done
}

# Switch the domain to the app template. With Apache the nginx proxy template
# is switched; on nginx-only servers the web template is switched.
# Prints "TPL_TYPE PREVIOUS_TEMPLATE".
app_enable_template() {
	local user="$1" domain="$2" restart="$3" proxy tpl proxy_ext
	app_ensure_templates
	proxy=$(get_object_value 'web' 'DOMAIN' "$domain" '$PROXY')
	tpl=$(get_object_value 'web' 'DOMAIN' "$domain" '$TPL')
	proxy_ext=$(get_object_value 'web' 'DOMAIN' "$domain" '$PROXY_EXT')
	if [ "$PROXY_SYSTEM" = 'nginx' ]; then
		if [ -z "$proxy" ]; then
			$BIN/v-add-web-domain-proxy "$user" "$domain" "$APP_TEMPLATE" '' "$restart" > /dev/null
		elif [ "$proxy" != "$APP_TEMPLATE" ]; then
			$BIN/v-change-web-domain-proxy-tpl "$user" "$domain" "$APP_TEMPLATE" "$proxy_ext" "$restart" > /dev/null
		fi
		check_result $? "unable to switch proxy template to $APP_TEMPLATE" > /dev/null
		echo "proxy $proxy"
	else
		if [ "$tpl" != "$APP_TEMPLATE" ]; then
			$BIN/v-change-web-domain-tpl "$user" "$domain" "$APP_TEMPLATE" "$restart" > /dev/null
			check_result $? "unable to switch web template to $APP_TEMPLATE" > /dev/null
		fi
		echo "web $tpl"
	fi
}

# Restore the template that was active before the app was added
app_disable_template() {
	local user="$1" domain="$2" restart="$3" type="$4" previous="$5" current
	if [ "$type" = 'proxy' ]; then
		current=$(get_object_value 'web' 'DOMAIN' "$domain" '$PROXY')
		[ "$current" != "$APP_TEMPLATE" ] && return 0
		if [ -z "$previous" ]; then
			$BIN/v-delete-web-domain-proxy "$user" "$domain" "$restart" > /dev/null
		else
			[ -e "$WEBTPL/$PROXY_SYSTEM/$previous.tpl" ] || previous='default'
			$BIN/v-change-web-domain-proxy-tpl "$user" "$domain" "$previous" \
				"$(get_object_value 'web' 'DOMAIN' "$domain" '$PROXY_EXT')" "$restart" > /dev/null
		fi
	else
		current=$(get_object_value 'web' 'DOMAIN' "$domain" '$TPL')
		[ "$current" != "$APP_TEMPLATE" ] && return 0
		[ -n "$previous" ] && [ "$previous" != "$APP_TEMPLATE" ] || previous='default'
		$BIN/v-change-web-domain-tpl "$user" "$domain" "$previous" "$restart" > /dev/null
	fi
}

#----------------------------------------------------------#
#                    Listing                               #
#----------------------------------------------------------#

app_json_escape() {
	local s="$1"
	s="${s//\\/\\\\}"
	s="${s//\"/\\\"}"
	s="${s//$'\t'/\\t}"
	printf '%s' "$s"
}

# Collect app rows (optionally a single domain) with live state into the
# APP_ROWS array. Each entry holds the app.conf line plus a CPU sample.
# CPU usage is measured over 500ms for all running apps at once.
collect_app_rows() {
	local filter="$1" line domain i
	APP_ROWS=()
	APP_CPU=()
	declare -gA APP_CPU_A=()
	[ -f "$USER_DATA/app.conf" ] || return 0
	while IFS= read -r line; do
		[[ "$line" =~ ^DOMAIN=\'([^\']+)\' ]] || continue
		domain="${BASH_REMATCH[1]}"
		if [ -n "$filter" ] && [ "$filter" != "$domain" ]; then
			continue
		fi
		APP_ROWS+=("$line")
	done < "$USER_DATA/app.conf"

	local sampled=no
	for i in "${!APP_ROWS[@]}"; do
		[[ "${APP_ROWS[$i]}" =~ ^DOMAIN=\'([^\']+)\' ]]
		get_app_state "$user" "${BASH_REMATCH[1]}"
		APP_CPU_A[$i]="$APP_CPU_NSEC"
		[ "$APP_STATE" = 'active' ] && sampled=yes
	done
	[ "$sampled" = 'yes' ] && sleep 0.5
	for i in "${!APP_ROWS[@]}"; do
		[[ "${APP_ROWS[$i]}" =~ ^DOMAIN=\'([^\']+)\' ]]
		get_app_state "$user" "${BASH_REMATCH[1]}"
		if [ "$APP_STATE" = 'active' ]; then
			APP_CPU[$i]=$(app_cpu_percent "${APP_CPU_A[$i]}" "$APP_CPU_NSEC" 500)
		else
			APP_CPU[$i]='0.0'
		fi
	done
}

# Print the collected rows in the requested format
print_app_rows() {
	local format="$1" i total
	total=${#APP_ROWS[@]}
	case "$format" in
		json) echo '{' ;;
		shell)
			echo "DOMAIN^RUNTIME^VERSION^STATUS^STATE^PID^CPU^MEMORY^UPTIME^RESTARTS^PORT"
			echo "------^-------^-------^------^-----^---^---^------^------^--------^----"
			;;
		csv) echo "DOMAIN,RUNTIME,VERSION,DIR,CMD,INSTALL,PORT,STATUS,STATE,PID,UPTIME,RESTARTS,MEMORY,CPU,SUSPENDED,TIME,DATE" ;;
	esac
	for i in "${!APP_ROWS[@]}"; do
		parse_object_kv_list "${APP_ROWS[$i]}"
		CMD="${CMD//%quote%/\'}"
		INSTALL="${INSTALL//%quote%/\'}"
		# An app is suspended together with its web domain
		SUSPENDED=$(grep "DOMAIN='$DOMAIN'" "$USER_DATA/web.conf" | grep -o "SUSPENDED='[a-z]*'" | cut -f 2 -d \')
		SUSPENDED="${SUSPENDED:-no}"
		get_app_state "$user" "$DOMAIN"
		case "$format" in
			json)
				echo -n '    "'$DOMAIN'": {
        "DOMAIN": "'$DOMAIN'",
        "RUNTIME": "'$RUNTIME'",
        "VERSION": "'$VERSION'",
        "DIR": "'"$(app_json_escape "$DIR")"'",
        "CMD": "'"$(app_json_escape "$CMD")"'",
        "INSTALL": "'"$(app_json_escape "$INSTALL")"'",
        "PORT": "'$PORT'",
        "STATUS": "'$STATUS'",
        "STATE": "'$APP_STATE'",
        "SUBSTATE": "'$APP_SUBSTATE'",
        "PID": "'$APP_PID'",
        "UPTIME": "'$APP_UPTIME'",
        "RESTARTS": "'$APP_RESTARTS'",
        "MEMORY": "'$APP_MEMORY'",
        "CPU": "'${APP_CPU[$i]}'",
        "SUSPENDED": "'$SUSPENDED'",
        "TIME": "'$TIME'",
        "DATE": "'$DATE'"
    }'
				if [ "$((i + 1))" -lt "$total" ]; then
					echo ','
				else
					echo
				fi
				;;
			plain)
				echo -ne "$DOMAIN\t$RUNTIME\t$VERSION\t$DIR\t$CMD\t$INSTALL\t$PORT\t$STATUS\t"
				echo -e "$APP_STATE\t$APP_PID\t$APP_UPTIME\t$APP_RESTARTS\t$APP_MEMORY\t${APP_CPU[$i]}\t$SUSPENDED\t$TIME\t$DATE"
				;;
			csv)
				echo -n "$DOMAIN,$RUNTIME,$VERSION,\"$DIR\",\"${CMD//\"/\"\"}\",\"${INSTALL//\"/\"\"}\",$PORT,$STATUS,"
				echo "$APP_STATE,$APP_PID,$APP_UPTIME,$APP_RESTARTS,$APP_MEMORY,${APP_CPU[$i]},$SUSPENDED,$TIME,$DATE"
				;;
			shell)
				echo "$DOMAIN^$RUNTIME^$VERSION^$STATUS^$APP_STATE^$APP_PID^${APP_CPU[$i]}%^$((APP_MEMORY / 1048576))M^${APP_UPTIME}s^$APP_RESTARTS^$PORT"
				;;
		esac
	done
	[ "$format" = 'json' ] && echo '}'
	return 0
}

# Keep the app template and the app in sync: the template can only be chosen
# through v-add-web-domain-app and can't be replaced while the app exists.
# $4 is the template kind being changed: proxy or web.
is_app_template_change_valid() {
	local user="$1" domain="$2" template="$3" kind="$4" tpl_type
	if ! app_exists "$user" "$domain"; then
		if [ "$template" = "$APP_TEMPLATE" ]; then
			check_result "$E_INVALID" "the $APP_TEMPLATE template is managed by v-add-web-domain-app"
		fi
		return 0
	fi
	tpl_type=$(grep "^DOMAIN='$domain' " "$HESTIA/data/users/$user/app.conf" | grep -o "TPL_TYPE='[a-z]*'" | cut -f 2 -d \')
	# While the app is being added TPL_TYPE is still empty
	if [ -n "$tpl_type" ] && [ "$tpl_type" = "$kind" ] && [ "$template" != "$APP_TEMPLATE" ]; then
		check_result "$E_INUSE" "domain $domain serves an application, delete it first (v-delete-web-domain-app)"
	fi
}

#----------------------------------------------------------#
#                    Backup & restore                      #
#----------------------------------------------------------#

# Copy the app settings and environment of $user/$domain into $3 (the
# "hestia" folder of the domain in a backup). The app files themselves are
# part of the domain data archive.
backup_app_conf() {
	local user="$1" domain="$2" dst="$3" envfile
	app_exists "$user" "$domain" || return 0
	grep "^DOMAIN='$domain' " "$HESTIA/data/users/$user/app.conf" > "$dst/app.conf"
	envfile=$(app_env_file "$user" "$domain")
	if [ -f "$envfile" ]; then
		cp "$envfile" "$dst/app.env"
	fi
	chmod 600 "$dst/app.conf" "$dst/app.env" 2> /dev/null
	return 0
}

# A restored web.conf may still select the app template while its app could
# not be restored: fall back to the default template.
app_restore_reset_template() {
	local domain="$1"
	if [ "$(get_object_value 'web' 'DOMAIN' "$domain" '$PROXY')" = "$APP_TEMPLATE" ]; then
		update_object_value 'web' 'DOMAIN' "$domain" '$PROXY' 'default'
	fi
	if [ "$(get_object_value 'web' 'DOMAIN' "$domain" '$TPL')" = "$APP_TEMPLATE" ]; then
		update_object_value 'web' 'DOMAIN' "$domain" '$TPL' 'default'
	fi
}

# Register the app of $user/$domain from the "hestia" folder $3 of a backup.
# Backup content is not trusted (restic backups pass through the user home):
# every value is validated again, the port is re-allocated when it is taken,
# the environment is re-parsed and the template type follows this server.
# Call it after the domain data was restored and before the final rebuild,
# which writes the unit and starts the app.
restore_app_conf() {
	local user="$1" domain="$2" src="$3"
	local line port tpl_type prev_tpl envfile tmpenv used
	# Filled by parse_object_kv_list; local so restore variables are untouched
	# shellcheck disable=SC2034
	local DOMAIN RUNTIME VERSION DIR CMD INSTALL PORT STATUS TPL_TYPE PREV_TPL SUSPENDED TIME DATE

	if [ ! -f "$src/app.conf" ]; then
		app_exists "$user" "$domain" || app_restore_reset_template "$domain"
		return 0
	fi
	if [ "$PROXY_SYSTEM" != 'nginx' ] && [ "$WEB_SYSTEM" != 'nginx' ]; then
		echo "$(date "+%F %T") skipping application of $domain: nginx is required"
		return 0
	fi
	line=$(grep -m1 "^DOMAIN='$domain' " "$src/app.conf")
	[ -n "$line" ] || return 0
	parse_object_kv_list "$line" || return 0
	CMD="${CMD//%quote%/\'}"
	INSTALL="${INSTALL//%quote%/\'}"

	# Validate in subshells: check_result would end the whole restore
	if ! (is_app_runtime_valid "$RUNTIME") > /dev/null \
		|| ! (is_app_dir_valid "$DIR") > /dev/null \
		|| ! (is_app_command_valid "$CMD" 'command') > /dev/null \
		|| ! (is_app_command_valid "$INSTALL" 'install command') > /dev/null; then
		echo "$(date "+%F %T") skipping application of $domain: invalid settings in backup"
		app_restore_reset_template "$domain"
		return 0
	fi
	if [ -z "$CMD" ] && [ "$RUNTIME" != 'node' ]; then
		echo "$(date "+%F %T") skipping application of $domain: no start command"
		app_restore_reset_template "$domain"
		return 0
	fi
	if ! (is_app_version_valid "$RUNTIME" "$VERSION") > /dev/null 2>&1; then
		echo "$(date "+%F %T") Node.js $VERSION is not installed, $domain uses the system version"
		VERSION='system'
	fi
	[ "$STATUS" = 'running' ] || STATUS='stopped'

	app_lock
	# Keep the old port when no other app uses it and nothing listens on it
	used=$(grep -h "PORT='$PORT'" "$HESTIA"/data/users/*/app.conf 2> /dev/null \
		| grep -v "^DOMAIN='$domain' ")
	port="$PORT"
	if ! [[ "$port" =~ ^[0-9]+$ ]] || [ "$port" -lt "$(app_port_min)" ] \
		|| [ "$port" -gt "$(app_port_max)" ] || [ -n "$used" ] \
		|| { ! app_exists "$user" "$domain" && ss -Htln "sport = :$port" | grep -q .; }; then
		port=$(get_free_app_port)
		if [ -z "$port" ]; then
			flock -u "$APP_LOCK_FD"
			echo "$(date "+%F %T") skipping application of $domain: no free port"
			app_restore_reset_template "$domain"
			return 0
		fi
	fi

	# Point the domain at the app on this server (with or without Apache)
	if [ "$PROXY_SYSTEM" = 'nginx' ]; then
		tpl_type='proxy'
		prev_tpl=$(get_object_value 'web' 'DOMAIN' "$domain" '$PROXY')
		update_object_value 'web' 'DOMAIN' "$domain" '$PROXY' "$APP_TEMPLATE"
	else
		tpl_type='web'
		prev_tpl=$(get_object_value 'web' 'DOMAIN' "$domain" '$TPL')
		update_object_value 'web' 'DOMAIN' "$domain" '$TPL' "$APP_TEMPLATE"
	fi
	if [ "$prev_tpl" = "$APP_TEMPLATE" ]; then
		prev_tpl="$PREV_TPL"
		[[ "$prev_tpl" =~ ^[[:alnum:]][-._[:alnum:]]*$ ]] || prev_tpl='default'
	fi

	touch "$USER_DATA/app.conf"
	chmod 660 "$USER_DATA/app.conf"
	sed -i "/^DOMAIN='$domain' /d" "$USER_DATA/app.conf"
	echo "DOMAIN='$domain' RUNTIME='$RUNTIME' VERSION='$VERSION'\
 DIR='$DIR' CMD='$(app_conf_encode "$CMD")' INSTALL='$(app_conf_encode "$INSTALL")'\
 PORT='$port' STATUS='$STATUS' TPL_TYPE='$tpl_type' PREV_TPL='$prev_tpl'\
 SUSPENDED='no' TIME='$(date +%T)' DATE='$(date +%F)'" >> "$USER_DATA/app.conf"
	flock -u "$APP_LOCK_FD"

	# Environment: re-parse the backup copy, fall back to an empty one
	mkdir -p "$USER_DATA/app"
	chmod 750 "$USER_DATA/app"
	envfile=$(app_env_file "$user" "$domain")
	: > "$envfile"
	chmod 600 "$envfile"
	if [ -f "$src/app.env" ] && [ ! -L "$src/app.env" ]; then
		tmpenv=$(mktemp)
		print_app_env "$src/app.env" > "$tmpenv"
		if ! (write_app_env "$tmpenv" "$envfile") > /dev/null; then
			echo "$(date "+%F %T") environment of $domain could not be restored"
		fi
		rm -f "$tmpenv"
	fi

	app_ensure_templates
	echo "$(date "+%F %T") application of $domain restored ($RUNTIME, port $port, $STATUS)"
}
