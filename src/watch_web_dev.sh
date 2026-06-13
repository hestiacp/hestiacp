#!/bin/bash

sudo chown -Rv hestiaweb:hestiaweb /usr/local/hestia/web2/var

watch -n 1 "rsync -a -v --recursive --delete --exclude 'var' ../web2 /usr/local/hestia"
