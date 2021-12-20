#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.5.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### Pass trough information to the end user incase of a issue or problem    #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### to the upgrade email. Please add it using:                              #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

echo "[ * ] Apply changes for 1.5.2"

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'true'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

echo "[ * ] Replace apt key with keyring for extra securirty"

if [ ! -f "/usr/share/keyrings/nginx-keyring.gpg" ]; then 
    # Get Architecture
    architecture="$(uname -m)"
    case $architecture in 
    x86_64)
        ARCH="amd64"
        ;;
     aarch64)
        ARCH="arm64"
        ;;
    *)
        echo "Not supported"
    esac
        
    #Get OS details
    os=$(grep "^ID=" /etc/os-release | cut -f 2 -d '=')
    codename="$(lsb_release -s -c)"
    VERSION="$(lsb_release -s -r)"
    
    apt="/etc/apt/sources.list.d"
    
    if [ -f "$apt/nginx.list" ]; then
        rm  $apt/nginx.list 
        echo "   [ * ] NGINX"
        echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/nginx-keyring.gpg] https://nginx.org/packages/mainline/$VERSION/ $codename nginx" > $apt/nginx.list
        curl -s  https://nginx.org/keys/nginx_signing.key | gpg --dearmor | tee /usr/share/keyrings/nginx-keyring.gpg >/dev/null 2>&1
    fi
    if [ "$os" = "debian" ]; then
        if [ -f "$apt/php.list" ]; then
            rm  $apt/php.list 
            echo "   [ * ] PHP"
            echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/sury-keyring.gpg] https://packages.sury.org/php/ $codename main" > $apt/php.list
            curl -s  https://packages.sury.org/php/apt.gpg | gpg --dearmor | tee /usr/share/keyrings/sury-keyring.gpg >/dev/null 2>&1
        fi
        if [ -f "$apt/apache2.list" ]; then
            rm  $apt/apache2.list 
            echo "   [ * ] Apache2"
            echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/apache2-keyring.gpg] https://packages.sury.org/apache2/ $codename main" > $apt/apache2.list
            curl -s https://packages.sury.org/apache2/apt.gpg | gpg --dearmor | tee /usr/share/keyrings/apache2-keyring.gpg >/dev/null 2>&1
        fi
    fi
    if [ -f "$apt/mysql.list" ]; then
        rm  $apt/mysql.list 
        echo "   [ * ] MariaDB"
        echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/mariadb-keyring.gpg] https://mirror.mva-n.net/mariadb/repo/$mariadb_v/$VERSION $codename main" > $apt/mariadb.list
        curl -s https://mariadb.org/mariadb_release_signing_key.asc | gpg --dearmor | tee /usr/share/keyrings/mariadb-keyring.gpg >/dev/null 2>&1
    fi
    if [ -f "$apt/hestia.list" ]; then
        rm  $apt/hestia.list 
        echo "   [ * ] hestia"
        echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/hestia-keyring.gpg] https://$RHOST/ $codename main" > $apt/hestia.list
        gpg --no-default-keyring --keyring /usr/share/keyrings/hestia-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys A189E93654F0B0E5 >/dev/null 2>&1
        apt-key del A189E93654F0B0E5
    fi
    if [ -f "$apt/postgresql.list" ]; then
        rm  $apt/postgresql.list 
        echo "[ * ] PostgreSQL"
        echo "deb [arch=$ARCH signed-by=/usr/share/keyrings/postgresql-keyring.gpg] https://apt.postgresql.org/pub/repos/apt/ $codename-pgdg main" > $apt/postgresql.list
        curl -s https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor | tee /usr/share/keyrings/postgresql-keyring.gpg >/dev/null 2>&1
    fi
    
fi