#!/bin/bash
# info: enable fail2ban dovecot whitelisting
#
# The function:
#   edits jail.local
#   adds action.d/ignoreip.conf
#   adds filter.d/dovecot-whitelist.conf

#----------------------------------------------------------#
#                    Variable&Function                     #
#----------------------------------------------------------#

#----------------------------------------------------------#
#                    Verifications                         #
#----------------------------------------------------------#

#----------------------------------------------------------#
#                       Action                             #
#----------------------------------------------------------#

#Adds the config lines at the end of jail.local
file1='/etc/fail2ban/jail.local'

echo "[dovecot-whitelist]" >> $file
echo "enabled   = true" >> $file
echo "action    = ignoreip[name=WHITELIST]" >> $file
echo "filter = dovecot-whitelist" >> $file
echo "logpath   = /var/log/dovecot.log" >> $file
echo "maxretry  = 0" >> $file
echo "bantime = 14400" >> $file

#Adds the filter
file2='/etc/fail2ban/filter.d/dovecot-whitelist.conf'
if [ ! -f $file2 ]; then
    echo "[INCLUDES]" >> $file2
    echo "before = common.conf" >> $file2
    echo "[Definition]" >> $file2
    echo "_auth_worker = (?:dovecot: )?auth(?:-worker)?" >> $file2
    echo "_daemon = (?:dovecot(?:-auth)?|auth)" >> $file2
    echo "failregex = ^.*(?:pop3|imap)-login: Info: Login:.*rip=<HOST>.*\s$" >> $file2
    echo "ignoreregex = ^authentication failure; logname=\S* uid=\S* euid=\S* tty=dovecot ruser=\S* rhost=<HOST>(?:\s+user=\S*)?\s*$" >> $file2
    echo "        ^(?:Aborted login|Disconnected)(?::(?: [^ \(]+)+)? \((?:auth failed, \d+ attempts(?: in \d+ secs)?|tried to use (?:disabled|disallowed) \S+ auth)\):(?: user=<[^>]*>,)?(?: method=\S+,)? rip=<HOST>(?:[^>]*(?:, session=<\S+>)?)\s*$">> $file2
    echo "        ^pam\(\S+,<HOST>(?:,\S*)?\): pam_authenticate\(\) failed: (?:User not known to the underlying authentication module: \d+ Time\(s\)|Authentication failure \(password mismatch\?\)|Permission denied)\s*$" >> $file2
    echo "        ^[a-z\-]{3,15}\(\S*,<HOST>(?:,\S*)?\): (?:unknown user|invalid credentials)\s*$" >> $file2
    echo "journalmatch = _SYSTEMD_UNIT=dovecot.service" >> $file2
    echo "datepattern = {^LN-BEG}TAI64N" >> $file2
    echo "          {^LN-BEG}" >> $file2
  fi


#Adds the action
file3='/etc/fail2ban/action.d/ignoreip.conf'
if [ ! -f $file3 ]; then
    echo "[Definition]" >> $file3
    echo "actionstart =" >> $file3
    echo "actionstop  =" >> $file3
    echo "actioncheck = iptables -n -L <chain> | grep -q 'f2b-<name>[ \t]'" >> $file3
    echo "actionban   = fail2ban-client set <name> addignoreip <ip>" >> $file3
    echo "actionunban = fail2ban-client set <name> delignoreip <ip>" >> $file3
    echo "[Init]" >> $file3
    echo "name  = default" >> $file3
    echo "chain = INPUT" >> $file3
fi

#restarts service
systemctl restart fail2ban

