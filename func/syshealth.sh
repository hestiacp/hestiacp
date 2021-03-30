#!/bin/bash

# Hestia Control Panel - System Health Check and Repair Function Library

# Read known configuration keys from $HESTIA/conf/defaults/$system.conf
function read_kv_config_file() {
    local system=$1
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

    for key in ${known_keys[@]}; do
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
    known_keys=(DOMAIN IP IP6 CUSTOM_DOCROOT CUSTOM_PHPROOT FASTCGI_CACHE FASTCGI_DURATION ALIAS TPL SSL SSL_FORCE SSL_HOME LETSENCRYPT FTP_USER FTP_MD5 FTP_PATH BACKEND PROXY PROXY_EXT STATS STATS_USER STATS_CRYPT SUSPENDED TIME DATE)
    write_kv_config_file
    unset system
    unset known_keys
}

# Update list of known keys for dns.conf files
function syshealth_update_dns_config_format() {

    # DNS DOMAINS
    # Create array of known keys in configuration file
    system="dns"
    known_keys=(DOMAIN IP TPL TTL EXP SOA SERIAL SRC RECORDS SUSPENDED TIME DATE)
    write_kv_config_file
    unset system
    unset known_keys

    # DNS RECORDS
    system="dns_records"
    known_keys=(ID RECORD TYPE PRIORITY VALUE SUSPENDED TIME DATE TTL)
    write_kv_config_file
    unset system
    unset known_keys
}

# Update list of known keys for mail.conf files
function syshealth_update_mail_config_format() {

    # MAIL DOMAINS
    # Create array of known keys in configuration file
    system="mail"
    known_keys=(DOMAIN ANTIVIRUS ANTISPAM DKIM WEBMAIL SSL LETSENCRYPT CATCHALL ACCOUNTS U_DISK SUSPENDED TIME DATE)
    write_kv_config_file
    unset system
    unset known_keys

    # MAIL ACCOUNTS
    system="mail_accounts"
    known_keys=(ACCOUNT ALIAS AUTOREPLY FWD FWD_ONLY MD5 QUOTA U_DISK SUSPENDED TIME DATE)
    write_kv_config_file
    unset system
    unset known_keys
}

# Update list of known keys for user.conf files
function syshealth_update_user_config_format() {

    # USER CONFIGURATION
    # Create array of known keys in configuration file
    system="user"
    known_keys=(NAME PACKAGE CONTACT CRON_REPORTS MD5 RKEY TWOFA QRCODE PHPCLI ROLE SUSPENDED SUSPENDED_USERS SUSPENDED_WEB SUSPENDED_DNS SUSPENDED_MAIL SUSPENDED_DB SUSPENDED_CRON IP_AVAIL IP_OWNED U_USERS U_DISK U_DISK_DIRS U_DISK_WEB U_DISK_MAIL U_DISK_DB U_BANDWIDTH U_WEB_DOMAINS U_WEB_SSL U_WEB_ALIASES U_DNS_DOMAINS U_DNS_RECORDS U_MAIL_DKIM U_MAIL_DKIM U_MAIL_ACCOUNTS U_MAIL_DOMAINS U_MAIL_SSL U_DATABASES U_CRON_JOBS U_BACKUPS LANGUAGE NOTIFICATIONS TIME DATE)
    write_kv_config_file
    unset system
    unset known_keys

    # CRON JOB CONFIGURATION
    # Create array of known keys in configuration file
    system="cron"
    known_keys=(JOB MIN HOUR DAY MONTH WDAY CMD SUSPENDED TIME DATE)
    write_kv_config_file
    unset system
    unset known_keys
}

# Update list of known keys for db.conf files
function syshealth_update_db_config_format() {

    # DATABASE CONFIGURATION
    # Create array of known keys in configuration file
    system="db"
    known_keys=(DB DBUSER MD5 HOST TYPE CHARSET U_DISK SUSPENDED TIME DATE)
    write_kv_config_file
    unset system
    unset known_keys
}

