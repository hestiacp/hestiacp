#!/bin/bash
if [ ! -e /usr/bin/xgettext ]; then
	echo " **********************************************************"
	echo " * Unable to find xgettext please install gettext package *"
	echo " **********************************************************"
	exit 3
fi

echo "[ * ] Move hestiacp.pot to Move hestiacp.pot.old"
mv hestiacp.pot hestiacp.pot.old
echo "" > hestiacp.pot

echo "[ * ] Search *.php *.html and *.sh for php based gettext functions"
find ../.. \( -name '*.php' -o -name '*.html' -o -name '*.sh' \) | xgettext --output=hestiacp.pot --language=PHP --join-existing -f -
OLDIFS=$IFS
IFS=$'\n'
# Scan the description string for list updates page
for string in $(awk -F'DESCR=' '/data=".+ DESCR=[^"]/ {print $2}' ../../bin/v-list-sys-hestia-updates | cut -d\' -f2); do
	if [ -z "$(grep "\"$string\"" hestiacp.pot)" ]; then
		echo -e "\n#: ../../bin/v-list-sys-hestia-updates:"$(grep -n "$string" ../../bin/v-list-sys-hestia-updates | cut -d: -f1)"\nmsgid \"$string\"\nmsgstr \"\"" >> hestiacp.pot
	fi
done
# Scan the description string for list server page
for string in $(awk -F'SYSTEM=' '/data=".+ SYSTEM=[^"]/ {print $2}' ../../bin/v-list-sys-services | cut -d\' -f2); do
	if [ -z "$(grep "\"$string\"" hestiacp.pot)" ]; then
		echo -e "\n#: ../../bin/v-list-sys-services:"$(grep -n "$string" ../../bin/v-list-sys-services | cut -d: -f1)"\nmsgid \"$string\"\nmsgstr \"\"" >> hestiacp.pot
	fi
done
IFS=$OLDIFS

# Prevent only date change become a commit
if [ $(diff hestiacp.pot hestiacp.pot.old | wc -l) != 2 ]; then
	rm hestiacp.pot
	mv hestiacp.pot.old hestiacp.pot
else
	rm hestiacp.pot.old
fi
