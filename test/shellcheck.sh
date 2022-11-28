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
shellcheck --version

i=0
f=0
files=$(grep -rlE '#!/bin/(bash|sh)' ./ | grep -vE '\.(git|j2$|md$)');
for file in $files; do
    i=$(($i+1));
    shellcheck -x "$file" --severity="error"
    # Only show failed checks
    if [ $? -gt 0 ]; then
       f=$(($f+1));
       echo "Linting: $file"
       printf "%s: \033[0;31m Fail \033[0m\n" "$file"
       err=1
    fi
done
echo "$i files checked and $f errors"

if [ $err = 1 ]; then
    exit 1;
fi
