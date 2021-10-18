#!/bin/bash

# To be used with in shellcheck and automated usage 
# Generate timestamp

if [ ! -f "/usr/bin/shellcheck" ]; then 
    echo "Run APT install shell check"
    exit 2; 
fi

run_shell_check() {
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
    
    if [ -f "$1" ]; then 
        # -x "$1" 
        echo "$1"
        shellcheck -x  "$1" --severity="error" -e "SC2086,SC2002,SC2153,SC2181,SC2153,SC2129,SC2016,SC2196,SC1090,SC2031,SC2010,SC2143,SC2046"
    fi
}

# Start with listing all .sh files
# Exclude: /usr/local/hestia/data/ 
# Exclude: /usr/local/hestia/test/
# Exclude: /usr/local/hestia/src/
find "/usr/local/hestia/" -name "*.sh" -not -path "/usr/local/hestia/data/*" -not -path "/usr/local/hestia/test/*" -not -path "/usr/local/hestia/php/*" -not -path "/usr/local/hestia/nginx/*" -print0 | while read -r -d $'\0' file
do
    run_shell_check "$file"
done
# Exclude v-generate-password-hash
find "/usr/local/hestia/bin/" -name "*" -print0 | while read -r -d $'\0' file
do
    if [ "$file" != "/usr/local/hestia/bin/v-generate-password-hash" ]; then
        run_shell_check "$file"
    fi
done