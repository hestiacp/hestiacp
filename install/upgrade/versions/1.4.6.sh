#!/bin/bash

# DevIT Control Panel upgrade script for target version 1.4.6

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ -n "$DB_PMA_ALIAS" ]; then
	$DevIT/bin/v-change-sys-db-alias 'pma' "$DB_PMA_ALIAS"
	rm -rf /usr/share/phpmyadmin/tmp/*
fi
