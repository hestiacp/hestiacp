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
is_ip_owner() {
    # Parsing ip
    owner=$(grep 'OWNER=' $VESTA/data/ips/$IP|cut -f 2 -d \')
    if [ "$owner" != "$user" ]; then
        echo "Error: IP $IP not owned"
        log_event "$E_FORBIDEN" "$EVENT"
        exit $E_FORBIDEN
    fi
}

# Check if ip address is free
is_ip_free() {
    list=$(/sbin/ifconfig |grep 'inet addr:' |cut -f 2 -d : |cut -f 1 -d ' ')
    ip_check=$(echo "$list" |grep -w "$ip")
    if [ -n "$ip_check" ] || [ -e "$VESTA/data/ips/$ip" ]; then
        echo "Error: IP exist"
        log_event "$E_EXISTS" "$EVENT"
        exit  $E_EXISTS
    fi
}

# Get full interface name
get_ip_iface() {
    i=$(/sbin/ifconfig -a |grep -w "$interface"|cut -f1 -d ' '|\
        tail -n 1|cut -f 2 -d :)
    if [ "$i" = "$interface" ]; then
        n=0
    else
        n=$((i + 1))
    fi
    iface="$interface:$n"
}


# Check ip address speciefic value
is_ip_key_empty() {
    key="$1"
    string=$(cat $VESTA/data/ips/$ip)
    eval $string
    eval value="$key"
    if [ ! -z "$value" ] && [ "$value" != '0' ]; then
        echo "Error: $key is not empty = $value"
        log_event "$E_EXISTS" "$EVENT"
        exit $E_EXISTS
    fi
}

# Update ip address value
update_ip_value() {
    key="$1"
    value="$2"
    conf="$VESTA/data/ips/$ip"
    str=$(cat $conf)
    eval $str
    c_key=$(echo "${key//$/}")
    eval old="${key}"
    old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    new=$(echo "$value" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    sed -i "$str_number s/$c_key='${old//\*/\\*}'/$c_key='${new//\*/\\*}'/g"\
        $conf
}

# Get ip name
get_ip_name() {
    grep "NAME=" $VESTA/data/ips/$ip |cut -f 2 -d \'
}

# Increase ip value
increase_ip_value() {
    sip=${1-ip}
    USER=$user
    web_key='U_WEB_DOMAINS'
    usr_key='U_SYS_USERS'
    current_web=$(grep "$web_key=" $VESTA/data/ips/$sip |cut -f 2 -d \')
    current_usr=$(grep "$usr_key=" $VESTA/data/ips/$sip |cut -f 2 -d \')
    if [ -z "$current_web" ]; then
        echo "Error: Parsing error"
        log_event "$E_PARSING" "$EVENT"
        exit $E_PARSING
    fi
    new_web=$((current_web + 1))
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

    sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" \
        $VESTA/data/ips/$ip
    sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
        $VESTA/data/ips/$ip
}

# Decrease ip value
decrease_ip_value() {
    sip=${1-ip}
    USER=$user
    web_key='U_WEB_DOMAINS'
    usr_key='U_SYS_USERS'

    current_web=$(grep "$web_key=" $VESTA/data/ips/$sip |cut -f 2 -d \')
    current_usr=$(grep "$usr_key=" $VESTA/data/ips/$sip |cut -f 2 -d \')

    if [ -z "$current_web" ]; then
        echo "Error: Parsing error"
        log_event "$E_PARSING" "$EVENT"
        exit $E_PARSING
    fi

    new_web=$((current_web - 1))
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

    sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" \
        $VESTA/data/ips/$sip
    sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
        $VESTA/data/ips/$sip
}

# Get ip address value
get_ip_value() {
    key="$1"
    string=$( cat $VESTA/data/ips/$ip )
    eval $string
    eval value="$key"
    echo "$value"
}

# Get current ip interface
get_current_interface() {
    i=$(/sbin/ifconfig |grep -B1 "addr:$ip "|head -n 1 |cut -f 1 -d ' ')
    if [ -z "$i" ]; then
        echo "Error: interface for ip $ip not found"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
    if [ -z "$(echo $i | cut -s -f 2 -d :)" ]; then
        echo "Error: Main IP on interface"
        log_event "$E_FORBIDEN" "$EVENT"
        exit $E_FORBIDEN
    fi
    interface="$i"
}

# Create ip vesta configuration
create_vesta_ip() {
    ip_data="OWNER='$user'"
    ip_data="$ip_data\nSTATUS='$ip_status'"
    ip_data="$ip_data\nNAME='$ip_name'"
    ip_data="$ip_data\nU_SYS_USERS=''"
    ip_data="$ip_data\nU_WEB_DOMAINS='0'"
    ip_data="$ip_data\nINTERFACE='$interface'"
    ip_data="$ip_data\nNETMASK='$mask'"
    ip_data="$ip_data\nDATE='$DATE'"
    echo -e "$ip_data" >$VESTA/data/ips/$ip
    chmod 660 $VESTA/data/ips/$ip
}

# Create ip address startup configuration
create_ip_startup() {
    ip_data="# Added by vesta $SCRIPT\nDEVICE=$iface"
    ip_data="$ip_data\nBOOTPROTO=static\nONBOOT=yes\nIPADDR=$ip"
    ip_data="$ip_data\nNETMASK=$mask"
    echo -e "$ip_data" > $iconf-$iface
}
