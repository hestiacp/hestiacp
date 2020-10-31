# Check ipv6 ownership
is_ipv6_owner() {
    owner=$(grep 'OWNER=' $HESTIA/data/ips/$ipv6 |cut -f 2 -d \')
    if [ "$owner" != "$user" ]; then
        check_result $E_FORBIDEN "$ipv6 is not owned by $user"
    fi
}

# Check if ipv6 address is free
is_ipv6_free() {
    if [ -e "$HESTIA/data/ips/$ipv6" ]; then
        check_result $E_EXISTS "$ipv6 is already exists"
    fi
}

# Get full interface name
get_ipv6_iface() {
    i=$(/sbin/ip addr |grep -w $interface |\
         awk '{print $NF}' |tail -n 1 |cut -f 2 -d :)
    if [ "$i" = "$interface" ]; then
        n=0
    else
        n=$((i + 1))
    fi
    echo "$interface:$n"
}


# Check ipv6 address speciefic value
is_ipv6_key_empty() {
    key="$1"
    string=$(cat $HESTIA/data/ips/$ipv6)
    eval $string
    eval value="$key"
    if [ ! -z "$value" ] && [ "$value" != '0' ]; then
        key="$(echo $key|sed -e "s/\$U_//")"
        check_result $E_EXISTS "IP6 is in use / $key = $value"
    fi
}

# Update ipv6 address value
update_ipv6_value() {
    key="$1"
    value="$2"
    conf="$HESTIA/data/ips/$ipv6"
    str=$(cat $conf)
    eval $str
    c_key=$(echo "${key//$/}")
    eval old="${key}"
    old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    new=$(echo "$value" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    sed -i "$str_number s/$c_key='${old//\*/\\*}'/$c_key='${new//\*/\\*}'/g"\
        $conf
}

# Get ipv6 name
get_ipv6_alias() {
    ip_name=$(grep "NAME=" $HESTIA/data/ips/$ipv6 2> /dev/null |cut -f 2 -d \')
    if [ ! -z "$ip_name" ]; then
        echo "${1//./-}.$ip_name"
    fi
}

# Increase ipv6 value
increase_ipv6_value() {
    sip=${1-ipv6}
    if [ "$sip" != "no" ] && [ ! -z "$sip" ]; then
        USER=$user
        web_key='U_WEB_DOMAINS'
        usr_key='U_SYS_USERS'
        current_web=$(grep "$web_key=" $HESTIA/data/ips/$sip |cut -f 2 -d \')
        current_usr=$(grep "$usr_key=" $HESTIA/data/ips/$sip |cut -f 2 -d \')
        if [ -z "$current_web" ]; then
            echo "Error: Parsing error"
            log_event "$E_PARSING" "$ARGUMENTS"
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
            $HESTIA/data/ips/$sip
        sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
            $HESTIA/data/ips/$sip
    fi
}

# Decrease ipv6 value
decrease_ipv6_value() {
    sip=${1-ipv6}
    if [ "$sip" != "no" ] && [ ! -z "$sip" ]; then
        USER=$user
        web_key='U_WEB_DOMAINS'
        usr_key='U_SYS_USERS'

        current_web=$(grep "$web_key=" $HESTIA/data/ips/$sip |cut -f 2 -d \')
        current_usr=$(grep "$usr_key=" $HESTIA/data/ips/$sip |cut -f 2 -d \')

        if [ -z "$current_web" ]; then
            check_result $E_PARSING "Parsing error"
        fi

        new_web=$((current_web - 1))
        check_ip=$(grep $sip $USER_DATA/web.conf |wc -l)
        if [ "$check_ip" -lt 2 ]; then
            new_usr=$(echo "$current_usr" |\
                sed "s/,/\n/g"|\
                sed "s/^$user$//g"|\
                sed "/^$/d"|\
                sed ':a;N;$!ba;s/\n/,/g')
        else
            new_usr="$current_usr"
        fi

        sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" \
            $HESTIA/data/ips/$sip
        sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
            $HESTIA/data/ips/$sip
    fi
}

# Get ipv6 address value
get_ipv6_value() {
    key="$1"
    string=$(cat $HESTIA/data/ips/$ip)
    eval $string
    eval value="$key"
    echo "$value"
}


# Get real ipv6 address
get_real_ipv6() {
    if [ -e "$HESTIA/data/ips/$1" ]; then
        echo $1
    else
        nat=$(grep -H "^NAT='$1'" $HESTIA/data/ips/*)
        if [ ! -z "$nat" ]; then
            echo "$nat" |cut -f 1 -d : |cut -f 7 -d /
        fi
    fi
}

# Convert CIDR to netmask
convert_cidrv6() {
    set -- $(( 5 - ($1 / 8) )) 255 255 255 255 \
        $(((255 << (8 - ($1 % 8))) & 255 )) 0 0 0
    if [[ $1 -gt 1 ]]; then
        shift $1
    else
        shift
    fi
    echo ${1-0}.${2-0}.${3-0}.${4-0}
}

# Convert netmask to CIDR
convert_netmaskv6() {
    nbits=0
    IFS=.
    for dec in $1 ; do
        case $dec in
            255) let nbits+=8;;
            254) let nbits+=7;;
            252) let nbits+=6;;
            248) let nbits+=5;;
            240) let nbits+=4;;
            224) let nbits+=3;;
            192) let nbits+=2;;
            128) let nbits+=1;;
            0);;
        esac
    done
    echo "$nbits"
}

# Get user ips
get_user_ip6s() {
    dedicated=$(grep -H -A10 "OWNER='$user'" $HESTIA/data/ips/* |grep "VERSION='6'")
    dedicated=$(echo "$dedicated" |cut -f 1 -d '-' |sed 's=.*/==')
    shared=$(grep -H -A10 "OWNER='admin'" $HESTIA/data/ips/* |grep -A10 shared |grep "VERSION='6'")
    shared=$(echo "$shared" |cut -f 1 -d '-' |sed 's=.*/==' |cut -f 1 -d \-)
    for dedicated_ip in $dedicated; do
        shared=$(echo "$shared" |grep -v $dedicated_ip)
    done
    echo -e "$dedicated\n$shared" |sed "/^$/d"
}

# Get user ipv6
get_user_ipv6() {
    ipv6=$(get_user_ip6s |head -n1)
    if [ -z "$ipv6" ]; then
        ipv6="no"
        #check_result $E_NOTEXIST "no IP6 is available"
    fi
}

# Validate ipv6 address
is_ipv6_valid() {
    ipv6="$1"
    if [ ! -e "$HESTIA/data/ips/$1" ]; then
        check_result $E_NOTEXIST "IP6 $1 doesn't exist"
    fi
    if [ ! -z $2 ]; then
        ip_data=$(cat $HESTIA/data/ips/$1)
        ip_owner=$(echo "$ip_data" |grep OWNER= |cut -f2 -d \')
        ip_status=$(echo "$ip_data" |grep STATUS= |cut -f2 -d \')
        if [ "$ip_owner" != "$user" ] && [ "$ip_status" = 'dedicated' ]; then
            check_result $E_FORBIDEN "$user user can't use IP6 $1"
        fi
        get_user_owner
        if [ "$ip_owner" != "$user" ] && [ "$ip_owner" != "$owner" ]; then
            check_result $E_FORBIDEN "$user user can't use IP6 $1"
        fi
    fi
}