#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.5.9

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
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
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# shellcheck source=/usr/local/hestia/func/db.sh
source $HESTIA/func/db.sh

if [ -n "$(echo $DB_SYSTEM | grep -w mysql)" ]; then
    mysql_connect 'localhost'
    version=$(mysql --defaults-file=/usr/local/hestia/conf/.mysql.localhost -e 'SELECT VERSION()')
    mysql_version=$(echo $version | grep -o -E '[0-9]*.[0-9].[0-9]+' | head -n1);
    mysql_version2=$(echo $mysql_version | grep -o -E '[0-9]*.[0-9]' | head -n1 );
    
    if [ "$mysql_version2" = "10.6" ]; then 
        test=$(mysql -e "select * from mysql.global_priv;" | grep root | grep unix_socket);
        if [ -z "$test" ]; then 
            echo "[ ! ] Updating MariaDB permissions to fix startup issue "
            mysql --defaults-file=/usr/local/hestia/conf/.mysql.localhost -e "UPDATE mysql.global_priv SET priv=json_set(priv, '$.password_last_changed', UNIX_TIMESTAMP(), '$.plugin', 'mysql_native_password', '$.authentication_string', 'invalid', '$.auth_or', json_array(json_object(), json_object('plugin', 'unix_socket'))) WHERE User='root';"
        fi
    fi
fi