#!/bin/sh

# Hestia Control Panel upgrade script for target version 1.3.0

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ "$FTP_SYSTEM" == "proftpd" ]; then
    if [ -e  /etc/proftpd/proftpd.conf ]; then
        rm /etc/proftpd/proftpd.conf
    fi
    if [ -e  /etc/proftpd/tlss.conf ]; then
        rm /etc/proftpd/tls.conf
    fi
    
    cp -f $HESTIA_INSTALL_DIR/proftpd/proftpd.conf /etc/proftpd/
    cp -f $HESTIA_INSTALL_DIR/proftpd/tls.conf /etc/proftpd/
    
fi