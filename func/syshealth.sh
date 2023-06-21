#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - System Health Check and Repair Function Library    #
#                                                                           #
#===========================================================================#

# Read known configuration keys from $HESTIA/conf/defaults/$system.conf
function read_kv_config_file() {
	local system=$1

	if [ ! -f "$HESTIA/conf/defaults/$system.conf" ]; then
		write_kv_config_file $system
	fi
	while read -r str; do
		echo "$str"
	done < <(cat $HESTIA/conf/defaults/$system.conf)
	unset system
}

# Write known configuration keys to $HESTIA/conf/defaults/
function write_kv_config_file() {
	# Ensure configuration directory exists
	if [ ! -d "$HESTIA/conf/defaults/" ]; then
		mkdir "$HESTIA/conf/defaults/"
	fi

	# Remove previous known good configuration
	if [ -f "$HESTIA/conf/defaults/$system.conf" ]; then
		rm -f $HESTIA/conf/defaults/$system.conf
	fi

	touch $HESTIA/conf/defaults/$system.conf
	for key in $known_keys; do
		echo $key >> $HESTIA/conf/defaults/$system.conf
	done
}

# Sanitize configuration input
function sanitize_config_file() {
	local system=$1
	known_keys=$(read_kv_config_file "$system")
	for key in $known_keys; do
		unset $key
	done
}

# Update list of known keys for web.conf files
function syshealth_update_web_config_format() {

	# WEB DOMAINS
	# Create array of known keys in configuration file
	system="web"
	known_keys="DOMAIN IP IP6 CUSTOM_DOCROOT CUSTOM_PHPROOT FASTCGI_CACHE FASTCGI_DURATION ALIAS TPL SSL SSL_FORCE SSL_HSTS SSL_HOME LETSENCRYPT FTP_USER FTP_MD5 FTP_PATH BACKEND PROXY PROXY_EXT STATS STATS_USER STATS_CRYPT REDIRECT REDIRECT_CODE AUTH_USER AUTH_HASH SUSPENDED TIME DATE"
	write_kv_config_file
	unset system
	unset known_keys
}

# Update list of known keys for dns.conf files
function syshealth_update_dns_config_format() {

	# DNS DOMAINS
	# Create array of known keys in configuration file
	system="dns"
	known_keys="DOMAIN IP TPL TTL EXP SOA SERIAL SRC RECORDS DNSSEC KEY SLAVE MASTER SUSPENDED TIME DATE"
	write_kv_config_file
	unset system
	unset known_keys

	# DNS RECORDS
	system="dns_records"
	known_keys="ID RECORD TYPE PRIORITY VALUE SUSPENDED TIME DATE TTL"
	write_kv_config_file
	unset system
	unset known_keys
}

# Update list of known keys for mail.conf files
function syshealth_update_mail_config_format() {

	# MAIL DOMAINS
	# Create array of known keys in configuration file
	system="mail"
	known_keys="DOMAIN ANTIVIRUS ANTISPAM DKIM WEBMAIL SSL LETSENCRYPT CATCHALL ACCOUNTS RATE_LIMIT REJECT U_DISK SUSPENDED TIME DATE"
	write_kv_config_file
	unset system
	unset known_keys
}

function syshealth_update_mail_account_config_format() {
	# MAIL ACCOUNTS
	system="mail_accounts"
	known_keys="ACCOUNT ALIAS AUTOREPLY FWD FWD_ONLY MD5 QUOTA RATE_LIMIT U_DISK SUSPENDED TIME DATE"
	write_kv_config_file
	unset system
	unset known_keys
}

