#!/bin/bash

# Define some variables
source /etc/profile.d/hestia.sh
V_BIN="$HESTIA/bin"
V_TEST="$HESTIA/test"

# Define functions
random() {
    head /dev/urandom | tr -dc 0-9 | head -c$1
}

echo_result() {
    echo -en  "$1"
    echo -en '\033[60G'
    echo -n '['

    if [ "$2" -ne 0 ]; then
        echo -n 'FAILED'
        echo -n ']'
        echo -ne '\r\n'
        echo ">>> $4"
        echo ">>> RETURN VALUE $2"
        cat $3
    else
        echo -n '  OK  '
        echo -n ']'
    fi
    echo -ne '\r\n'
}

# Create random username
user="testu-$(random 4)"
while [ ! -z "$(grep "^$user:" /etc/passwd)" ]; do
    user="tmp-$(random 4)"
done

# Create random tmpfile
tmpfile=$(mktemp -p /tmp )
echo $tmpfile

#----------------------------------------------------------#
#                         User                             #
#----------------------------------------------------------#
# Add user
cmd="v-add-user $user $user $user@hestiacp.com default Super Test"
$cmd > $tmpfile 2>> $tmpfile
echo_result "USER: Adding new user $user" "$?" "$tmpfile" "$cmd"

# Change user password
cmd="v-change-user-password $user t3st-p4ssw0rd"
$cmd > $tmpfile 2>> $tmpfile
echo_result "USER: Changing password" "$?" "$tmpfile" "$cmd"

# Change user contact
cmd="v-change-user-contact $user tester@hestiacp.com"
$cmd > $tmpfile 2>> $tmpfile
echo_result "USER: Changing email" "$?" "$tmpfile" "$cmd"

# Change system shell
cmd="v-change-user-shell $user bash"
$cmd > $tmpfile 2>> $tmpfile
echo_result "USER: Changing system shell to /bin/bash" "$?" "$tmpfile" "$cmd"

# Change name servers
cmd="v-change-user-ns $user ns0.com ns1.com ns2.com ns3.com"
$cmd > $tmpfile 2>> $tmpfile
echo_result "USER: Changing nameservers" "$?" "$tmpfile" "$cmd"


#----------------------------------------------------------#
#                         Cron                             #
#----------------------------------------------------------#

# Add cron job
cmd="v-add-cron-job $user 1 1 1 1 1 echo"
$cmd > $tmpfile 2>> $tmpfile
echo_result "CRON: Adding cron job" "$?" "$tmpfile" "$cmd"

# Suspend cron job
cmd="v-suspend-cron-job $user 1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "CRON: Suspending cron job" "$?" "$tmpfile" "$cmd"

# Unsuspend cron job
cmd="v-unsuspend-cron-job $user 1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "CRON: Unsuspending cron job" "$?" "$tmpfile" "$cmd"

# Delete cron job
cmd="v-delete-cron-job $user 1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "CRON: Deleting cron job" "$?" "$tmpfile" "$cmd"

# Add cron job
cmd="v-add-cron-job $user 1 1 1 1 1 echo 1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "CRON: Adding cron job" "$?" "$tmpfile" "$cmd"

# Add cron job
cmd="v-add-cron-job $user 1 1 1 1 1 echo 1"
$cmd > $tmpfile 2>> $tmpfile
if [ "$?" -eq 4 ]; then
    retval=0
else
    retval=1
fi
echo_result "CRON: Duplicate cron job check" "$retval" "$tmpfile" "$cmd"

# Add second cron job
cmd="v-add-cron-job $user 2 2 2 2 2 echo 2"
$cmd > $tmpfile 2>> $tmpfile
echo_result "CRON: Adding second cron job" "$?" "$tmpfile" "$cmd"

# Rebuild cron jobs
cmd="v-rebuild-cron-jobs $user"
$cmd > $tmpfile 2>> $tmpfile
echo_result "CRON: Rebuilding cron jobs" "$?" "$tmpfile" "$cmd"


