#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.2.3

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Update web templates for awstats
echo "[ ! ] Updating default web domain templates..."
$BIN/v-update-web-templates
