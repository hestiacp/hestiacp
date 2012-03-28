# Validationg ip address
is_ip_valid() {
    check_ifc=$(/sbin/ifconfig |grep "inet addr:$ip")
    if [ ! -e "$VESTA/data/ips/$ip" ] || [ -z "$check_ifc" ]; then
        echo "Error: IP $ip not exist"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
}

# Check if ip availabile for user
is_ip_avalable() {
    ip_data=$(cat $VESTA/data/ips/$ip)
    owner=$(echo "$ip_data"|grep OWNER= | cut -f 2 -d \')
    status=$(echo "$ip_data"|grep OWNER= | cut -f 2 -d \')
    shared=no
    if [ 'admin' = "$owner" ] && [ "$status" = 'shared' ]; then
        shared='yes'
    fi
    if [ "$owner" != "$user" ] && [ "$shared" != 'yes' ]; then
        echo "Error: User $user don't have permission to use $ip"
        log_event "$E_FORBIDEN" "$EVENT"
        exit $E_FORBIDEN
    fi
}

# Check ip ownership
is_sys_ip_owner() {
    # Parsing ip
    owner=$(grep 'OWNER=' $VESTA/data/ips/$IP|cut -f 2 -d \')
    if [ "$owner" != "$user" ]; then
        echo "Error: IP $IP not owned"
        log_event "$E_FORBIDEN" "$EVENT"
        exit $E_FORBIDEN
    fi
}


is_sys_ip_free() {
    # Parsing system ips
    ip_list=$(/sbin/ifconfig|grep 'inet addr:'|cut -f 2 -d ':'|cut -f 1 -d " ")

    # Checking ip existance
    ip_check=$(echo "$ip_list"|grep -w "$ip")
    if [ -n "$ip_check" ] || [ -e "$VESTA/data/ips/$ip" ]; then
        echo "Error: IP exist"
        log_event 'debug' "$E_EXISTS $EVENT"
        exit  $E_EXISTS
    fi
}

get_next_interface_number() {
    # Parsing ifconfig
    i=$(/sbin/ifconfig -a |grep -w "$interface"|cut -f1 -d ' '|\
        tail -n 1|cut -f 2 -d :)

    # Checking result
    if [ "$i" = "$interface" ]; then
        n=0
    else
        n=$((i + 1))
    fi
    echo ":$n"
}


is_ip_key_empty() {
    key="$1"

    # Parsing ip
    string=$(cat $VESTA/data/ips/$ip )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Checkng key
    if [ ! -z "$value" ] && [ "$value" != '0' ]; then
        echo "Error: value is not empty = $value "
        log_event 'debug' "$E_EXISTS $EVENT"
        exit $E_EXISTS
    fi
}

update_sys_ip_value() {
    key="$1"
    value="$2"

    # Defining conf
    conf="$VESTA/data/ips/$ip"

    # Parsing conf
    str=$(cat $conf)

    # Reading key=values
    for keys in $str; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Define clean key
    c_key=$(echo "${key//$/}")

    eval old="${key}"

    # Escaping slashes
    old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    new=$(echo "$value" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')

    # Updating conf
    sed -i "$str_number s/$c_key='${old//\*/\\*}'/$c_key='${new//\*/\\*}'/g"\
         $conf
}



get_ip_name() {
    # Prinitng name
    grep "NAME=" $VESTA/data/ips/$ip |cut -f 2 -d \'
}

increase_ip_value() {
    sip=${1-ip}
    USER=$user
    web_key='U_WEB_DOMAINS'
    usr_key='U_SYS_USERS'

    # Parsing values
    current_web=$(grep "$web_key=" $VESTA/data/ips/$sip |cut -f 2 -d \')
    current_usr=$(grep "$usr_key=" $VESTA/data/ips/$sip |cut -f 2 -d \')

    # Checking result
    if [ -z "$current_web" ]; then
        echo "Error: Parsing error"
        log_event 'debug' "$E_PARSING $EVENT"
        exit $E_PARSING
    fi

    # +1 webdomain
    new_web=$((current_web + 1))

    # +1 user
    if [ -z "$current_usr" ]; then
        new_usr="$USER"
    else
        check_usr=$(echo -e "${current_usr//,/\n}" |grep -w $USER)
        if [ -z "$check_usr" ]; then
            new_usr="$current_usr,$USER"
        else
            new_usr="$current_usr"
        fi
    fi

    # Changing config
    sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" \
        $VESTA/data/ips/$ip
    sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
        $VESTA/data/ips/$ip
}

decrease_ip_value() {
    sip=${1-ip}
    USER=$user
    web_key='U_WEB_DOMAINS'
    usr_key='U_SYS_USERS'

    # Parsing values
    current_web=$(grep "$web_key=" $VESTA/data/ips/$sip |cut -f 2 -d \')
    current_usr=$(grep "$usr_key=" $VESTA/data/ips/$sip |cut -f 2 -d \')

    # Checking result
    if [ -z "$current_web" ]; then
        echo "Error: Parsing error"
        log_event 'debug' "$E_PARSING $EVENT"
        exit $E_PARSING
    fi

    # -1 webdomain
    new_web=$((current_web - 1))

    # -1 user
    check_ip=$(grep $sip $USER_DATA/web.conf |wc -l)
    if [ "$check_ip" -lt 2 ]; then
        new_usr=$(echo "$current_usr" |\
            sed -e "s/,/\n/g"|\
            sed -e "s/^$user$//g"|\
            sed -e "/^$/d"|\
            sed -e ':a;N;$!ba;s/\n/,/g')
    else
        new_usr="$current_usr"
    fi

    # Changing config
    sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" \
        $VESTA/data/ips/$sip
    sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
        $VESTA/data/ips/$sip
}

get_sys_ip_value() {
    key="$1"

    # Parsing domains
    string=$( cat $VESTA/data/ips/$ip )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Print value
    echo "$value"
}

get_current_interface() {
    # Parsing ifconfig
    i=$(/sbin/ifconfig |grep -B1 "addr:$ip "|head -n 1 |cut -f 1 -d ' ')

    # Checking result
    if [ -z "$i" ]; then
        echo "Error: IP not exist"
        log_event 'debug' "$E_NOTEXIST $EVENT"
        exit $E_NOTEXIST
    fi

    # Checking ip is alias
    check_alias=$(echo $i| cut -s -f 2 -d :)
    if [ -z "$check_alias" ]; then
        echo "Error: Main IP on interface"
        log_event 'debug' "$E_FORBIDEN $EVENT"
        exit $E_FORBIDEN
    fi
    echo "$i"
}

ip_add_vesta() {
    # Filling ip values
    ip_data="OWNER='$user'"
    ip_data="$ip_data\nSTATUS='$ip_status'"
    ip_data="$ip_data\nNAME='$ip_name'"
    ip_data="$ip_data\nU_SYS_USERS=''"
    ip_data="$ip_data\nU_WEB_DOMAINS='0'"
    ip_data="$ip_data\nINTERFACE='$interface'"
    ip_data="$ip_data\nNETMASK='$mask'"
    ip_data="$ip_data\nDATE='$DATE'"

    # Adding ip
    echo -e "$ip_data" >$VESTA/data/ips/$ip
    chmod 660 $VESTA/data/ips/$ip
}

ip_add_startup() {
    # Filling ip values
    ip_data="# Added by vesta $SCRIPT"
    ip_data="$ip_data\nDEVICE=$iface"
    ip_data="$ip_data\nBOOTPROTO=static\nONBOOT=yes"
    ip_data="$ip_data\nIPADDR=$ip"
    ip_data="$ip_data\nNETMASK=$mask"

    # Adding ip
    echo -e "$ip_data" >$iconf-$iface
}

ip_owner_search(){
    for ip in $(ls $VESTA/data/ips/); do
        check_owner=$(grep "OWNER='$user'" $VESTA/data/ips/$ip)
        if [ ! -z "$check_owner" ]; then
            echo "$ip"
        fi
    done
}
