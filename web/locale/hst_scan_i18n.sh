#!/bin/bash

if [ ! -x /usr/bin/xgettext ]; then
	echo " **********************************************************"
	echo " * Unable to find xgettext please install gettext package *"
	echo " **********************************************************"
	exit 3
fi

echo "[ * ] Move DevITcp.pot to DevITcp.pot.old"
mv DevITcp.pot DevITcp.pot.old
true > DevITcp.pot

echo "[ * ] Search *.php *.html and *.sh for php based gettext functions"
find ../.. \( -name '*.php' -o -name '*.html' -o -name '*.sh' \) | xgettext --output=DevITcp.pot --language=PHP --join-existing -f -

# Scan the description string for list updates page
while IFS= read -r string; do
	if ! grep -q "\"$string\"" DevITcp.pot; then
		echo -e "\n#: ../../bin/v-list-sys-DevIT-updates:$(grep -n "$string" ../../bin/v-list-sys-DevIT-updates | cut -d: -f1)\nmsgid \"$string\"\nmsgstr \"\"" >> DevITcp.pot
	fi
done < <(awk -F'DESCR=' '/data=".+ DESCR=[^"]/ {print $2}' ../../bin/v-list-sys-DevIT-updates | cut -d\' -f2)

# Scan the description string for list server page
while IFS= read -r string; do
	if ! grep -q "\"$string\"" DevITcp.pot; then
		echo -e "\n#: ../../bin/v-list-sys-services:$(grep -n "$string" ../../bin/v-list-sys-services | cut -d: -f1)\nmsgid \"$string\"\nmsgstr \"\"" >> DevITcp.pot
	fi
done < <(awk -F'SYSTEM=' '/data=".+ SYSTEM=[^"]/ {print $2}' ../../bin/v-list-sys-services | cut -d\' -f2)

# Prevent only date change become a commit
if [ "$(diff DevITcp.pot DevITcp.pot.old | wc -l)" -gt 4 ]; then
	rm DevITcp.pot.old
else
	mv -f DevITcp.pot.old DevITcp.pot
fi
