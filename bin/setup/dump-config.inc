#!/bin/bash

hestia_setup_dump-config() {
    if [ -f $HESTIA/conf/hestia.conf ]; then
        cat $HESTIA/conf/hestia.conf
    else
        echo "No config found. Use 'hestia setup init'"
    fi
}