#----------------------------------------------------------#
#                          IP                              #
#----------------------------------------------------------#

# List network interfaces
cmd="v-list-sys-interfaces plain"
interface=$($cmd 2> $tmpfile | head -n 1)
if [ -z "$interface" ]; then
    echo_result "IP: Listing network interfaces" "1" "$tmpfile" "$cmd"
else
    echo_result "IP: Listing network interfaces" "0" "$tmpfile" "$cmd"
fi

# Add ip address
cmd="v-add-sys-ip 198.18.0.123 255.255.255.255 $interface $user"
$cmd > $tmpfile 2>> $tmpfile
echo_result "IP: Adding ip 198.18.0.123" "$?" "$tmpfile" "$cmd"

# Add duplicate ip
$cmd > $tmpfile 2>> $tmpfile
if [ "$?" -eq 4 ]; then
    retval=0
else
    retval=1
fi
echo_result "IP: Duplicate ip address check" "$retval" "$tmpfile" "$cmd"

# Delete ip address
cmd="v-delete-sys-ip 198.18.0.123"
$cmd > $tmpfile 2>> $tmpfile
echo_result "IP: Deleting ip 198.18.0.123" "$?" "$tmpfile" "$cmd"

# Add ip address
cmd="v-add-sys-ip 198.18.0.125 255.255.255.255 $interface $user"
$cmd > $tmpfile 2>> $tmpfile
echo_result "IP: Adding ip 198.18.0.125" "$?" "$tmpfile" "$cmd"


#----------------------------------------------------------#
#                         WEB                              #
#----------------------------------------------------------#

# Add web domain
domain="test-$(random 4).hestiacp.com"
cmd="v-add-web-domain $user $domain 198.18.0.125"
$cmd > $tmpfile 2>> $tmpfile
echo_result "WEB: Adding domain $domain on 198.18.0.125" "$?" "$tmpfile" "$cmd"

# Add duplicate
$cmd > $tmpfile 2>> $tmpfile
if [ "$?" -eq 4 ]; then
    retval=0
else
    retval=1
fi
echo_result "WEB: Duplicate web domain check" "$retval" "$tmpfile" "$cmd"

# Add web domain alias
cmd="v-add-web-domain-alias $user $domain v3.$domain"
$cmd > $tmpfile 2>> $tmpfile
echo_result "WEB: Adding alias v3.$domain" "$?" "$tmpfile" "$cmd"

# Alias duplicate
$cmd > $tmpfile 2>> $tmpfile
if [ "$?" -eq 4 ]; then
    retval=0
else
    retval=1
fi
echo_result "WEB: Duplicate web alias check" "$retval" "$tmpfile" "$cmd"

# Add web domain stats
cmd="v-add-web-domain-stats $user $domain awstats"
$cmd > $tmpfile 2>> $tmpfile
echo_result "WEB: Enabling awstats" "$?" "$tmpfile" "$cmd"

# Add web domain stats 
cmd="v-add-web-domain-stats-user $user $domain test m3g4p4ssw0rd"
$cmd > $tmpfile 2>> $tmpfile
echo_result "WEB: Adding awstats uzer" "$?" "$tmpfile" "$cmd"

# Suspend web domain
cmd="v-suspend-web-domain $user $domain"
$cmd > $tmpfile 2>> $tmpfile
echo_result "WEB: Suspending web domain" "$?" "$tmpfile" "$cmd"

# Unsuspend web domain
cmd="v-unsuspend-web-domain $user $domain"
$cmd > $tmpfile 2>> $tmpfile
echo_result "WEB: Unsuspending web domain" "$?" "$tmpfile" "$cmd"

