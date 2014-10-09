#!/bin/bash

source /etc/profile.d/vesta.sh
if [ ! -e "$VESTA/data/firewall" ]; then
    mkdir -p $VESTA/data/firewall
    chmod 770 $VESTA/data/firewall

    cp $VESTA/install/rhel/firewall/* \
        $VESTA/data/firewall/
    chmod 660 $VESTA/data/firewall/*

    source $VESTA/conf/vesta.conf
    if [ -z "$FIREWALL_SYSTEM" ]; then
        echo "FIREWALL_SYSTEM='iptables'" \
            >> $VESTA/conf/vesta.conf
    fi
fi
