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

# Upgrading Mail System
if [ "$MAIL_SYSTEM" == "exim4" ]; then
    if ! grep -q "send_via_unauthenticated_smtp_relay" /etc/exim4/exim4.conf.template; then

        echo '[ * ] Enabling SMTP relay support...'
        # Add smtp relay router
        insert='send_via_unauthenticated_smtp_relay:\n  driver = manualroute\n  address_data = SMTP_RELAY_HOST:SMTP_RELAY_PORT\n  domains = !+local_domains\n  require_files = SMTP_RELAY_FILE\n  condition = ${if eq{SMTP_RELAY_USER}{}}\n  transport = remote_smtp\n  route_list = * ${extract{1}{:}{$address_data}}::${extract{2}{:}{$address_data}}\n  no_more\n  no_verify\n'

        line=$(expr $(sed -n '/begin routers/=' /etc/exim4/exim4.conf.template) + 2)
        sed -i "${line}i $insert" /etc/exim4/exim4.conf.template
    fi
fi
