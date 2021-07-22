#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.8

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

matches=$(grep -o 'ENFORCE_SUBDOMAIN_OWNERSHIP' $HESTIA/conf/hestia.conf | wc -l);
if [ "$matches" > 1 ]; then
	echo "[ * ] Removing double matches ENFORCE_SUBDOMAIN_OWNERSHIP key"
	source $HESTIA/conf/hestia.conf
	sed -i "/ENFORCE_SUBDOMAIN_OWNERSHIP='$ENFORCE_SUBDOMAIN_OWNERSHIP'/d" $HESTIA/conf/hestia.conf
	$HESTIA/bin/v-change-sys-config-value "ENFORCE_SUBDOMAIN_OWNERSHIP" "$ENFORCE_SUBDOMAIN_OWNERSHIP"
fi