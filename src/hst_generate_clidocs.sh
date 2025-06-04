#!/bin/bash

for file in /usr/local/DevIT/bin/*; do
	echo "$file" >> ~/DevIT_cli_help.txt
	[ -f "$file" ] && [ -x "$file" ] && "$file" >> ~/DevIT_cli_help.txt
done

sed -i 's\/usr/local/DevIT/bin/\\' ~/DevIT_cli_help.txt
