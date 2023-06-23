#!/bin/bash
# Changing public_html permission

user="$1"
domain="$2"
ip="$3"
home_dir="$4"
docroot="$5"

chmod 755 "$docroot"

exit 0
