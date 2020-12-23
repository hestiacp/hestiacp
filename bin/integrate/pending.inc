#!/bin/bash

hestia_integrate_pending() {
    if [ -f $HESTIA/data/queue/integrate ]; then
        integrate_mail=0
        integrate_web=0

        for PKG in $(cat $HESTIA/data/queue/integrate | sort | uniq)
        do
            case $PKG in
                exim|dovecot|spamassassin|clamav|mail-server)
                    integrate_mail=1
                    ;;
                apache|httpd|nginx|php|php-fpm|phpmyadmin|pgmyadmin|awstats|webalizer|web-server)
                    integrate_web=1
                    ;;
                *)
                    # Unrecognized package name... integrate everything.
                    integrate_mail=1
                    integrate_web=1
                    ;;
            esac
        done
        
        if [ $integrate_web -gt 0 ]; then
            hestia package web-server integrate
        fi

        if [ $integrate_mail -gt 0 ]; then
            hestia package mail-server integrate
        fi

        rm $HESTIA/data/queue/integrate
    fi
}
