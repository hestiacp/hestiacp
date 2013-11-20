#!/bin/bash

# Define exim config
if [ -e "/etc/exim/exim.conf" ]; then
    # RHEL or CentOS
    conf="/etc/exim/exim.conf"
else
    # Debian or Ubuntu
    conf="/etc/exim4/exim4.conf.template"
fi

# Check existance
if [ ! -e "$conf" ]; then
    exit
fi

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

# Restart mail server
/usr/local/vesta/bin/v-restart-mail

exit
