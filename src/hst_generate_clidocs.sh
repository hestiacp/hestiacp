#!/bin/bash

for file in /usr/local/hestia/bin/*; do
        echo "$file" >> ~/hestia_cli_help.txt
        [ -f "$file" ] && [ -x "$file" ] && "$file" >> ~/hestia_cli_help.txt
done;

sed -i 's\/usr/local/hestia/bin/\\' ~/hestia_cli_help.txt