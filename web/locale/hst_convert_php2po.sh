#!/bin/bash
if [ ! -e /usr/bin/xgettext ]; then 
    echo " **********************************************************"
    echo " * Unable to find xgettext please install gettext package *"
    echo " **********************************************************"
    exit 3;
fi

lang=${1}

if [ -z $lang ]; then 
    echo "No Language defined"
    exit 3;
fi

# Tempory delete the old file just for testing 
rm -f -r ./$lang

if [ ! -d ./$lang/LC_MESSAGES/ ]; then 
    mkdir ./$lang/
    mkdir ./$lang/LC_MESSAGES/
    
    msginit -i ./hestiacp.pot --locale=$lang --no-translator
    mv ./$lang.po ./$lang/LC_MESSAGES/hestiacp.po;
fi

if [ -e ../inc/i18n/$lang.php ]; then 
    while read f
    do
        b=$(echo "$f" |cut -f 2 -d \');
        c=$(echo "$f" |cut -f 4 -d \');
        if [ ! -z "$c" ]; then 
            c=$(echo $c | sed -e 's/\"/\\\"/g');
            #c=$(echo $c | sed -e 's/\\/\\\\/g');
            echo "$b -> $c"
            # locate b in msgid  and replace the next line 
            #echo "msgid \"$b\"";
            sed -i "/msgid \"$b\"/{n;s/msgstr \".*\"/msgstr \"$c\"/}" ./$lang/LC_MESSAGES/hestiacp.po

            
#            rm ./$lang/LC_MESSAGES/hestiacp.po
#            mv ./$lang/LC_MESSAGES/hestiacp.po.bak ./$lang/LC_MESSAGES/hestiacp.po
        fi
    done <  ../inc/i18n/$lang.php
fi