#!/bin/bash

if [ -e "/etc/sudoers.d/admin" ]; then
    sed -i "s/admin.*ALL=(ALL).*/# sudo is limited to vesta scripts/" \
        /etc/sudoers.d/admin
fi
