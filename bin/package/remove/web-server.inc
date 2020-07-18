#!/bin/sh

hestia_package_remove() {
    echo "Removing web server package..."

    $BIN/hestia module remove nginx
    $BIN/hestia module remove apache
    $BIN/hestia module remove php
    $BIN/hestia module remove awstats
}
