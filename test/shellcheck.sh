#!/bin/sh

# Note use sh and not bash! 

# To be used with in shellcheck and automated usage 
# Generate timestamp

# If logging specified, export shellcheck output to log
# Excluded codes
# SC2086 = SC2086: Double quote to prevent globbing and word splitting. - Keep it more readable please use them with v-xxx-commands when used user input might be not validated corrections and  whitespaces might cause a risk
#  SC2002: Useless cat. Consider 'cmd < file | ..' or 'cmd file | ..' instead.
# Check exit code directly with e.g. 'if mycmd;', not indirectly with $?. Might be worth disable in in the future
# SC2181: Check exit code directly with e.g. 'if mycmd;', not indirectly with $?.
# SC2153: Possible misspelling: DOMAIN may not be assigned, but domain is. - Issues with SOURCE importing vars that are not defined in the script it self but config files
# SC2016: Expressions don't expand in single quotes, use double quotes for that. - History reasons
# SC2196: egrep is non-standard and deprecated. Use grep -E instead. Todo be removed in the future
# SC1090; Can't follow non-constant source. Use a directive to specify location. - Hestia loves $HESTIA/data/ips/$ip
# SC2031: var was modified in a subshell. That change might be lost.
# SC2010
# SC2143 
# SC2046

#set default value for error
err=0;

files=$(grep -rlE '#!/bin/(bash|sh)' ./ | grep -vE '\.(git|j2$|md$)'); 
for file in $files; do 
    echo "Linting: $file"
    shellcheck -x "$file" --severity="error" -e "SC2086,SC2002,SC2153,SC2181,SC2153,SC2129,SC2016,SC2196,SC1090,SC2031,SC2010,SC2143,SC2046" 
    if [ $? -gt 0 ]; then 
       printf "%s: \033[0;31m Fail \033[0m\n" "$file"
       err=1
    else 
        # split loop in 2 parts allowing debuggin in earier stage
       printf "%s: \033[0;32m Success \033[0m\n" "$file"
    fi
done

if [ $err == 1 ];
then 
exit "$err";
fi

for file in $files; do 
    echo "Linting: $file"
    shellcheck -x "$file" -e "SC2086,SC2002,SC2153,SC2181,SC2153,SC2129,SC2016,SC2196,SC1090,SC2031,SC2010,SC2143,SC2046" 
done

exit "$err";
