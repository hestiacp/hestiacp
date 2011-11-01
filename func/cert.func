is_cert_free() {
    # Defining path
    user_cert="$V_USERS/$user/cert/$cert"

    # Checking file existance
    if [ -e "$user_cert.crt" ] || [ -e "$user_cert.key" ]; then
        echo "Error: certificate exist"
        log_event 'debug' "$E_CERT_EXIST $V_EVENT"
        exit $E_CERT_EXIST
    fi
}

is_cert_valid() {
    path="$1"

    # Checking file existance
    if [ ! -e "$path/$cert.crt" ] || [ ! -e "$path/$cert.key" ]; then
        echo "Error: certificate not exist"
        log_event 'debug' "$E_CERT_NOTEXIST $V_EVENT"
        exit $E_CERT_NOTEXIST
    fi

    # Checking crt file
    crt=$(openssl verify "$path/$cert.crt" 2>/dev/null|tail -n 1|grep -w 'OK')
    if [ -z "$crt" ]; then
        echo "Error: certificate invalid"
        log_event 'debug' "$E_CERT_INVALID $V_EVENT"
        exit $E_CERT_INVALID
    fi

    # Checking key file
    key=$(openssl rsa -in "$path/$cert.key" -check 2>/dev/null|\
        head -n1|grep -w 'ok')
    if [ -z "$key" ]; then
        echo "Error: key invalid"
        log_event 'debug' "$E_KEY_INVALID $V_EVENT"
        exit $E_KEY_INVALID
    fi

    # FIXME we should run server on free port
    # Checking server
    cmd="openssl s_server -quiet -cert $path/$cert.crt -key $path/$cert.key"
    $cmd &

    # Defining pid
    pid=$!

    # Sleep 1 second
    sleep 1

    # Disown background process
    disown > /dev/null 2>&1

    # Killing ssl server
    kill $pid > /dev/null 2>&1

    # Checking result
    result=$?
    if [ "$result" -ne '0' ]; then
        echo "Error: certificate key pair invalid"
        log_event 'debug' "$E_CERTKEY_INVALID $V_EVENT"
        exit $E_CERTKEY_INVALID
    fi
}

is_cert_used() {
    # Parsing config
    check_cert=$(grep "SSL_CERT='$cert'" $V_USERS/$user/web.conf)

    # Checking result
    if [ ! -z "$check_cert" ]; then
        echo "Error: certificate used"
        log_event 'debug' "$E_CERT_USED $V_EVENT"
        exit $E_CERT_USED
    fi
}
