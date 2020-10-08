#!/bin/bash
if [ ! -e /usr/bin/xgettext ]; then 
    echo " **********************************************************"
    echo " * Unable to find xgettext please install gettext package *"
    echo " **********************************************************"
    exit 3;
fi

delete=0

echo "[ * ] Remove old hestiacp.pot and generate new one"
rm hestiacp.pot
echo "" > hestiacp.pot
find ../.. \( -name '*.php' -o -name '*.html' -o -name '*.sh' \) | xgettext --output=hestiacp.pot --language=PHP --join-existing -f -

echo "[ * ] Scan language folders"
languages=$(ls -d $HESTIA/web/locale/*/ |awk -F'/' '{print $(NF-1)}');
echo "[ * ] Update hestiacp.pot with new files"
for lang in $languages; do
    if [ -e "$HESTIA/web/locale/$lang/LC_MESSAGES/hestiacp.po" ]; then 
        echo "[ * ] Update $lang "
        mv $HESTIA/web/locale/$lang/LC_MESSAGES/hestiacp.po $HESTIA/web/locale/$lang/LC_MESSAGES/hestiacp.po.bak
        msgmerge --verbose "$HESTIA/web/locale/$lang/LC_MESSAGES/hestiacp.po.bak" "$HESTIA/web/locale/hestiacp.pot" > $HESTIA/web/locale/$lang/LC_MESSAGES/hestiacp.po
        rm $HESTIA/web/locale/$lang/LC_MESSAGES/hestiacp.po.bak
    fi
done
echo "[ ! ] Update complete"
