#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.8

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

echo "[ * ] Configuring PHPMailer..."
$HESTIA/bin/v-add-sys-phpmailer quiet
