#!/bin/bash

if [ -e "/etc/exim4/domains/" ]; then
    for domain in $(ls /etc/exim4/domains/); do
        domain_link=$(readlink /etc/exim4/domains/$domain)
        chown Debian-exim:mail $domain_link
        chown Debian-exim:mail /etc/exim4/domains/$domain/*
        chown dovecot:mail /etc/exim4/domains/$domain/passwd
    done
fi

exit
