is_sys_ip_free() {
    # Parsing system ips
    ip_list=$(ifconfig|grep 'inet addr:'|cut -f 2 -d ':'|cut -f 1 -d " ")

    # Checking ip existance
    ip_check=$(echo "$ip_list"|grep -w "$ip")
    if [ -n "$ip_check" ] || [ -e "$V_IPS/$ip" ]; then
        echo "Error: IP exist"
        log_event 'debug' "$E_IP_EXIST $V_EVENT"
        exit  $E_IP_EXIST
    fi
}

get_next_interface_number() {
    # Parsing ifconfig
    i=$(ifconfig -a |grep -w "$interface"|cut -f1 -d ' '|\
        tail -n 1|cut -f 2 -d :)

    # Checking result
    if [ "$i" = "$interface" ]; then
        n=0
    else
        n=$((i + 1))
    fi
    echo ":$n"
}

is_sys_ip_valid() {
    # Parsing ifconfig
    check_ifc=$(/sbin/ifconfig |grep "inet addr:$ip")

    # Checking ip existance
    if [ ! -e "$V_IPS/$ip" ] || [ -z "$check_ifc" ]; then
        echo "Error: IP not exist"
        log_event 'debug' "$E_IP_NOTEXIST $V_EVENT"
        exit $E_IP_NOTEXIST
    fi
}

is_ip_key_empty() {
    key="$1"

    # Parsing ip
    string=$(cat $V_IPS/$ip )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Checkng key
    if [ ! -z "$value" ] && [ "$value" != '0' ]; then
        echo "Error: value is not empty = $value "
        log_event 'debug' "$E_VALUE_EXIST $V_EVENT"
        exit $E_VALUE_EXIST
    fi
}