# Add web domain ssl
cp $HESTIA/ssl/certificate.crt /tmp/$domain.crt
cp $HESTIA/ssl/certificate.key /tmp/$domain.key
cmd="v-add-web-domain-ssl $user $domain /tmp"
$cmd > $tmpfile 2>> $tmpfile
echo_result "WEB: Adding ssl support" "$?" "$tmpfile" "$cmd"

# Rebuild web domains
cmd="v-rebuild-web-domains $user"
$cmd > $tmpfile 2>> $tmpfile
echo_result "WEB: rebuilding web domains" "$?" "$tmpfile" "$cmd"


#----------------------------------------------------------#
#                         DNS                              #
#----------------------------------------------------------#

# Add dns domain
cmd="v-add-dns-domain $user $domain 198.18.0.125"
$cmd > $tmpfile 2>> $tmpfile
echo_result "DNS: Adding dns domain $domain" "$?" "$tmpfile" "$cmd"

# Add duplicate
$cmd > $tmpfile 2>> $tmpfile
if [ "$?" -eq 4 ]; then
    retval=0
else
    retval=1
fi
echo_result "DNS: Duplicate domain check" "$retval" "$tmpfile" "$cmd"

# Add dns record
cmd="v-add-dns-record $user $domain test A 198.18.0.125 20"
$cmd > $tmpfile 2>> $tmpfile
echo_result "DNS: Adding dns record" "$?" "$tmpfile" "$cmd"

# Add duplicate
$cmd > $tmpfile 2>> $tmpfile
if [ "$?" -eq 4 ]; then
    retval=0
else
    retval=1
fi
echo_result "DNS: Duplicate record check" "$retval" "$tmpfile" "$cmd"

# Delete dns record
cmd="v-delete-dns-record $user $domain 20"
$cmd > $tmpfile 2>> $tmpfile
echo_result "DNS: Deleteing dns record" "$?" "$tmpfile" "$cmd"

# Change exp
cmd="v-change-dns-domain-exp $user $domain 2020-01-01"
$cmd > $tmpfile 2>> $tmpfile
echo_result "DNS: Changing expiriation date" "$?" "$tmpfile" "$cmd"

# Change ip
cmd="v-change-dns-domain-ip $user $domain 127.0.0.1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "DNS: Changing domain ip" "$?" "$tmpfile" "$cmd"

# Suspend dns domain
cmd="v-suspend-dns-domain $user $domain"
$cmd > $tmpfile 2>> $tmpfile
echo_result "DNS: Suspending domain" "$?" "$tmpfile" "$cmd"

# Unuspend dns domain
cmd="v-unsuspend-dns-domain $user $domain"
$cmd > $tmpfile 2>> $tmpfile
echo_result "DNS: Unsuspending domain" "$?" "$tmpfile" "$cmd"

# Rebuild dns domain
cmd="v-rebuild-dns-domains $user"
$cmd > $tmpfile 2>> $tmpfile
echo_result "DNS: Rebuilding domain" "$?" "$tmpfile" "$cmd"


# Add mail domain
cmd="v-add-mail-domain $user $domain"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Adding mail domain $domain" "$?" "$tmpfile" "$cmd"

# Add mysql database
database=d$(random 4)
cmd="v-add-database $user $database $database dbp4ssw0rd mysql"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Adding mysql database $database" "$?" "$tmpfile" "$cmd"

# Add pgsql database
# database=d$(random 4)
# cmd="v-add-database $user $database $database dbp4ssw0rd pgsql"
# $cmd > $tmpfile 2>> $tmpfile
# echo_result "Adding pgsql database $database" "$?" "$tmpfile" "$cmd"

# Rebuild user configs
cmd="v-rebuild-user $user yes"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Rebuilding user config" "$?" "$tmpfile" "$cmd"

# Delete user
cmd="v-delete-user $user"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Deleting user $user" "$?" "$tmpfile" "$cmd"

# Delete ip address
cmd="v-delete-sys-ip 198.18.0.125"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Deleting ip 198.18.0.125" "$?" "$tmpfile" "$cmd"

