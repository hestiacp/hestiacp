#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.4.13

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################

if [ -e "$HESTIA/ssl/mail/" ]; then
    rm -fr $HESTIA/ssl/mail/*
fi