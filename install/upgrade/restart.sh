#!/bin/bash

echo "(*) Restarting services..."
sleep 3
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

# Restart SSH daemon and Hestia Control Panel service
$BIN/v-restart-service ssh $restart
$BIN/v-restart-service hestia $restart
