#!/bin/bash

hestia_package_web-server_remove() {
    echo "Removing web server package..."

    hestia module remove awstats --no-integrate
    #TODO: remove non-default PHP versions, only leave default for CLI?
    #hestia module remove php --no-integrate
    hestia module remove nginx --no-integrate
    hestia module remove apache --no-integrate
    
    # Integrate everything
    hestia package web-server integrate
}
