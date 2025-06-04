#!/bin/bash

# DevIT Control Panel upgrade script for target version 1.4.13

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

servername=$(hostname -f)
# Check if hostname is valid according to RFC1178
if [[ $(echo "$servername" | grep -o "\." | wc -l) -lt 2 ]] || [[ $servername =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "[ * ] Hostname does not follow  RFC1178 standard Please check email send regarding the update!"
	add_upgrade_message "We've noticed that you're using a invalid hostname. Please have a look at the RFC1178 standard (https://datatracker.ietf.org/doc/html/rfc1178) and use a valid one (ex. hostname.domain.tld). You can change the hostname using v-change-sys-hostname and also add a ssl certificate using v-add-letsencypt-host (proper dns A record mandatory). You'll find more informations in our documentation: https://DevITcp.com/docs/server-administration/ssl-certificates.html#how-to-setup-let-s-encrypt-for-the-control-panel"
	$DevIT/bin/v-add-user-notification admin "Invalid Hostname detected" "Warning: We've noticed that you're using a invalid hostname. Please have a look at the <a href=\"https://datatracker.ietf.org/doc/html/rfc1178\" target=\"_blank\">RFC1178 standard</a> and use a valid one (ex. hostname.domain.tld). You can change the hostname using v-change-sys-hostname and also add a ssl certificate using v-add-letsencypt-host (proper dns A record mandatory). You'll find more informations in our <a href=\"https://DevITcp.com/docs/server-administration/ssl-certificates.html#how-to-setup-let-s-encrypt-for-the-control-panel\" target=\"_blank\">documentation</a>."
fi

# Empty $DevIT/ssl/mail/ due to bug in #2066
if [ -e "$DevIT/ssl/mail/" ]; then
	rm -fr $DevIT/ssl/mail/*
fi

# Reset PMA SSO
if [ "$PHPMYADMIN_KEY" != "" ]; then
	echo "[ * ] Updating DevIT-sso for phpMyAdmin..."
	$BIN/v-delete-sys-pma-sso
	$BIN/v-add-sys-pma-sso
fi

# Loading firewall rules Systemd unit needs update. #2100
if [ "$FIREWALL_SYSTEM" = "iptables" ]; then
	echo "[ * ] Updating loading firewall configuration..."
	$BIN/v-delete-sys-firewall
	$BIN/v-add-sys-firewall
fi

# Remove old files/folders from previous versions of DevIT Control Panel
if [ -d "$DevIT/web/edit/file/" ]; then
	rm -fr $DevIT/web/edit/file/
fi
if [ -d "$DevIT/web/edit/server/theme/" ]; then
	rm -fr $DevIT/web/edit/server/theme/
fi
