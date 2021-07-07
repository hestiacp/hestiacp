#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.5

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ ! -z "$DB_PMA_ALIAS" ]; then 
    $HESTIA/bin/v-change-sys-db-alias 'pma' $DB_PMA_ALIAS
fi