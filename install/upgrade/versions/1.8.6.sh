#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.8.6

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

# Modify Exim conf file (/etc/exim4/exim4.conf.template) to advertise AUTH only for localhost and TLS
# connections, so we avoid that users send their passwords as clear text over the net.
if [ "$MAIL_SYSTEM" = 'exim4' ]; then
        if ! grep -qw '^auth_advertise_hosts =' '/etc/exim4/exim4.conf.template'; then
                echo '[ * ] Enable auth advertise for Exim only for localhost and TLS connections'
                sed -i '/^tls_require_ciphers\s=\s.*/a auth_advertise_hosts = localhost : ${if eq{$tls_in_cipher}{}{}{*}}' '/etc/exim4/exim4.conf.template'
                "$BIN"/v-restart-mail
        fi
fi
