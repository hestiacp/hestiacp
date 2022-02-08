#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.15

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

if [ -n "$DB_PMA_ALIAS" ]; then
    $HESTIA/bin/v-change-sys-db-alias 'pma' "$DB_PMA_ALIAS"
fi

exim_version=$(exim4 --version |  head -1 | awk  '{print $3}' | cut -f -2 -d .);
if [ "$exim_version" = "4.94" ]; then
    echo "[ ! ] Updating Exim configuration..."
    if [ -f "/etc/exim4/exim4.conf.template" ]; then 
        sed -i 's|file = /etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/autoreply.${lookup{$local_part}dsearch{${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/mail/${lookup{$domain}dsearch{/etc/exim4/domains/}}}}.msg|file = /etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/autoreply.${extract{1}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/accounts}}}}.msg|g'  /etc/exim4/exim4.conf.template
        sed -i 's| from = "${lookup{$local_part}dsearch{${extract{5}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/passwd}}}}/mail/${lookup{$domain}dsearch{/etc/exim4/domains/}}}}@${lookup{$domain}dsearch{/etc/exim4/domains/}}"| from = "${extract{1}{:}{${lookup{$local_part}lsearch{/etc/exim4/domains/${lookup{$domain}dsearch{/etc/exim4/domains/}}/accounts}}}}@${lookup{$domain}dsearch{/etc/exim4/domains/}}"|g' /etc/exim4/exim4.conf.template
    fi
fi


