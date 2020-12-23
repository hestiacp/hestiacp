#!/bin/bash

hestia_package_web-server_remove() {
    echo "Removing web server package..."

    hestia module remove awstats
    #TODO: remove non-default PHP versions, only leave default for CLI?
    #hestia module remove php
    hestia module remove nginx
    hestia module remove apache
    
    # Integrate everything
    osal_enqueue_integrate web-server
}
