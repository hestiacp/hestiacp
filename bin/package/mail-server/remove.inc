#!/bin/bash

hestia_package_mail-server_remove() {
    echo "Removing mail server package..."

    hestia module remove exim --no-integrate
    hestia module remove dovecot
    hestia module remove clamav
    hestia module remove spamassassin
}
