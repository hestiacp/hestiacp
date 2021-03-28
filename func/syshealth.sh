#!/bin/bash

# Hestia Control Panel - System Health Check Function Library

# Repair System Configuration
# Adds missing variables to $HESTIA/conf/hestia.conf with safe default values
function syshealth_repair_system_config () {
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
                echo "[ ! ] Adding missing variable to hestia.conf: DB_PMA_ALIAS ('phpMyAdmin')"
                $BIN/v-change-sys-config-value 'DB_PMA_ALIAS' 'phpMyAdmin'
            fi
        fi
        if [ "$DB_SYSTEM" = "pgsql" ]; then
            if [ -z "$DB_PGA_ALIAS" ]; then 
                echo "[ ! ] Adding missing variable to hestia.conf: DB_PGA_ALIAS ('phpPgAdmin')"
                $BIN/v-change-sys-config-value 'DB_PGA_ALIAS' 'phpPgAdmin'
            fi
        fi
    fi

    # Backup compression level
    if [ -z "$BACKUP_GZIP" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: BACKUP_GZIP ('9')"
        $BIN/v-change-sys-config-value 'BACKUP_GZIP' '9'
    fi

    # Theme
    if [ -z "$THEME" ]; then 
        echo "[ ! ] Adding missing variable to hestia.conf: THEME ('default')"
        $BIN/v-change-sys-config-value 'THEME' 'default'
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
        echo "[ ! ] File Manager is enabled but not installed, repairing components..."
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