# Update list of known keys for user.conf files
function syshealth_update_user_config_format() {

	# USER CONFIGURATION
	# Create array of known keys in configuration file
	system="user"
	known_keys="NAME PACKAGE CONTACT CRON_REPORTS MD5 RKEY TWOFA QRCODE PHPCLI ROLE SUSPENDED SUSPENDED_USERS SUSPENDED_WEB SUSPENDED_DNS SUSPENDED_MAIL SUSPENDED_DB SUSPENDED_CRON IP_AVAIL IP_OWNED U_USERS U_DISK U_DISK_DIRS U_DISK_WEB U_DISK_MAIL U_DISK_DB U_BANDWIDTH U_WEB_DOMAINS U_WEB_SSL U_WEB_ALIASES U_DNS_DOMAINS U_DNS_RECORDS U_MAIL_DKIM U_MAIL_DKIM U_MAIL_ACCOUNTS U_MAIL_DOMAINS U_MAIL_SSL U_DATABASES U_CRON_JOBS U_BACKUPS LANGUAGE THEME NOTIFICATIONS PREF_UI_SORT TIME DATE"
	write_kv_config_file
	unset system
	unset known_keys

	# CRON JOB CONFIGURATION
	# Create array of known keys in configuration file
	system="cron"
	known_keys="JOB MIN HOUR DAY MONTH WDAY CMD SUSPENDED TIME DATE"
	write_kv_config_file
	unset system
	unset known_keys
}

# Update list of known keys for db.conf files
function syshealth_update_db_config_format() {

	# DATABASE CONFIGURATION
	# Create array of known keys in configuration file
	system="db"
	known_keys="DB DBUSER MD5 HOST TYPE CHARSET U_DISK SUSPENDED TIME DATE"
	write_kv_config_file
	unset system
	unset known_keys
}

# Update list of known keys for ip.conf files
function syshealth_update_ip_config_format() {

	# IP ADDRESS
	# Create array of known keys in configuration file
	system="ip"
	known_keys="OWNER STATUS NAME U_SYS_USERS U_WEB_DOMAINS INTERFACE NETMASK NAT TIME DATE"
	write_kv_config_file
	unset system
	unset known_keys
}

# Repair web domain configuration
function syshealth_repair_web_config() {
	system="web"
	sanitize_config_file "$system"
	get_domain_values 'web'
	prev="DOMAIN"
	for key in $known_keys; do
		if [ -z "${!key}" ]; then
			add_object_key 'web' 'DOMAIN' "$domain" "$key" "$prev"
		fi
		prev=$key
	done
}

function syshealth_repair_mail_config() {
	system="mail"
	sanitize_config_file "$system"
	get_domain_values 'mail'
	prev="DOMAIN"
	for key in $known_keys; do
		if [ -z "${!key}" ]; then
			add_object_key 'mail' 'DOMAIN' "$domain" "$key" "$prev"
		fi
		prev=$key
	done
}

function syshealth_repair_dns_config() {
	system="dns"
	sanitize_config_file "$system"
	get_domain_values 'dns'
	prev="DOMAIN"
	for key in $known_keys; do
		if [ -z "${!key}" ]; then
			add_object_key 'dns' 'DOMAIN' "$domain" "$key" "$prev"
		fi
		prev=$key
	done
}

function syshealth_repair_mail_account_config() {
	system="mail_accounts"
	sanitize_config_file "$system"
	get_object_values "mail/$domain" 'ACCOUNT' "$account"
	for key in $known_keys; do
		if [ -z "${!key}" ]; then
			add_object_key "mail/$domain" 'ACCOUNT' "$account" "$key" "$prev"
		fi
		prev=$key
	done
}

function syshealth_update_system_config_format() {
	# SYSTEM CONFIGURATION
	# Create array of known keys in configuration file
	system="system"
	known_keys="ANTISPAM_SYSTEM ANTIVIRUS_SYSTEM API_ALLOWED_IP API BACKEND_PORT BACKUP_GZIP BACKUP_MODE BACKUP_SYSTEM CRON_SYSTEM DB_PMA_ALIAS DB_SYSTEM DISK_QUOTA DNS_SYSTEM ENFORCE_SUBDOMAIN_OWNERSHIP FILE_MANAGER FIREWALL_EXTENSION FIREWALL_SYSTEM FTP_SYSTEM IMAP_SYSTEM INACTIVE_SESSION_TIMEOUT LANGUAGE LOGIN_STYLE MAIL_SYSTEM PROXY_PORT PROXY_SSL_PORT PROXY_SYSTEM RELEASE_BRANCH STATS_SYSTEM THEME UPDATE_HOSTNAME_SSL UPGRADE_SEND_EMAIL UPGRADE_SEND_EMAIL_LOG WEB_BACKEND WEBMAIL_ALIAS WEBMAIL_SYSTEM WEB_PORT WEB_RGROUPS WEB_SSL WEB_SSL_PORT WEB_SYSTEM VERSION DISABLE_IP_CHECK"
	write_kv_config_file
	unset system
	unset known_keys
}

