#!/bin/bash

# Check if there is no crontab
if [ ! -e "/var/spool/cron" ]; then
    exit
fi

# Fix ownership and permissions
for crn_tab in $(ls /var/spool/cron/); do
    chown $crn_tab:$crn_tab /var/spool/cron/$crn_tab
    chmod 600 /var/spool/cron/$crn_tab
done

exit

