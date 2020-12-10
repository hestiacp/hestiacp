#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.1

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

# Remove duplicate values in U_SYS_USERS variable for ips
for ip in $(ls $HESTIA/data/ips/); do
    current_usr=$(grep "U_SYS_USERS=" $HESTIA/data/ips/$ip |cut -f 2 -d \')
    
    new_usr=$(echo "$current_usr" |\
        sed "s/,/\n/g"|\
        sort -u |\
        sed ':a;N;$!ba;s/\n/,/g')

    if [ ! -z "$new_usr" ]; then
        sed -i "s/U_SYS_USERS='$current_usr'/U_SYS_USERS='$new_usr'/g" $HESTIA/data/ips/$ip
    fi
done

