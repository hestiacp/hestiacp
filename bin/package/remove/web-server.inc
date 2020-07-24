#!/bin/bash

hestia_package_remove() {
    echo "Removing web server package..."

    hestia module remove nginx
    hestia module remove apache
    hestia module remove php
    hestia module remove awstats
}
