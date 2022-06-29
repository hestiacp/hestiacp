#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.6.2

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

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'no'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'no'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'

alc=$(cat exim4.conf.template | grep '${lookup{${domain:$authenticated_id}}dsearch{/etc/exim4/domains/}}/limits');
if [ -z "$alc" ]; then
    sed -i 's| set acl_c_msg_limit|#string1\n#replaceme|'  /etc/exim4/exim4.conf.template
    sed -i '/#replaceme/d' /etc/exim4/exim4.conf.template
    sed -i 's|#string1|set acl_c_msg_limit  = \${if exists{/etc/exim4/domains/\${lookup{\${domain:\$authenticated_id}}dsearch{/etc/exim4/domains/}}/limits}{\${lookup {\$authenticated_id} lsearch{/etc/exim4/domains/\${lookup{${domain:\$authenticated_id}}dsearch{/etc/exim4/domains/}}/limits}{\$value}{\${readfile{/etc/exim4/limit.conf}}}}}{\${readfile{/etc/exim4/limit.conf}}} }|g' /etc/exim4/exim4.conf.template
fi