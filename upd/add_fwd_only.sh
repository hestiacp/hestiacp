#!/bin/bash

# RHEL or CentOS
if [ -e "/etc/exim/exim.conf" ]; then
    conf='/etc/exim/exim.conf'

    # Check if fwd_only flag
    check_flag=$(grep localuser_fwd_only $conf)
    if [ ! -z "$check_flag" ]; then
        exit
    fi

    # Define new router
    fwd1='localuser_fwd_only:\n  driver = accept\n  transport = devnull\n'
    fwd2='  condition = \${if exists{/etc/exim/domains/\$domain/fwd_only}'
    fwd3='{\${lookup{\$local_part}lsearch{/etc/exim/domains/\$domain/fwd_only}'
    fwd4='{true}{false}}}}\n\n'

    # Insert router
    sed -i "s%localuser_spam:%$fwd1$fwd2$fwd3${fwd4}localuser_spam:%" $conf
fi

# Debian or Ubuntu
if [ -e "/etc/exim4/exim4.conf.template" ]; then
    conf="/etc/exim4/exim4.conf.template"

    # Check if fwd_only flag
    check_flag=$(grep localuser_fwd_only $conf)
    if [ ! -z "$check_flag" ]; then
        sed -i "s%/exim/domains/%/exim4/domains/%g" $conf
    else
        # Define new router
        fwd1='localuser_fwd_only:\n  driver = accept\n  transport = devnull\n'
        fwd2='  condition = \${if exists{/etc/exim4/domains/\$domain/fwd_only}'
        fwd3='{\${lookup{\$local_part}lsearch'
        fwd4='{/etc/exim4/domains/\$domain/fwd_only}{true}{false}}}}\n\n'

        # Insert router
        sed -i "s%localuser_spam:%$fwd1$fwd2$fwd3${fwd4}localuser_spam:%" $conf
    fi
fi

# Restart mail server
/usr/local/vesta/bin/v-restart-mail > /dev/null 2>&1

exit