# Restore System Configuration
# Replaces $HESTIA/conf/hestia.conf with "known good defaults" file ($HESTIA/conf/defaults/hestia.conf)
function syshealth_restore_system_config() {
	if [ -f "$HESTIA/conf/defaults/hestia.conf" ]; then
		mv $HESTIA/conf/hestia.conf $HESTIA/conf/hestia.conf.old
		cp $HESTIA/conf/defaults/hestia.conf $HESTIA/conf/hestia.conf
		rm -f $HESTIA/conf/hestia.conf.old
	else
		echo "ERROR: System default configuration file not found, aborting."
		exit 1
	fi
}

function check_key_exists() {
	grep -e "^$1=" $HESTIA/conf/hestia.conf
}

# Repair System Configuration
# Adds missing variables to $HESTIA/conf/hestia.conf with safe default values
function syshealth_repair_system_config() {
	# Release branch
	if [[ -z $(check_key_exists 'RELEASE_BRANCH') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: RELEASE_BRANCH ('release')"
		$BIN/v-change-sys-config-value 'RELEASE_BRANCH' 'release'
	fi
	# Webmail alias
	if [ -n "$IMAP_SYSTEM" ]; then
		if [[ -z $(check_key_exists 'WEBMAIL_ALIAS') ]]; then
			echo "[ ! ] Adding missing variable to hestia.conf: WEBMAIL_ALIAS ('webmail')"
			$BIN/v-change-sys-config-value 'WEBMAIL_ALIAS' 'webmail'
		fi
	fi

	# phpMyAdmin/phpPgAdmin alias
	if [ -n "$DB_SYSTEM" ]; then
		if [ "$DB_SYSTEM" = "mysql" ]; then
			if [[ -z $(check_key_exists 'DB_PMA_ALIAS') ]]; then
				echo "[ ! ] Adding missing variable to hestia.conf: DB_PMA_ALIAS ('phpmyadmin)"
				$BIN/v-change-sys-config-value 'DB_PMA_ALIAS' 'phpmyadmin'
			fi
		fi
		if [ "$DB_SYSTEM" = "pgsql" ]; then
			if [[ -z $(check_key_exists 'DB_PGA_ALIAS') ]]; then
				echo "[ ! ] Adding missing variable to hestia.conf: DB_PGA_ALIAS ('phppgadmin')"
				$BIN/v-change-sys-config-value 'DB_PGA_ALIAS' 'phppgadmin'
			fi
		fi
	fi

	# Backup compression level
	if [[ -z $(check_key_exists 'BACKUP_GZIP') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: BACKUP_GZIP ('4')"
		$BIN/v-change-sys-config-value 'BACKUP_GZIP' '4'
	fi

	# Theme
	if [[ -z $(check_key_exists 'THEME') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: THEME ('dark')"
		$BIN/v-change-sys-config-value 'THEME' 'dark'
	fi

	# Default language
	if [[ -z $(check_key_exists 'LANGUAGE') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: LANGUAGE ('en')"
		$BIN/v-change-sys-language 'LANGUAGE' 'en'
	fi

	# Disk Quota
	if [[ -z $(check_key_exists 'DISK_QUOTA') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: DISK_QUOTA ('no')"
		$BIN/v-change-sys-config-value 'DISK_QUOTA' 'no'
	fi

	# CRON daemon
	if [[ -z $(check_key_exists 'CRON_SYSTEM') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: CRON_SYSTEM ('cron')"
		$BIN/v-change-sys-config-value 'CRON_SYSTEM' 'cron'
	fi

	# Backend port
	if [[ -z $(check_key_exists 'BACKEND_PORT') ]]; then
		ORIGINAL_PORT=$(cat $HESTIA/nginx/conf/nginx.conf | grep -m 1 "listen" | sed 's/[^0-9]*//g')
		echo "[ ! ] Adding missing variable to hestia.conf: BACKEND_PORT ('$ORIGINAL_PORT')"
		$BIN/v-change-sys-config-value 'BACKEND_PORT' $ORIGINAL_PORT
	fi

	# Upgrade: Send email notification
	if [[ -z $(check_key_exists 'UPGRADE_SEND_EMAIL') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: UPGRADE_SEND_EMAIL ('true')"
		$BIN/v-change-sys-config-value 'UPGRADE_SEND_EMAIL' 'true'
	fi

	# Upgrade: Send email notification
	if [[ -z $(check_key_exists 'UPGRADE_SEND_EMAIL_LOG') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: UPGRADE_SEND_EMAIL_LOG ('false')"
		$BIN/v-change-sys-config-value 'UPGRADE_SEND_EMAIL_LOG' 'false'
	fi

	# File Manager
	if [[ -z $(check_key_exists 'FILE_MANAGER') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: FILE_MANAGER ('true')"
		$BIN/v-add-sys-filemanager quiet
	fi

	# Support for ZSTD / GZIP Change
	if [[ -z $(check_key_exists 'BACKUP_MODE') ]]; then
		echo "[ ! ] Setting zstd backup compression type as default..."
		$BIN/v-change-sys-config-value "BACKUP_MODE" "zstd"
	fi

	# Login style switcher
	if [[ -z $(check_key_exists 'LOGIN_STYLE') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: LOGIN_STYLE ('default')"
		$BIN/v-change-sys-config-value "LOGIN_STYLE" "default"
	fi

	# Webmail clients
	if [[ -z $(check_key_exists 'WEBMAIL_SYSTEM') ]]; then
		if [ -d "/var/lib/roundcube" ]; then
			echo "[ ! ] Adding missing variable to hestia.conf: WEBMAIL_SYSTEM ('roundcube')"
			$BIN/v-change-sys-config-value "WEBMAIL_SYSTEM" "roundcube"
		else
			echo "[ ! ] Adding missing variable to hestia.conf: WEBMAIL_SYSTEM ('')"
			$BIN/v-change-sys-config-value "WEBMAIL_SYSTEM" ""
		fi
	fi

	# Inactive session timeout
	if [[ -z $(check_key_exists 'INACTIVE_SESSION_TIMEOUT') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: INACTIVE_SESSION_TIMEOUT ('60')"
		$BIN/v-change-sys-config-value "INACTIVE_SESSION_TIMEOUT" "60"
	fi

	# Enforce subdomain ownership
	if [[ -z $(check_key_exists 'ENFORCE_SUBDOMAIN_OWNERSHIP') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: ENFORCE_SUBDOMAIN_OWNERSHIP ('yes')"
		$BIN/v-change-sys-config-value "ENFORCE_SUBDOMAIN_OWNERSHIP" "yes"
	fi

	if [[ -z $(check_key_exists 'API') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: API ('no')"
		$BIN/v-change-sys-config-value "API" "no"
	fi

	# Enable API V2
	if [[ -z $(check_key_exists 'API_SYSTEM') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: API_SYSTEM ('0')"
		$BIN/v-change-sys-config-value "API_SYSTEM" "0"
	fi

	# API access allowed IP's
	if [ "$API" = "yes" ]; then
		check_api_key=$(grep "API_ALLOWED_IP" $HESTIA/conf/hestia.conf)
		if [ -z "$check_api_key" ]; then
			if [[ -z $(check_key_exists 'API_ALLOWED_IP') ]]; then
				echo "[ ! ] Adding missing variable to hestia.conf: API_ALLOWED_IP ('allow-all')"
				$BIN/v-change-sys-config-value "API_ALLOWED_IP" "allow-all"
			fi
		fi
	fi

	# Debug mode
	if [[ -z $(check_key_exists 'DEBUG_MODE') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: DEBUG_MODE ('false')"
		$BIN/v-change-sys-config-value "DEBUG_MODE" "false"
	fi
	# Quick install plugin
	if [[ -z $(check_key_exists 'PLUGIN_APP_INSTALLER') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: PLUGIN_APP_INSTALLER ('true')"
		$BIN/v-change-sys-config-value "PLUGIN_APP_INSTALLER" "true"
	fi
	# Enable preview mode
	if [[ -z $(check_key_exists 'POLICY_SYSTEM_ENABLE_BACON') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_SYSTEM_ENABLE_BACON ('false')"
		$BIN/v-change-sys-config-value "POLICY_SYSTEM_ENABLE_BACON" "false"
	fi
	# Hide system services
	if [[ -z $(check_key_exists 'POLICY_SYSTEM_HIDE_SERVICES') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_SYSTEM_HIDE_SERVICES ('no')"
		$BIN/v-change-sys-config-value "POLICY_SYSTEM_HIDE_SERVICES" "no"
	fi
	# Password reset
	if [[ -z $(check_key_exists 'POLICY_SYSTEM_PASSWORD_RESET') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_SYSTEM_PASSWORD_RESET ('no')"
		$BIN/v-change-sys-config-value "POLICY_SYSTEM_PASSWORD_RESET" "no"
	fi

	# Theme editor
	if [[ -z $(check_key_exists 'POLICY_USER_CHANGE_THEME') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_USER_CHANGE_THEME ('yes')"
		$BIN/v-change-sys-config-value "POLICY_USER_CHANGE_THEME" "true"
	fi
	# Protect admin user
	if [[ -z $(check_key_exists 'POLICY_SYSTEM_PROTECTED_ADMIN') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_SYSTEM_PROTECTED_ADMIN ('no')"
		$BIN/v-change-sys-config-value "POLICY_SYSTEM_PROTECTED_ADMIN" "no"
	fi
	# Allow user delete logs
	if [[ -z $(check_key_exists 'POLICY_USER_DELETE_LOGS') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_USER_DELETE_LOGS ('yes')"
		$BIN/v-change-sys-config-value "POLICY_USER_DELETE_LOGS" "yes"
	fi
	# Allow users to delete details
	if [[ -z $(check_key_exists 'POLICY_USER_EDIT_DETAILS') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_USER_EDIT_DETAILS ('yes')"
		$BIN/v-change-sys-config-value "POLICY_USER_EDIT_DETAILS" "yes"
	fi
	# Allow users to edit DNS templates
	if [[ -z $(check_key_exists 'POLICY_USER_EDIT_DNS_TEMPLATES') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_USER_EDIT_DNS_TEMPLATES ('yes')"
		$BIN/v-change-sys-config-value "POLICY_USER_EDIT_DNS_TEMPLATES" "yes"
	fi
	# Allow users to edit web templates
	if [[ -z $(check_key_exists 'POLICY_USER_EDIT_WEB_TEMPLATES') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_USER_EDIT_WEB_TEMPLATES ('yes')"
		$BIN/v-change-sys-config-value "POLICY_USER_EDIT_WEB_TEMPLATES" "true"
	fi
	# View user logs
	if [[ -z $(check_key_exists 'POLICY_USER_VIEW_LOGS') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_USER_VIEW_LOGS ('yes')"
		$BIN/v-change-sys-config-value "POLICY_USER_VIEW_LOGS" "true"
	fi
	# Allow users to login (read only) when suspended
	if [[ -z $(check_key_exists 'POLICY_USER_VIEW_SUSPENDED') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_USER_VIEW_SUSPENDED ('no')"
		$BIN/v-change-sys-config-value "POLICY_USER_VIEW_SUSPENDED" "no"
	fi
	# PHPMyadmin SSO key
	if [[ -z $(check_key_exists 'PHPMYADMIN_KEY') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: PHPMYADMIN_KEY ('')"
		$BIN/v-change-sys-config-value "PHPMYADMIN_KEY" ""
	fi
	# Use SMTP server for hestia internal mail
	if [[ -z $(check_key_exists 'USE_SERVER_SMTP') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: USE_SERVER_SMTP ('')"
		$BIN/v-change-sys-config-value "USE_SERVER_SMTP" "false"
	fi

	if [[ -z $(check_key_exists 'SERVER_SMTP_HOST') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: SERVER_SMTP_PORT ('')"
		$BIN/v-change-sys-config-value "SERVER_SMTP_HOST" ""
	fi

	if [[ -z $(check_key_exists 'SERVER_SMTP_HOST') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: SERVER_SMTP_HOST ('')"
		$BIN/v-change-sys-config-value "SERVER_SMTP_HOST" ""
	fi

	if [[ -z $(check_key_exists 'SERVER_SMTP_SECURITY') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: SERVER_SMTP_SECURITY ('')"
		$BIN/v-change-sys-config-value "SERVER_SMTP_SECURITY" ""
	fi

	if [[ -z $(check_key_exists 'SERVER_SMTP_USER') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: SERVER_SMTP_USER ('')"
		$BIN/v-change-sys-config-value "SERVER_SMTP_USER" ""
	fi

	if [[ -z $(check_key_exists 'SERVER_SMTP_PASSWD') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: SERVER_SMTP_PASSWD ('')"
		$BIN/v-change-sys-config-value "SERVER_SMTP_PASSWD" ""
	fi

	if [[ -z $(check_key_exists 'SERVER_SMTP_ADDR') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: SERVER_SMTP_ADDR ('')"
		$BIN/v-change-sys-config-value "SERVER_SMTP_ADDR" ""
	fi
	if [[ -z $(check_key_exists 'POLICY_CSRF_STRICTNESS') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_CSRF_STRICTNESS ('')"
		$BIN/v-change-sys-config-value "POLICY_CSRF_STRICTNESS" "1"
	fi
	if [[ -z $(check_key_exists 'DNS_CLUSTER_SYSTEM') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: DNS_CLUSTER_SYSTEM ('hestia')"
		$BIN/v-change-sys-config-value "DNS_CLUSTER_SYSTEM" "hestia"
	fi
	if [[ -z $(check_key_exists 'DISABLE_IP_CHECK') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: DISABLE_IP_CHECK ('no')"
		$BIN/v-change-sys-config-value "DISABLE_IP_CHECK" "no"
	fi
	if [[ -z $(check_key_exists 'APP_NAME') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: APP_NAME ('Hestia Control Panel')"
		$BIN/v-change-sys-config-value "APP_NAME" "Hestia Control Panel"
	fi
	if [[ -z $(check_key_exists 'FROM_NAME') ]]; then
		# Default is always APP_NAME
		echo "[ ! ] Adding missing variable to hestia.conf: FROM_NAME ('')"
		$BIN/v-change-sys-config-value "FROM_NAME" ""
	fi
	if [[ -z $(check_key_exists 'FROM_EMAIL') ]]; then
		# Default is always noreply@hostname.com
		echo "[ ! ] Adding missing variable to hestia.conf: FROM_EMAIL ('')"
		$BIN/v-change-sys-config-value "FROM_EMAIL" ""
	fi
	if [[ -z $(check_key_exists 'SUBJECT_EMAIL') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: SUBJECT_EMAIL ('{{subject}}')"
		$BIN/v-change-sys-config-value "SUBJECT_EMAIL" "{{subject}}"
	fi

	if [[ -z $(check_key_exists 'TITLE') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: TITLE ('{{page}} - {{hostname}} - {{appname}}')"
		$BIN/v-change-sys-config-value "TITLE" "{{page}} - {{hostname}} - {{appname}}"
	fi

	if [[ -z $(check_key_exists 'HIDE_DOCS') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: HIDE_DOCS ('no')"
		$BIN/v-change-sys-config-value "HIDE_DOCS" "no"
	fi

	if [[ -z $(check_key_exists 'POLICY_SYNC_ERROR_DOCUMENTS') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_SYNC_ERROR_DOCUMENTS ('yes')"
		$BIN/v-change-sys-config-value "POLICY_SYNC_ERROR_DOCUMENTS" "yes"
	fi

	if [[ -z $(check_key_exists 'POLICY_SYNC_SKELETON') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_SYNC_SKELETON ('yes')"
		$BIN/v-change-sys-config-value "POLICY_SYNC_SKELETON" "yes"
	fi
	if [[ -z $(check_key_exists 'POLICY_BACKUP_SUSPENDED_USERS') ]]; then
		echo "[ ! ] Adding missing variable to hestia.conf: POLICY_BACKUP_SUSPENDED_USERS ('no')"
		$BIN/v-change-sys-config-value "POLICY_BACKUP_SUSPENDED_USERS" "no"
	fi

	touch $HESTIA/conf/hestia.conf.new
	while IFS='= ' read -r lhs rhs; do
		if [[ ! $lhs =~ ^\ *# && -n $lhs ]]; then
			rhs="${rhs%%^\#*}" # Del in line right comments
			rhs="${rhs%%*( )}" # Del trailing spaces
			rhs="${rhs%\'*}"   # Del opening string quotes
			rhs="${rhs#\'*}"   # Del closing string quotes

		fi
		check_ckey=$(grep "^$lhs='" "$HESTIA/conf/hestia.conf.new")
		if [ -z "$check_ckey" ]; then
			echo "$lhs='$rhs'" >> "$HESTIA/conf/hestia.conf.new"
		else
			sed -i "s|^$lhs=.*|$lhs='$rhs'|g" "$HESTIA/conf/hestia.conf.new"
		fi
	done < $HESTIA/conf/hestia.conf

	cmp --silent $HESTIA/conf/hestia.conf $HESTIA/conf/hestia.conf.new
	if [ $? -ne 0 ]; then
		echo "[ ! ] Duplicated keys found repair config"
		rm $HESTIA/conf/hestia.conf
		cp $HESTIA/conf/hestia.conf.new $HESTIA/conf/hestia.conf
		rm $HESTIA/conf/hestia.conf.new
	fi
}

# Repair System Cron Jobs
# Add default cron jobs to "admin" user account's cron tab
function syshealth_repair_system_cronjobs() {
	$BIN/v-add-cron-job 'admin' '*/2' '*' '*' '*' '*' "sudo $BIN/v-update-sys-queue restart" '' 'no'
	$BIN/v-add-cron-job 'admin' '10' '00' '*' '*' '*' "sudo $BIN/v-update-sys-queue daily" '' 'no'
	$BIN/v-add-cron-job 'admin' '15' '02' '*' '*' '*' "sudo $BIN/v-update-sys-queue disk" '' 'no'
	$BIN/v-add-cron-job 'admin' '10' '00' '*' '*' '*' "sudo $BIN/v-update-sys-queue traffic" '' 'no'
	$BIN/v-add-cron-job 'admin' '30' '03' '*' '*' '*' "sudo $BIN/v-update-sys-queue webstats" '' 'no'
	$BIN/v-add-cron-job 'admin' '*/5' '*' '*' '*' '*' "sudo $BIN/v-update-sys-queue backup" '' 'no'
	$BIN/v-add-cron-job 'admin' '10' '05' '*' '*' '*' "sudo $BIN/v-backup-users" '' 'no'
	$BIN/v-add-cron-job 'admin' '20' '00' '*' '*' '*' "sudo $BIN/v-update-user-stats" '' 'no'
	$BIN/v-add-cron-job 'admin' '*/5' '*' '*' '*' '*' "sudo $BIN/v-update-sys-rrd" '' 'no'
	$BIN/v-restart-cron
}
