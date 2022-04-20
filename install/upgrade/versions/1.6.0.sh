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
    sed -i '115,250 s/ratelimit             = 200 \/ 1h \/ $authenticated_id/          set acl_c_msg_limit = \${if exists{\/etc\/exim4\/domains\/\${lookup{\$sender_address_domain}dsearch{\/etc\/exim4\/domains\/}}\/limits} {\${extract{1}{:}{\${lookup{\$sender_address_local_part@\$sender_address_domain}lsearch{\/etc\/exim4\/domains\/\${lookup{\$sender_address_domain}dsearch{\/etc\/exim4\/domains\/}}\/limits}}}}} {\${readfile{\/etc\/exim4\/limit.conf}}} }\n ratelimit     = \$acl_c_msg_limit \/ 1h \/ strict\/ \$authenticated_idy/g' /etc/exim4/exim4.conf.template
    sed -i '115,250 s/warn    ratelimit     = 100 \/ 1h \/ strict \/ $authenticated_id/warn    ratelimit     = ${eval:$acl_c_msg_limit \/ 2} \/ 1h \/ strict \/ $authenticated_id/g' /etc/exim4/exim4.conf.template
    # Add missing limit.conf file
    cp $HESTIA_INSTALL_DIR/exim/limit.conf /etc/exim4/limit.conf
    cp $HESTIA_INSTALL_DIR/exim/system.filter /etc/exim4/system.filter
    
    acl=$(cat /etc/exim4/exim4.conf.template | grep "set acl_m3")
    if [ -z "$acl" ]; then
        echo "[ * ] Add support for optional rejecting spam"
        sed -i 's/ warn    set acl_m1    = no/ warn    set acl_m1    = no \n          set acl_m3    = no/g' /etc/exim4/exim4.conf.template
        sed -i 's|                          set acl_m1    = yes|              set acl_m1    = yes \n  warn    condition     = \${if exists {/etc/exim4/domains/\$domain/reject_spam}{yes}{no}} \n         set acl_m3    = yes|g' /etc/exim4/exim4.conf.template
        sed -i 's|         message        = SpamAssassin detected spam (from \$sender_address to \$recipients).|        message        = SpamAssassin detected spam (from $sender_address to $recipients).\n\n  # Deny spam at high score if spam score > SPAM_REJECT_SCORE and delete_spam is enabled\n  deny   message        = This message scored \$spam_score spam points\n          spam           = debian-spamd:true \n          condition      = \${if eq{\$acl_m3}{yes}{yes}{no}} \n          condition      = ${if >{$spam_score_int}{SPAM_REJECT_SCORE}{1}{0}}	|g' /etc/exim4/exim4.conf.template
    fi
    
    if ! grep -q "send_via_unauthenticated_smtp_relay" /etc/exim4/exim4.conf.template; then
       echo '[ * ] Enabling SMTP relay support...'
       # Add smtp relay router
       insert='send_via_unauthenticated_smtp_relay:\n  driver = manualroute\n  address_data = SMTP_RELAY_HOST:SMTP_RELAY_PORT\n  domains = !+local_domains\n  require_files = SMTP_RELAY_FILE\n  condition = ${if eq{SMTP_RELAY_USER}{}}\n  transport = remote_smtp\n  route_list = * ${extract{1}{:}{$address_data}}::${extract{2}{:}{$address_data}}\n  no_more\n  no_verify\n'
    
       line=$(expr $(sed -n '/begin routers/=' /etc/exim4/exim4.conf.template) + 2)
       sed -i "${line}i $insert" /etc/exim4/exim4.conf.template
    fi
fi

if [ -f "/etc/dovecot/conf.d/10-ssl.conf" ]; then
    sed -i 's|ssl_min_protocol = TLSv1.1|ssl_min_protocol = TLSv1.2|' /etc/dovecot/conf.d/10-ssl.conf
    if ! grep -q "!TLSv1.1" /etc/dovecot/conf.d/10-ssl.conf; then
        sed -i 's|ssl_protocols = !SSLv3 !TLSv1|ssl_protocols = !SSLv3 !TLSv1 !TLSv1.1|' /etc/dovecot/conf.d/10-ssl.conf
    fi
fi

# Adding LE autorenew cronjob if there are none
if [ -z "$(grep v-update-lets $HESTIA/data/users/admin/cron.conf)" ]; then
	min=$(generate_password '012345' '2')
	hour=$(generate_password '1234567' '1')
	command="sudo $BIN/v-update-letsencrypt-ssl"
	$BIN/v-add-cron-job 'admin' "$min" "$hour" '*' '*' '*' "$command"
fi

# Add apis if they don't exist
if [[ ! -d $HESTIA/data/api ]]; then
    cp -rf $HESTIA_INSTALL_DIR/api $HESTIA/data/
fi
