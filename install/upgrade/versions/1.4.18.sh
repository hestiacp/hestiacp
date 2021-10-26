#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.18

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

if [ -n "$DB_PMA_ALIAS" ]; then
    $HESTIA/bin/v-change-sys-db-alias 'pma' "$DB_PMA_ALIAS"
fi

if [ -n "$MAIL_SYSTEM" ]; then

    if [ -f "/etc/exim4/exim4.conf.template" ]; then
        sed -i 's/^smtp_active_hostname = \${if exists {\/etc\/exim4\/mailhelo\.conf}{\${lookup{\$interface_address}lsearch{\/etc\/exim4\/mailhelo\.conf}{\$value}{\$primary_hostname}}}{\$primary_hostname}}$/smtp_active_hostname = \${lookup dnsdb{ptr=\$interface_address}{\$value}{\$primary_hostname}}/' /etc/exim4/exim4.conf.template
    
        sed -i 's/^  helo_data = \${if exists {\/etc\/exim4\/mailhelo\.conf}{\${lookup{\$sending_ip_address}lsearch{\/etc\/exim4\/mailhelo\.conf}{\$value}{\$primary_hostname}}}{\$primary_hostname}}$/  helo_data = \${lookup dnsdb{ptr=\$sending_ip_address}{\$value}{\$primary_hostname}}/' /etc/exim4/exim4.conf.template
    fi
    
    # Clean up legacy mailhelo file
    rm -f /etc/${MAIL_SYSTEM}/mailhelo.conf
    
    # Clean up legacy ip variable
    for ip in $($BIN/v-list-sys-ips plain | cut -f1); do
        sed '/^HELO/d' $HESTIA/data/ips/$ip;
    done
fi

if [ -L "/var/log/hestia" ]; then
    echo "[ ! ] Move /usr/local/hestia/log/* to /var/log/hestia/"
    rm /var/log/hestia
    cp $HESTIA/log/* /var/log/hestia
    rm -rf $HESTIA/log/
   ln -s /var/log/hestia $HESTIA/log
fi