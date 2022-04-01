#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.6.0

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
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'true'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

if [ "$MAIL_SYSTEM" = "exim4" ]; then 
    echo "[ * ] Update exim4 config to support rate limits"
    # Upgrade config exim for custom limits
    
    exim_version=$(exim4 --version |  head -1 | awk  '{print $3}' | cut -f -2 -d .);
    if [ "$exim_version" = "4.94" ] || [ "$exim_version" = "4.95" ]; then
        #For Debian 11 and Ubuntu 22.04 
        sed -i '115,250 s/ratelimit             = 200 \/ 1h \/ $authenticated_id/          set acl_c_msg_limit = ${if exists{\/etc\/exim4\/domains\/${lookup{$sender_address_domain}dsearch{\/etc\/exim4\/domains\/}}\/limits\/${extract{1}{:}{${lookup{$sender_address_local_part}lsearch{\/etc\/exim4\/domains\/${lookup{$sender_address_domain}dsearch{\/etc\/exim4\/domains\/}}\/accounts}}}}} {${readfile{\/etc\/exim4\/domains\/${lookup{$sender_address_domain}dsearch{\/etc\/exim4\/domains\/}}\/limits\/${extract{1}{:}{${lookup{$sender_address_local_part}lsearch{\/etc\/exim4\/domains\/${lookup{$sender_address_domain}dsearch{\/etc\/exim4\/domains\/}}\/accounts}}}}}}} {${readfile{\/etc\/exim4\/limit.conf}}} } \n  ratelimit     = $acl_c_msg_limit \/ 1h \/ strict\/ $authenticated_id/g' /etc/exim4/exim4.conf.template
        sed -i '115,250 s/warn    ratelimit     = 100 \/ 1h \/ strict \/ $authenticated_id/warn    ratelimit     = ${eval:$acl_c_msg_limit \/ 2} \/ 1h \/ strict \/ $authenticated_id/g' /etc/exim4/exim4.conf.template
    else
        # And the other 
        sed -i '115,250 s/ratelimit             = 200 \/ 1h \/ $authenticated_id/ set acl_c_msg_limit = ${if exists{\/etc\/exim4\/domains\/$sender_address_domain\/limits\/$sender_address} {${readfile{\/etc\/exim4\/domains\/$sender_address_domain\/limits\/$sender_address_local_part}}} {${readfile{\/etc\/exim4\/limit.conf}}} } \n ratelimit     = $acl_c_msg_limit \/ 1h \/ strict\/ $authenticated_id/g' /etc/exim4/exim4.conf.template
        sed -i '115,250 s/warn    ratelimit     = 100 \/ 1h \/ strict \/ $authenticated_id/warn    ratelimit     = ${eval:$acl_c_msg_limit \/ 2} \/ 1h \/ strict \/ $authenticated_id/g' /etc/exim4/exim4.conf.template
    fi
    # Add missing limit.conf file
    cp $HESTIA_INSTALL_DIR/exim/limit.conf /etc/exim4/limit.conf
fi

# Adding LE autorenew cronjob if there are none
if [ -z "$(grep v-update-lets $HESTIA/data/users/admin/cron.conf)" ]; then
	min=$(generate_password '012345' '2')
	hour=$(generate_password '1234567' '1')
	command="sudo $BIN/v-update-letsencrypt-ssl"
	$BIN/v-add-cron-job 'admin' "$min" "$hour" '*' '*' '*' "$command"
fi
