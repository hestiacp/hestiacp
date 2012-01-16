#!/bin/bash

# Define some variables
source /etc/profile.d/vesta.sh
V_BIN="$VESTA/bin"
V_TEST="$VESTA/test"

# Define functions
random() {
    MATRIX='0123456789'
    LENGTH=$1
    while [ ${n:=1} -le $LENGTH ]; do
        rand="$rand${MATRIX:$(($RANDOM%${#MATRIX})):1}"
        let n+=1
    done
    echo "$rand"
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
user="tmp_$(random 4)"
while [ ! -z "$(grep "^$user:" /etc/passwd)" ]; do
    user="tmp_$(random 4)"
done

# Create random tmpfile
tmpfile=$(mktemp -p /tmp )


# Add new user
cmd="v_add_user $user $user $user@vestacp.com default Super Test"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Adding new user $user" "$?" "$tmpfile" "$cmd"


# Change system shell
cmd="v_change_user_shell $user bash"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Changing system shell to /bin/bash" "$?" "$tmpfile" "$cmd"


# Change name servers
cmd="v_change_user_ns $user ns0.com ns1.com ns2.com ns3.com"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Changing nameservers" "$?" "$tmpfile" "$cmd"


# Add cron job
cmd="v_add_cron_job $user 1 1 1 1 1 echo"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Adding cron job" "$?" "$tmpfile" "$cmd"


# Suspend cron job
cmd="v_suspend_cron_job $user 1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Suspending cron job" "$?" "$tmpfile" "$cmd"


# Unsuspend cron job
cmd="v_unsuspend_cron_job $user 1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Unsuspending cron job" "$?" "$tmpfile" "$cmd"


# Delete cron job
cmd="v_delete_cron_job $user 1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Deleting cron job" "$?" "$tmpfile" "$cmd"


# Add cron job
cmd="v_add_cron_job $user 1 1 1 1 1 echo 1"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Adding cron job" "$?" "$tmpfile" "$cmd"


# Add cron job
cmd="v_add_cron_job $user 1 1 1 1 1 echo 1"
$cmd > $tmpfile 2>> $tmpfile
if [ "$?" -eq 4 ]; then
    retval=0
else
    retval=1
fi
echo_result "Dublicate cron job check" "$retval" "$tmpfile" "$cmd"


# List network interfaces
cmd="v_list_sys_interfaces plain"
interface=$($cmd 2> $tmpfile | head -n 1)
if [ -z "$interface" ]; then
    echo_result "Listing network interfaces" "1" "$tmpfile" "$cmd"
else
    echo_result "Listing network interfaces" "0" "$tmpfile" "$cmd"
fi


# Add new ip address
cmd="v_add_sys_ip 198.18.0.123 255.255.255.255 $interface $user"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Adding ip 198.18.0.123" "$?" "$tmpfile" "$cmd"


# Delete ip address
cmd="v_delete_sys_ip 198.18.0.123"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Deleting ip 198.18.0.123" "$?" "$tmpfile" "$cmd"


# Delete new user
cmd="v_delete_user $user"
$cmd > $tmpfile 2>> $tmpfile
echo_result "Deleting user $user" "$?" "$tmpfile" "$cmd"