# Update list of known keys for ip.conf files
function syshealth_update_ip_config_format() {

    # IP ADDRESS
    # Create array of known keys in configuration file
    system="ip"
    known_keys=(OWNER STATUS NAME U_SYS_USERS U_WEB_DOMAINS INTERFACE NETMASK NAT HELO TIME DATE)
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

function syshealth_update_system_config_format() {
    # SYSTEM CONFIGURATION
    # Create array of known keys in configuration file
    system="system"
    known_keys=(ANTISPAM_SYSTEM ANTIVIRUS_SYSTEM API_ALLOWED_IP API BACKEND_PORT BACKUP_GZIP BACKUP_MODE BACKUP_SYSTEM CRON_SYSTEM DB_PMA_ALIAS DB_SYSTEM DISK_QUOTA DNS_SYSTEM ENFORCE_SUBDOMAIN_OWNERSHIP FILE_MANAGER FIREWALL_EXTENSION FIREWALL_SYSTEM FTP_SYSTEM IMAP_SYSTEM INACTIVE_SESSION_TIMEOUT LANGUAGE LOGIN_STYLE MAIL_SYSTEM PROXY_PORT PROXY_SSL_PORT PROXY_SYSTEM RELEASE_BRANCH STATS_SYSTEM THEME UPDATE_HOSTNAME_SSL UPGRADE_SEND_EMAIL UPGRADE_SEND_EMAIL_LOG WEB_BACKEND WEBMAIL_ALIAS WEBMAIL_SYSTEM WEB_PORT WEB_RGROUPS WEB_SSL WEB_SSL_PORT WEB_SYSTEM VERSION)
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

# Repair System Configuration
# Adds missing variables to $HESTIA/conf/hestia.conf with safe default values
function syshealth_repair_system_config() {
    # Release branch
    if [ -z "$RELEASE_BRANCH" ]; then
        echo "[ ! ] Adding missing variable to hestia.conf: RELEASE_BRANCH ('release')"
        $BIN/v-change-sys-config-value 'RELEASE_BRANCH' 'release'
    fi

    # Webmail alias
    if [ ! -z "$IMAP_SYSTEM" ]; then
        if [ -z "$WEBMAIL_ALIAS" ]; then
            echo "[ ! ] Adding missing variable to hestia.conf: WEBMAIL_ALIAS ('webmail')"
            $BIN/v-change-sys-config-value 'WEBMAIL_ALIAS' 'webmail'
        fi
    fi

    # phpMyAdmin/phpPgAdmin alias
    if [ ! -z "$DB_SYSTEM" ]; then
        if [ "$DB_SYSTEM" = "mysql" ]; then
            if [ -z "$DB_PMA_ALIAS" ]; then 
                echo "[ ! ] Adding missing variable to hestia.conf: DB_PMA_ALIAS ('phpmyadmin)"
                $BIN/v-change-sys-config-value 'DB_PMA_ALIAS' 'phpmyadmin'
            fi
        fi
        if [ "$DB_SYSTEM" = "pgsql" ]; then
            if [ -z "$DB_PGA_ALIAS" ]; then 
                echo "[ ! ] Adding missing variable to hestia.conf: DB_PGA_ALIAS ('phppgadmin')"
                $BIN/v-change-sys-config-value 'DB_PGA_ALIAS' 'phppgadmin'
            fi
        fi
    fi

    # Backup compression level
    if [ -z "$BACKUP_GZIP" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: BACKUP_GZIP ('4')"
        $BIN/v-change-sys-config-value 'BACKUP_GZIP' '4'
    fi

    # Theme
    if [ -z "$THEME" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: THEME ('dark')"
        $BIN/v-change-sys-config-value 'THEME' 'dark'
    fi

    # Default language
    if [ -z "$LANGUAGE" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: LANGUAGE ('en')"
        $BIN/v-change-sys-language 'en'
    fi

    # Disk Quota
    if [ -z "$DISK_QUOTA" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: DISK_QUOTA ('no')"
        $BIN/v-change-sys-config-value 'DISK_QUOTA' 'no'
    fi

    # CRON daemon
    if [ -z "$CRON_SYSTEM" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: CRON_SYSTEM ('cron')"
        $BIN/v-change-sys-config-value 'CRON_SYSTEM' 'cron'
    fi

    # Backend port
    if [ -z "$BACKEND_PORT" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: BACKEND_PORT ('8083')"
        $BIN/v-change-sys-port '8083' >/dev/null 2>&1
    fi

    # Upgrade: Send email notification
    if [ -z "$UPGRADE_SEND_EMAIL" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: UPGRADE_SEND_EMAIL ('true')"
        $BIN/v-change-sys-config-value 'UPGRADE_SEND_EMAIL' 'true'
    fi

    # Upgrade: Send email notification
    if [ -z "$UPGRADE_SEND_EMAIL_LOG" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: UPGRADE_SEND_EMAIL_LOG ('false')"
        $BIN/v-change-sys-config-value 'UPGRADE_SEND_EMAIL_LOG' 'false'
    fi

    # File Manager
    if [ -z "$FILE_MANAGER" ]; then
        echo "[ ! ] Adding missing variable to hestia.conf: FILE_MANAGER ('true')"
        $BIN/v-add-sys-filemanager quiet
    fi
    
    # Support for ZSTD / GZIP Change
    if [ -z "$BACKUP_MODE" ]; then
        echo "[ ! ] Setting zstd backup compression type as default..."
        $BIN/v-change-sys-config-value "BACKUP_MODE" "zstd"
    fi
    
    # Login style switcher
    if [ -z "$LOGIN_STYLE" ]; then
        echo "[ ! ] Adding missing variable to hestia.conf: LOGIN_STYLE ('default')"
        $BIN/v-change-sys-config-value "LOGIN_STYLE" "default"
    fi
    
    # Webmail clients
    if [ -z "$WEBMAIL_SYSTEM" ]; then
        if [ -d "/var/lib/roundcube" ]; then 
            echo "[ ! ] Adding missing variable to hestia.conf: WEBMAIL_SYSTEM ('roundcube')"
            $BIN/v-change-sys-config-value "WEBMAIL_SYSTEM" "roundcube"
        else
            echo "[ ! ] Adding missing variable to hestia.conf: WEBMAIL_SYSTEM ('')"
            $BIN/v-change-sys-config-value "WEBMAIL_SYSTEM" ""
        fi
    fi

    # Inactive session timeout
    if [ -z "$INACTIVE_SESSION_TIMEOUT" ]; then
        echo "[ ! ] Adding missing variable to hestia.conf: INACTIVE_SESSION_TIMEOUT ('60')"
        $BIN/v-change-sys-config-value "INACTIVE_SESSION_TIMEOUT" "60"
    fi

    # Enforce subdomain ownership
    if [ -z "$ENFORCE_SUBDOMAIN_OWNERSHIP" ]; then
        echo "[ ! ] Adding missing variable to hestia.conf: ENFORCE_SUBDOMAIN_OWNERSHIP ('yes')"
        $BIN/v-change-sys-config-value "ENFORCE_SUBDOMAIN_OWNERSHIP" "yes"
    fi

    # API access allowed IP's
    if [ "$API" = "yes" ]; then
        check_api_key=$(grep "API_ALLOWED_IP" $HESTIA/conf/hestia.conf)
        if [ -z "$check_api_key" ]; then
            if [ -z "$API_ALLOWED_IP" ]; then
                echo "[ ! ] Adding missing variable to hestia.conf: API_ALLOWED_IP ('allow-all')"        
                $BIN/v-change-sys-config-value "API_ALLOWED_IP" "allow-all"
            fi
        fi
    fi
}
