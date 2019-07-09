#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.0.3

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Update webmail templates to rebuild all mail domains.
if [ ! -z "$IMAP_SYSTEM" ]; then
    echo "(*) Update and rebuild mail domains..."
    $BIN/v-update-mail-templates > /dev/null 2>&1
fi