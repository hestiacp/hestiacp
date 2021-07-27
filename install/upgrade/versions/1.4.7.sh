#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.7

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ ! -z "$DB_PGA_ALIAS" ]; then 
    $HESTIA/bin/v-change-sys-db-alias 'pga' $DB_PGA_ALIAS
fi