#!/bin/bash

# Modify Exim conf file (/etc/exim4/exim4.conf.template) to advertise AUTH only for localhost and TLS
# connections, so we avoid that users send their passwords as clear text over the net.
if ! grep -qw '^auth_advertise_hosts =' '/etc/exim4/exim4.conf.template'; then
	echo '[ * ] Enable auth advertise for Exim only for localhost and TLS connections'
	sed -i '/^tls_require_ciphers\s=\s.*/a auth_advertise_hosts = localhost : ${if eq{$tls_in_cipher}{}{}{*}}' '/etc/exim4/exim4.conf.template'
	systemctl restart exim4
fi
