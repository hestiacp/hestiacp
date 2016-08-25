# Check if script already running or not
is_procces_running() {
    SCRIPT=$(basename $0)
    for pid in $(pidof -x $SCRIPT); do
        if [ $pid != $$ ]; then
            check_result $E_INUSE "$SCRIPT is already running"
        fi
    done
}

send_api_cmd() {
    answer=$(curl -s -k \
        --data-urlencode "user=$USER" \
        --data-urlencode "password=$PASSWORD" \
        --data-urlencode "returncode=yes" \
        --data-urlencode "cmd=$1" \
        --data-urlencode "arg1=$2" \
        --data-urlencode "arg2=$3" \
        --data-urlencode "arg3=$4" \
        --data-urlencode "arg4=$5" \
        --data-urlencode "arg5=$6" \
        --data-urlencode "arg6=$7" \
        --data-urlencode "arg7=$8" \
        --data-urlencode "arg8=$9" \
        https://$HOST:$PORT/api/)
    return $answer
}

send_api_file() {
    answer=$(curl -s -k \
        --data-urlencode "user=$USER" \
        --data-urlencode "password=$PASSWORD" \
        --data-urlencode "returncode=yes" \
        --data-urlencode "cmd=v-make-tmp-file" \
        --data-urlencode "arg1=$(cat $1)" \
        --data-urlencode "arg2=$2" \
        https://$HOST:$PORT/api/)
    return $answer
}

send_ssh_cmd() {
    if [ -z "$IDENTITY_FILE" ] && [ "$USER" = 'root' ]; then
        IDENTITY_FILE="/root/.ssh/id_rsa"
    fi
    if [ -z "$IDENTITY_FILE" ]; then
        IDENTITY_FILE="/home/$USER/.ssh/id_rsa"
    fi

    if [ "$USER" = 'root' ]; then
        args="$VESTA/bin/$1 \"$2\" \"$3\" \"$4\" \"$5\""
    else
        args="sudo $VESTA/bin/$1 \"$2\" \"$3\" \"$4\" \"$5\""
    fi
    ssh -i $IDENTITY_FILE $USER@$HOST -p $PORT "$args" > /dev/null 2>&1
    if [ "$?" -ne '0' ]; then
        return 1
    else
        return 0
    fi
}

send_scp_file() {
    if [ -z "$IDENTITY_FILE" ]; then
        IDENTITY_FILE="/home/admin/.ssh/id_rsa"
    fi
    scp -P $PORT -i $IDENTITY_FILE $1 $USER@$HOST:$2 > /dev/null 2>&1
    if [ "$?" -ne '0' ]; then
        return 1
    else
        return 0
    fi
}

is_dnshost_new() {
    if [ -e "$VESTA/conf/dns-cluster.conf" ]; then
        check_host=$(grep "HOST='$host'" $VESTA/conf/dns-cluster.conf)
        if [ ! -z "$check_host" ]; then
            check_result $E_EXISTS "remote dns host $host exists"
        fi
    fi
}

is_dnshost_alive() {
    cluster_cmd v-list-sys-config
    check_result $? "$type connection to $HOST failed" $E_CONNECT

    cluster_cmd v-list-user $DNS_USER
    check_result $? "$DNS_USER doesn't exist" $E_CONNECT
}

remote_dns_health_check() {
    OLD_IFS="$IFS"
    IFS=$'\n'

    # Starting health-check
    for str in $(grep "SUSPENDED='no'" $VESTA/conf/dns-cluster.conf); do
        eval $str

        # Checking host connection
        cluster_cmd v-list-user $DNS_USER
        if [ $? -ne 0 ]; then

            # Creating error report
            tmpfile=$(mktemp)
            echo "$(basename $0) $*" > $tmpfile
            echo -e "Error: $TYPE connection to $HOST failed.\n" >> $tmpfile
            echo -n "Remote dns host has been suspended." >> $tmpfile
            echo -n "After resolving issue run "  >> $tmpfile
            echo -e "following commands:\n" >> $tmpfile
            echo "v-unsuspend-remote-dns-host $HOST" >> $tmpfile
            echo "v-sync-dns-cluster $HOST" >> $tmpfile
            echo -e "\n\n--\nVesta Control Panel\n$(hostname)" >> $tmpfile

            if [ "$1" = 'no_email' ]; then
                cat $tmpfile
            else
                subj="DNS sync failed"
                email=$($BIN/v-get-user-value admin CONTACT)
                cat $tmpfile |$SENDMAIL -s "$subj" $email
            fi

            # Deleting tmp file
            rm -f $tmpfile
            log_event "$E_CONNECT" "$ARGUMENTS"

            # Suspending remote host
            dconf="../../conf/dns-cluster"
            update_object_value "$dconf" 'HOST' "$HOST" '$SUSPENDED' 'yes'
        fi
    done
    IFS="$OLD_IFS"
}

cluster_cmd() {
    case $TYPE in
        ssh)    send_ssh_cmd $* ;;
        api)    send_api_cmd $* ;;
    esac
}

cluster_file() {
    case $TYPE in
        ssh)    send_scp_file $* ;;
        api)    send_api_file $* ;;
    esac
}
