#!/bin/bash

if [ ! -x /usr/bin/xgettext ]; then
	echo " **********************************************************"
	echo " * Unable to find xgettext please install gettext package *"
	echo " **********************************************************"
	exit 3
fi

echo "[ * ] Move hestiacp.pot to hestiacp.pot.old"
mv hestiacp.pot hestiacp.pot.old
true > hestiacp.pot

echo "[ * ] Search *.php *.html and *.sh for php based gettext functions"
find ../.. \( -name '*.php' -o -name '*.html' -o -name '*.sh' \) | xgettext --output=hestiacp.pot --language=PHP --join-existing -f -

# Scan the description string for list updates page
while IFS= read -r string; do
	if ! grep -q "\"$string\"" hestiacp.pot; then
		echo -e "\n#: ../../bin/v-list-sys-hestia-updates:$(grep -n "$string" ../../bin/v-list-sys-hestia-updates | cut -d: -f1)\nmsgid \"$string\"\nmsgstr \"\"" >> hestiacp.pot
	fi
done < <(awk -F'DESCR=' '/data=".+ DESCR=[^"]/ {print $2}' ../../bin/v-list-sys-hestia-updates | cut -d\' -f2)

# Scan the description string for list server page
while IFS= read -r string; do
	if ! grep -q "\"$string\"" hestiacp.pot; then
		echo -e "\n#: ../../bin/v-list-sys-services:$(grep -n "$string" ../../bin/v-list-sys-services | cut -d: -f1)\nmsgid \"$string\"\nmsgstr \"\"" >> hestiacp.pot
	fi
done < <(awk -F'SYSTEM=' '/data=".+ SYSTEM=[^"]/ {print $2}' ../../bin/v-list-sys-services | cut -d\' -f2)

# Prevent only date change become a commit
if [ "$(diff hestiacp.pot hestiacp.pot.old | wc -l)" -gt 4 ]; then
	rm hestiacp.pot.old
else
	mv -f hestiacp.pot.old hestiacp.pot
fi
