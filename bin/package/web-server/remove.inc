#!/bin/bash

hestia_package_web-server_remove() {
    echo "Removing web server package..."

    hestia module remove php-fpm --no-integrate
    hestia module remove nginx --no-integrate
    hestia module remove apache --no-integrate
    hestia module remove awstats --no-integrate
    
    # Integrate everything
    hestia package web-server integrate
}
