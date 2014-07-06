#!/bin/bash

# Fix MySQL autostart for Ubuntu 14.04
if [ -e "/etc/issue" ]; then
    release=$(head -n 1 /etc/issue | cut -f 2 -d ' ' )
    if [ "$release" = '14.04' ]; then
        update-rc.d mysql disable
    fi
fi

exit
