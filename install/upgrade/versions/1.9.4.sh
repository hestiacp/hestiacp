#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.9.4

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
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

# Includes
# shellcheck source=/etc/hestiacp/hestia.conf
source /etc/hestiacp/hestia.conf
# shellcheck source=/usr/local/hestia/func/main.sh
source "$HESTIA/func/main.sh"

# Fix missing dependency if using proftpd on Ubuntu
source_conf "$HESTIA/conf/hestia.conf"
if [[ "$FTP_SYSTEM" = "proftpd" ]]; then
    if ! dpkg -l | grep -q '^ii.*proftpd-mod-crypto'; then
        echo "[+] Installing missing dependency proftpd-mod-crypto."
        if ! apt install -qq -y proftpd-mod-crypto &>/dev/null; then
            echo "Error installing package proftpd-mod-crypto."
            echo "Try installing it manually: apt install proftpd-mod-crypto"
        else
            "$BIN"/v-restart-ftp
        fi
    fi
fi
