#!/bin/sh

hestia_package_remove() {
    echo "Removing mail server package..."

    $BIN/hestia module remove exim
    $BIN/hestia module remove dovecot
    $BIN/hestia module remove clamav
    $BIN/hestia module remove spamassassin
}
