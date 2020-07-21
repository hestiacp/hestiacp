#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.2.2

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Update template files to add warnings
# Backup current templates
cp -r -f $HESTIA/data/templates/* $HESTIA_BACKUP/templates/
echo "[ ! ] Updating default web domain templates..."
$BIN/v-update-web-templates
echo "[ ! ] Updating default mail domain templates..."
$BIN/v-update-mail-templates
echo "[ ! ] Updating default DNS zone templates..."
$BIN/v-update-dns-templates
