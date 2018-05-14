#!/bin/bash

# Locate roundcube directory
if [ -d '/etc/roundcube' ]; then
    rc_dir='/etc/roundcube'
fi
if [ -d '/etc/roundcubemail' ]; then
    rc_dir='/etc/roundcubemail'
fi

if [ -z "$rc_dir" ]; then
    exit
fi

# Check for eval
cd $rc_dir
for config in $(grep eval *.php |cut -f1 -d:); do
    sed -i '/eval/d' $config
done
