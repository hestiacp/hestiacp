send_api_cmd() {
    if [ -z $PORT ]; then
        PORT=8083
    fi
    if [ -z $USER ]; then
        USER=admin
    fi

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

    if [ "$answer" != '0' ]; then
        return 1
    else
        return 0
    fi
}

send_ssh_cmd() {
    if [ -z $PORT ]; then
        PORT=22
    fi
    if [ -z $USER ]; then
        USER=admin
    fi
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

scp_cmd() {
    if [ -z $PORT ]; then
        PORT=22
    fi
    if [ -z $USER ]; then
        USER=admin
    fi
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
            echo "Error: dns host $host exists"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
    fi
}

is_dnshost_alive() {
    HOST=$host
    PORT=$port
    USER=$user
    PASSWORD=$password

    # Switch on connection type
    case $type in
        ssh) send_cmd="send_ssh_cmd" ;;
        *)  send_cmd="send_api_cmd" ;;
    esac

    # Check host connection
    $send_cmd v-list-sys-config
    if [ $? -ne 0 ]; then
        echo "Error: $type connection to $HOST failed"
        log_event "$E_CONNECT" "$EVENT"
        exit $E_CONNECT
    fi

    # Check recipient dns user
    if [ -z "$DNS_USER" ]; then
        DNS_USER='dns-cluster'
    fi
    if [ ! -z "$verbose" ]; then
        echo "DNS_USER: $DNS_USER"
    fi
    $send_cmd v-list-user $DNS_USER
    if [ $? -ne 0 ]; then
        echo "Error: dns user $DNS_USER doesn't exist"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
}

remote_dns_health_check() {
    # Define tmp mail vars
    subj="DNS sync failed"
    email=$(grep CONTACT $VESTA/data/users/admin/user.conf | cut -f 2 -d \')
    send_mail="$VESTA/web/inc/mail-wrapper.php"
    tmpfile=$(mktemp)

    # Starting health-check
    for str in $(grep "SUSPENDED='no'" $VESTA/conf/dns-cluster.conf); do

        # Get host values
        eval $str

        # Check connection type
        if [ -z "TYPE" ]; then
            TYPE='api'
        fi

        # Switch on connection type
        case $TYPE in
            ssh) send_cmd="send_ssh_cmd" ;;
            *)  send_cmd="send_api_cmd" ;;
        esac

        # Check host connection
        $send_cmd v-list-sys-config
        if [ $? -ne 0 ]; then
            echo "$(basename $0) $*" > $tmpfile
            echo -e "Error: $TYPE connection to $HOST failed.\n" >> $tmpfile
            echo -n "Remote dns host has been suspended." >> $tmpfile
            echo -n "After resolving issue run "  >> $tmpfile
            echo -e "following commands:\n" >> $tmpfile
            echo "v-unsuspend-remote-dns-host $HOST" >> $tmpfile
            echo "v-sync-dns-cluster $HOST" >> $tmpfile
            echo -e "\n\n--\nVesta Control Panel\n$(hostname)" >> $tmpfile
            cat $tmpfile |  $send_mail -s "$subj" $email

            log_event "$E_CONNECT" "$EVENT"
            dconf="../../../conf/dns-cluster"
            update_object_value "$dconf" 'HOST' "$HOST" '$SUSPENDED' 'yes'
        fi

        # Remove tmp file
        rm -f $tmpfile
    done
}
