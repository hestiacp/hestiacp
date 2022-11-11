#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.12

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

exim_version=$(exim4 --version |  head -1 | awk  '{print $3}' | cut -f -2 -d .);
if [ "$exim_version" = "4.94" ]; then
	echo "[ ! ] Fixing issue with Exim 4.94 (#2087 - Unable send email)..."
	if [ -f "/etc/exim4/exim4.conf.template" ]; then
		sed -i 's|OUTGOING_IP = /etc/exim4/domains/$sender_address_domain/ip|OUTGOING_IP = /etc/exim4/domains/${lookup{$sender_address_domain}dsearch{/etc/exim4/domains}}/ip|g' /etc/exim4/exim4.conf.template
		sed -i 's|SMTP_RELAY_FILE = ${if exists{/etc/exim4/domains/${sender_address_domain}/smtp_relay.conf}{/etc/exim4/domains/$sender_address_domain/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}|SMTP_RELAY_FILE = ${if exists{/etc/exim4/domains/${lookup{$sender_address_domain}dsearch{/etc/exim4/domains}}/smtp_relay.conf}{/etc/exim4/domains/${lookup{$sender_address_domain}dsearch{/etc/exim4/domains}}/smtp_relay.conf}{/etc/exim4/smtp_relay.conf}}|g' /etc/exim4/exim4.conf.template
	fi
fi