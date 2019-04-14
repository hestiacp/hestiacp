#!/bin/bash
# Add notifications

rm -f /usr/local/hestia/data/users/admin/notifications.conf
/usr/local/hestia/bin/v-add-user-notification admin "HestiaCP Beta Test"