update_sys_ip_value() {
    key="$1"
    value="$2"

    # Defining conf
    conf="$V_IPS/$ip"

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

is_ip_avalable() {
    # Checking ip existance
    if [ ! -e "$V_IPS/$ip" ]; then
        echo "Error: IP not exist"
        log_event 'debug' "$E_IP_NOTEXIST $V_EVENT"
        exit $E_IP_NOTEXIST
    fi

    # Parsing ip data
    ip_data=$(cat $V_IPS/$ip)
    ip_owner=$(echo "$ip_data" | grep 'OWNER=' | cut -f 2 -d \' )
    ip_status=$(echo "$ip_data" | grep 'STATUS=' | cut -f 2 -d \' )

    # Parsing user data
    user_owner=$(grep 'OWNER=' $V_USERS/$user/user.conf | cut -f 2 -d \')
    if [ "$user_owner" = "$ip_owner" ] && [ "$ip_status" = 'shared' ]; then
        ip_shared='yes'
    else
        ip_shared='no'
    fi

    if [ "$ip_owner" != "$user" ] && [ "$ip_shared" != 'yes' ]; then
        echo "Error: ip not owned by user"
        log_event 'debug' "$E_IP_NOTOWNED $V_EVENT"
        exit $E_IP_NOTOWNED
    fi
}

is_sys_ip_owner() {
    # Parsing ip
    ip_owner=$(grep 'OWNER=' $V_IPS/$ip|cut -f 2 -d \')
    if [ "$ip_owner" != "$user" ]; then
        echo "Error: IP not owned"
        log_event 'debug' "$E_IP_NOTOWNED $V_EVENT"
        exit $E_IP_NOTOWNED
    fi
}

get_ip_name() {
    # Prinitng name
    grep "NAME=" $V_IPS/$ip |cut -f 2 -d \'
}

increase_ip_value() {
    USER=$user
    web_key='U_WEB_DOMAINS'
    usr_key='U_SYS_USERS'

    # Parsing values
    current_web=$(grep "$web_key=" $V_IPS/$ip |cut -f 2 -d \')
    current_usr=$(grep "$usr_key=" $V_IPS/$ip |cut -f 2 -d \')

    # Checking result
    if [ -z "$current_web" ]; then
        echo "Error: Parsing error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
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
    sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" $V_IPS/$ip
    sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" $V_IPS/$ip
}

decrease_ip_value() {
    sip=${1-ip}
    USER=$user
    web_key='U_WEB_DOMAINS'
    usr_key='U_SYS_USERS'

    # Parsing values
    current_web=$(grep "$web_key=" $V_IPS/$sip |cut -f 2 -d \')
    current_usr=$(grep "$usr_key=" $V_IPS/$sip |cut -f 2 -d \')

    # Checking result
    if [ -z "$current_web" ]; then
        echo "Error: Parsing error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi

    # -1 webdomain
    new_web=$((current_web - 1))

    # -1 user
    check_ip=$(grep $sip $V_USERS/$user/web.conf |wc -l)
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
    sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" $V_IPS/$sip
    sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" $V_IPS/$sip
}

get_sys_ip_value() {
    key="$1"

    # Parsing domains
    string=$( cat $V_IPS/$ip )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Print value
    echo "$value"
}

change_domain_ip() {
    # Defining vars
    conf="$1"
    domain="$2"
    ip="$3"
    old_ip="$4"
    tpl_file="$5"

    # Get ServerName line
    serv_line=$(grep -n 'ServerName %domain%' "$tpl_file" |cut -f 1 -d :)

    # Get tpl_file last line
    last_line=$(wc -l $tpl_file|cut -f 1 -d ' ')

    # Get before line
    bfr_line=$((serv_line - 1))

    # Parsing httpd.conf
    str=$(grep -B $bfr_line -n "ServerName $domain" $conf|grep '<VirtualHost')

    # Checking integrity
    if [ -z "$str" ] || [ -z "$serv_line" ] || [ -z "$bfr_line" ]; then
        echo "Error: httpd parsing error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi

    # String number
    str_number=$(echo $str | sed -e "s/-/+/" | cut -f 1 -d '+')

    # Changing elog in config
    sed -i "$str_number s/$old_ip/$ip/g" $conf
}

get_current_interface() {
    # Parsing ifconfig
    i=$(/sbin/ifconfig |grep -B1 "addr:$ip "|head -n 1 |cut -f 1 -d ' ')

    # Checking result
    if [ -z "$i" ]; then
        echo "Error: IP not exist"
        log_event 'debug' "$E_IP_NOTEXIST $V_EVENT"
        exit $E_IP_NOTEXIST
    fi

    # Checking ip is alias
    check_alias=$(echo $i| cut -s -f 2 -d :)
    if [ -z "$check_alias" ]; then
        echo "Error: IP is first on interface"
        log_event 'debug' "$E_IP_FIRST $V_EVENT"
        exit $E_IP_FIRST
    fi
    echo "$i"
}

ip_json_single_list() {
    # Definigng variables
    IP="$ip"    # ip
    i=1         # iterator

    # Define words number
    last_word=$(echo "$fields" | wc -w)

    # Reading file line by line
    line=$(cat $V_IPS/$IP)

    # Print top bracket
    echo '{'

    # Parsing key=value
    for key in $line; do
        eval ${key%%=*}=${key#*=}
    done

    # Starting output loop
    for field in $fields; do
        # Parsing key=value
        eval value=$field
        # Checking first field
        if [ "$i" -eq 1 ]; then
            echo -e "\t\"$value\": {"
        else
            if [ "$last_word" -eq "$i" ]; then
                echo -e "\t\t\"${field//$/}\": \"${value//,/, }\""
            else
                echo -e "\t\t\"${field//$/}\": \"${value//,/, }\","
            fi
        fi
    # Updating iterator
    i=$(( i + 1))
    done

    # If there was any output
    if [ -n "$value" ]; then
    echo -e "\t}"
    fi

    # Printing bottom json bracket
    echo -e "}"
}

ip_shell_single_list() {
    # Definigng variables
    IP="$ip"     # ip

    # Reading file line by line
    line=$(cat $V_IPS/$IP)

    # Parsing key=value
    for key in $line; do
        eval ${key%%=*}=${key#*=}
    done

    # Print result line
    for field in $fields; do 
        eval key="$field"
        echo "${field//$/}: $key "
    done
}

ip_json_list() {
    i='1'       # iterator
    end=$(($limit + $offset))   # last string

    # Definining user list
    ip_list=$(ls $V_IPS/)

    # Print top bracket
    echo '{'

    # Starting main loop
    for IP in $ip_list; do

        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Reading user data
            ip_data=$(cat $V_IPS/$IP)

            # Parsing key/value config
            for key in $ip_data; do
                eval ${key%%=*}=${key#*=}
            done

            # Checking !first line to print bracket with coma
            if [ "$i" -ne "$offset" ]; then
                echo -e "\t},"
            fi

            # Defining local iterator and words count
            j='1'
            last_word=$(echo "$fields" | wc -w)

            # Print data
            for field in $fields; do
                eval value=$field
                # Checking parrent key
                if [ "$j" -eq 1 ]; then
                    echo -e "\t\"$value\": {"
                else
                    if [ "$j" -eq "$last_word" ]; then
                        echo -e "\t\t\"${field//$/}\": \"${value//,/, }\""
                    else
                        echo -e "\t\t\"${field//$/}\": \"${value//,/, }\","
                    fi
                fi
                j=$(($j + 1))
            done
        fi
        i=$(($i + 1))
    done

    # If there was any output
    if [ -n "$value" ]; then
        echo -e "\t}"
    fi

    # Printing bottom json bracket
    echo '}'
}

ip_shell_list() {
    i='1'       # iterator
    end=$(($limit + $offset))   # last string

    # Definining ip list
    ip_list=$(ls $V_IPS/)

    # Print brief info
    echo "${fields//$/}"
    for a in $fields; do
        echo -e "--------- \c"
    done
    echo    # new line

    # Starting main loop
    for IP in $ip_list; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Reading user data
            ip_data=$(cat $V_IPS/$IP)

            # Parsing key/value config
            for key in $ip_data; do
                eval ${key%%=*}=${key#*=}
            done

            # Print result line
            eval echo "$fields"
        fi
        i=$(($i + 1))
    done
}

ip_user_json_list() {
    i='1'   # iterator
    end=$(($limit + $offset))   # last string
    user_ip=$(grep -l "OWNER='$user'" $V_IPS/*)
    owner_ip=$(grep -l -A2 "OWNER='$owner'" $V_IPS/*|grep "STATUS='shared'"|\
                cut -f 1 -d -)

    # Definining ip list
    ip_list=$(echo -e "$user_ip\n$owner_ip"|sort|uniq)

    # Print top bracket
    echo '{'

    # Starting main loop
    for IP in ${ip_list//$V_IPS\//}; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Reading user data
            ip_data=$(cat $V_IPS/$IP)

            # Parsing key/value config
            for key in $ip_data; do
                eval ${key%%=*}=${key#*=}
            done

            # Checking !first line to print bracket with coma
            if [ "$i" -ne "$offset" ]; then
                echo -e "\t},"
            fi

            # Defining local iterator and words count
            j='1'
            last_word=$(echo "$fields"| wc -w)

            # Print data
            for field in $fields; do
                eval value=$field
                # Checking parrent key
                if [ "$j" -eq 1 ]; then
                    echo -e "\t\"$value\": {"
                else
                    if [ "$j" -eq "$last_word" ]; then
                        echo -e "\t\t\"${field//$/}\": \"${value//,/, }\""
                    else
                        echo -e "\t\t\"${field//$/}\": \"${value//,/, }\","
                    fi
                fi
                j=$(($j + 1))
            done
        fi
        i=$(($i + 1))
    done

    # If there was any output
    if [ -n "$value" ]; then
        echo -e "\t}"
    fi

    # Printing bottom json bracket
    echo '}'
}

ip_user_shell_list() {
    i='1'			# iterator
    end=$(($limit + $offset))	# last string
    user_ip=$(grep -l "OWNER='$user'" $V_IPS/*)
    owner_ip=$(grep -A2 "OWNER='$owner'" $V_IPS/* |grep "STATUS='shared'" |\
                cut -f 1 -d -)

    # Definining ip list
    ip_list=$(echo -e "$user_ip\n$owner_ip"|sort|uniq)

    # Print brief info
    echo "${fields//$/}"
    for a in $fields; do
        echo -e "--------- \c"
    done
    echo    # new line

    # Starting main loop
    for IP in ${ip_list//$V_IPS\//}; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Reading user data
            ip_data=$(cat $V_IPS/$IP)

            # Parsing key/value config
            for key in $ip_data; do
                eval ${key%%=*}=${key#*=}
            done

            # Print result line
            eval echo "$fields"
        fi
        i=$(($i + 1))
    done
}

ip_add_vesta() {
    # Filling ip values
    ip_data="OWNER='$owner'"
    ip_data="$ip_data\nSTATUS='$ip_status'"
    ip_data="$ip_data\nNAME='$ip_name'"
    ip_data="$ip_data\nU_SYS_USERS=''"
    ip_data="$ip_data\nU_WEB_DOMAINS='0'"
    ip_data="$ip_data\nINTERFACE='$interface'"
    ip_data="$ip_data\nNETMASK='$mask'"
    ip_data="$ip_data\nDATE='$V_DATE'"

    # Adding ip
    echo -e "$ip_data" >$V_IPS/$ip
}

ip_add_startup() {
    # Filling ip values
    ip_data="# Added by vesta $V_SCRIPT"
    ip_data="$ip_data\nDEVICE=$iface"
    ip_data="$ip_data\nBOOTPROTO=static\nONBOOT=yes"
    ip_data="$ip_data\nIPADDR=$ip"
    ip_data="$ip_data\nNETMASK=$mask"

    # Adding ip
    echo -e "$ip_data" >$iconf-$iface
}

ipint_json_list() {
    interfaces=$(cat /proc/net/dev|grep :|cut -f 1 -d :|sed -e "s/ //g")
    int_counter=$(echo "$interfaces"|wc -l)
    i=1
    # Print top bracket
    echo '['
    # Listing servers
    for interface in $interfaces; do
        if [ "$i" -lt "$int_counter" ]; then
            echo -e  "\t\"$interface\","
        else
            echo -e  "\t\"$interface\""
        fi
        i=$((i + 1))
    done
    echo "]"
}

ipint_shell_list() {
    interfaces=$(cat /proc/net/dev|grep :|cut -f 1 -d :|sed -e "s/ //g")
    # Print result
    echo "INTERFACES"
    echo "----------"
    for interface in $interfaces; do
        echo "$interface"
    done
}

ip_owner_search(){
    for ip in $(ls $V_IPS/); do
        check_owner=$(grep "OWNER='$user'" $V_IPS/$ip)
        if [ ! -z "$check_owner" ]; then
            echo "$ip"
        fi
    done
}
