#!/bin/bash

# Rebuild users and domains
for user in `ls /usr/local/hestia/data/users/`; do
    echo "(*) Rebuilding domains and account for user: $user..."
    if [ ! -z $WEB_SYSTEM ]; then
        $BIN/v-rebuild-web-domains $user >/dev/null 2>&1
    fi
    if [ ! -z $DNS_SYSTEM ]; then
        $BIN/v-rebuild-dns-domains $user >/dev/null 2>&1
    fi
    if [ ! -z $MAIL_SYSTEM ]; then 
        $BIN/v-rebuild-mail-domains $user >/dev/null 2>&1
    fi
done

echo "(*) Restarting services..."
if [ ! -z $MAIL_SYSTEM ]; then
    $BIN/v-restart-mail $restart
fi
if [ ! -z $IMAP_SYSTEM ]; then
    $BIN/v-restart-service $IMAP_SYSTEM $restart
fi
if [ ! -z $WEB_SYSTEM ]; then
    $BIN/v-restart-web $restart
    $BIN/v-restart-proxy $restart
fi
if [ ! -z $DNS_SYSTEM ]; then
    $BIN/v-restart-dns $restart
fi
for v in `ls /etc/php/`; do
	if [ -e /etc/php/$v/fpm ]; then
		$BIN/v-restart-service php$v-fpm $restart
	fi
done
if [ ! -z $FTP_SYSTEM ]; then
    $BIN/v-restart-ftp $restart
fi

# Restart SSH daemon and Hestia Control Panel service
$BIN/v-restart-service ssh $restart
$BIN/v-restart-service hestia $restart
