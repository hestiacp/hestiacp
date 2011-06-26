# Checking domain existance
is_domain_new() {
    output_mode="$1"
    search_dom=${2-$domain}

    # Parsing domain values
    check_domain=$(grep -F "DOMAIN='$search_dom'" $V_USERS/*/*.conf| \
        grep -v crontab.conf)

    # Parsing alias values
    check_alias=$(grep -F 'ALIAS=' $V_USERS/*/*.conf | \
        grep -v crontab.conf | \
        awk -F "ALIAS=" '{print $2}' | \
        cut -f 2 -d \' | \
        sed -e "s/,/\n/g" | \
        grep "^$search_dom$" )

    # Checking result
    if [ ! -z "$check_domain" ] || [ ! -z "$check_alias" ]; then
        if [ "$output_mode" != 'quiet' ]; then
            echo "Error: domain exist"
            log_event 'debug' "$E_DOM_EXIST $V_EVENT"
            exit $E_DOM_EXIST
        fi
        return $E_DOM_EXIST
    fi

}

is_domain_owner() {
    search_dom=${1-$domain}

    # Parsing domain values
    check_domain=$(grep "DOMAIN='$search_dom'" $V_USERS/$user/*.conf)

    # Parsing alias values
    check_alias=$(grep 'ALIAS=' $V_USERS/$user/*.conf | \
        awk -F "ALIAS=" '{print $2}' | \
        cut -f 2 -d \' | \
        sed -e "s/,/\n/g" | \
        grep "^$search_dom$" )

    # Checking result
    if [ -z "$check_domain" ] && [ -z "$check_alias" ]; then
        echo "Error: domain not owned"
        log_event 'debug' "$E_DOM_NOTOWNED $V_EVENT"
        exit $E_DOM_NOTOWNED
    fi
}

is_dns_domain_free() {
    # Parsing domain values
    check_domain=$(grep -F "DOMAIN='$domain'" $V_USERS/$user/dns.conf)

    # Checking result
    if [ ! -z "$check_domain" ]; then
        echo "Error: domain exist"
        log_event 'debug' "$E_DOM_EXIST $V_EVENT"
        exit $E_DOM_EXIST
    fi
}

is_web_domain_free() {
    search_dom=${1-$domain}
    # Parsing domain values
    check_domain=$(grep -F "IN='$search_dom'" $V_USERS/$user/web_domains.conf)

    # Parsing alias values
    check_alias=$(grep -F 'ALIAS=' $V_USERS/$user/web_domains.conf | \
        awk -F "ALIAS=" '{print $2}' | \
        cut -f 2 -d \' | \
        sed -e "s/,/\n/g" | \
        grep "^$check_domain$" )

    # Checking result
    if [ ! -z "$check_domain" ] || [ ! -z "$check_alias" ]; then
        echo "Error: domain exist"
        log_event 'debug' "$E_DOM_EXIST $V_EVENT"
        exit $E_DOM_EXIST
    fi
}

is_dns_domain_valid() {
    # Parsing domain values
    check_domain=$(grep -F "DOMAIN='$domain'" $V_USERS/$user/dns.conf)

    # Checking result
    if [ -z "$check_domain" ]; then
        echo "Error: domain not exist"
        log_event 'debug' "$E_DOM_NOTEXIST $V_EVENT"
        exit $E_DOM_NOTEXIST
    fi
}

is_web_domain_valid() {
    # Parsing domain values
    check_domain=$(grep -F "DOMAIN='$domain'" $V_USERS/$user/web_domains.conf)

    # Checking result
    if [ -z "$check_domain" ]; then
        echo "Error: domain not exist"
        log_event 'debug' "$E_DOM_NOTEXIST $V_EVENT"
        exit $E_DOM_NOTEXIST
    fi
}

is_domain_suspended() {
    config_type="$1"
    # Parsing domain values
    check_domain=$(grep "DOMAIN='$domain'" $V_USERS/$user/$config_type.conf|\
        grep "SUSPEND='yes'")

    # Checking result
    if [ ! -z "$check_domain" ]; then
        echo "Error: domain suspended"
        log_event 'debug' "$E_DOM_SUSPENDED $V_EVENT"
        exit $E_DOM_SUSPENDED
    fi
}

is_domain_unsuspended() {
    config_type="$1"
    # Parsing domain values
    check_domain=$(grep "DOMAIN='$domain'" $V_USERS/$user/$config_type.conf|\
        grep "SUSPEND='no'")

    # Checking result
    if [ ! -z "$check_domain" ]; then
        echo "Error: domain unsuspended"
        log_event 'debug' "$E_DOM_UNSUSPENDED $V_EVENT"
        exit $E_DOM_UNSUSPENDED
    fi
}

update_domain_zone() {
    # Definigng variables
    line=$(grep "DOMAIN='$domain'" $V_USERS/$user/dns.conf)
    fields='$RECORD\t$TTL\tIN\t$TYPE\t$VALUE'
    conf="/etc/namedb/$domain.db"

    # Checking serial
    if [ -e $conf ]; then
        zn_serial=$(head $conf|grep 'SOA' -A1|tail -n 1|sed -e "s/ //g")
        s_date=$(echo ${zn_serial:0:8})
        c_date=$(date +'%Y%m%d')
        if [ "$s_date" == "$c_date" ]; then
            cur_value=$(echo ${zn_serial:8} )
            new_value=$(expr $cur_value + 1 )
            len_value=$(expr length $new_value)
            if [ 1 -eq "$len_value" ]; then
                new_value='0'$new_value
            fi
            serial="$c_date""$new_value"
        else
            serial="$(date +'%Y%m%d01')"
        fi
    else
        serial="$(date +'%Y%m%d01')"
    fi

    # Parsing dns domains conf
    for key in $line; do
        eval ${key%%=*}=${key#*=}
    done

    # Converting SOA to ascii
    SOA=$(idn --quiet -a -t "$SOA")
    # Adding zone header
    echo "\$TTL $TTL
@    IN    SOA    $SOA.    root.$domain_idn. (
                                            $serial
                                            7200
                                            3600
                                            1209600
                                            180 )
" > $conf

    # Adding zone records
    while read line ; do
        # Defining new delimeter
        IFS=$'\n'
        # Parsing key=value
        for key in $(echo $line|sed -e "s/' /'\n/g"); do
            eval ${key%%=*}="${key#*=}"
        done

        # Converting utf records to ascii
        RECORD=$(idn --quiet -a -t "$RECORD")
        VALUE=$(idn --quiet -a -t "$VALUE")
        eval echo -e "\"$fields\""|sed -e "s/%quote%/'/g" >> $conf
    done < $V_USERS/$user/zones/$domain

}

get_next_dns_record() {
    # Parsing config
    curr_str=$(grep "ID=" $V_USERS/$user/zones/$domain|cut -f 2 -d \'|\
        sort -n|tail -n1)

    # Print result
    echo "$((curr_str +1))"
}

is_dns_record_free() {
    # Checking record id
    check_id=$(grep "ID='$id'" $V_USERS/$user/zones/$domain)

    if [ ! -z "$check_id" ]; then
        echo "Error: ID exist"
        log_event 'debug' "$E_ID_EXIST $V_EVENT"
        exit  $E_ID_EXIST
    fi
}

sort_dns_records() {
    # Defining conf
    conf="$V_USERS/$user/zones/$domain"
    cat $conf |sort -n -k 2 -t \' >$conf.tmp
    mv -f $conf.tmp $conf
}

httpd_add_config() {
    # Adding template to config
    cat $tpl_file | \
        sed -e "s/%ip%/$ip/g" \
            -e "s/%port%/$port/g" \
            -e "s/%domain_idn%/$domain_idn/g" \
            -e "s/%domain%/$domain/g" \
            -e "s/%user%/$user/g" \
            -e "s/%group%/$group/g" \
            -e "s/%home%/${V_HOME////\/}/g" \
            -e "s/%docroot%/${docroot////\/}/g" \
            -e "s/%email%/$email/g" \
            -e "s/%alias_idn%/${aliases_idn//,/ }/g" \
            -e "s/%alias%/${aliases//,/ }/g" \
            -e "s/%ssl_cert%/${ssl_cert////\/}/g" \
            -e "s/%ssl_key%/${ssl_key////\/}/g" \
            -e "s/%extentions%/$extentions/g" \
    >> $conf
}

httpd_change_config() {
    # Get ServerName line
    serv_line=$(grep -n 'ServerName %domain_idn%' "$tpl_file" | cut -f 1 -d :)

    # Get tpl_file last line
    last_line=$(wc -l $tpl_file | cut -f 1 -d ' ')

    # Get before line
    bfr_line=$((serv_line - 1))

    # Get after line
    aftr_line=$((last_line - serv_line - 1))

    # Parsing httpd.conf
    vhost=$(grep -A $aftr_line -B $bfr_line -n "ServerName $domain_idn" $conf)

    # Searching prhase
    str=$(echo "$vhost" | grep -F "$search_phrase" | head -n 1)

    # Checking parsing result
    if [ -z "$str" ] || [ -z "$serv_line" ] || [ -z "$aftr_line" ]; then
        echo "Error: httpd parsing error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi

    # Parsing string position and content
    str_numb=$(echo "$str" | sed -e "s/-/=/" | cut -f 1 -d '=')
    str_cont=$(echo "$str" | sed -e "s/-/=/" | cut -f 2 -d '=')

    # Escaping chars
    str_repl=$(echo "$str_repl" | sed \
        -e 's/\\/\\\\/g' \
        -e 's/&/\\&/g' \
        -e 's/\//\\\//g')

    # Changing config
    sed -i  "$str_numb s/.*/$str_repl/" $conf
}

get_web_domain_value() {
    key="$1"

    # Parsing domains
    string=$( grep "DOMAIN='$domain'" $V_USERS/$user/web_domains.conf )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Print value
    echo "$value"
}

get_dns_domain_value() {
    key="$1"

    # Parsing domains
    string=$( grep "DOMAIN='$domain'" $V_USERS/$user/dns.conf )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Print value
    echo "$value"
}

update_web_domain_value() {
    key="$1"
    value="$2"

    # Defining conf
    conf="$V_USERS/$user/web_domains.conf"

    # Parsing conf
    domain_str=$(grep -n "DOMAIN='$domain'" $conf)
    str_number=$(echo $domain_str | cut -f 1 -d ':')
    str=$(echo $domain_str | cut -f 2 -d ':')

    # Reading key=values
    for keys in $str; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Defining clean key
    c_key=$(echo "${key//$/}")

    eval old="${key}"

    # Escaping slashes
    old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    new=$(echo "$value" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')

    # Updating conf
    sed -i "$str_number s/$c_key='${old//\*/\\*}'/$c_key='${new//\*/\\*}'/g"\
     $conf
}

update_dns_domain_value() {
    key="$1"
    value="$2"

    # Defining conf
    conf="$V_USERS/$user/dns.conf"

    # Parsing conf
    domain_str=$(grep -n "DOMAIN='$domain'" $conf)
    str_number=$(echo $domain_str | cut -f 1 -d ':')
    str=$(echo $domain_str | cut -f 2 -d ':')

    # Reading key=values
    for keys in $str; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Defining clean key
    c_key=$(echo "${key//$/}")

    eval old="${key}"

    # Escaping slashes
    old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    new=$(echo "$value" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')

    # Updating conf
    sed -i "$str_number s/$c_key='${old//\*/\\*}'/$c_key='${new//\*/\\*}'/g"\
     $conf
}

is_web_domain_key_empty() {
    key="$1"

    # Parsing domains
    string=$( grep "DOMAIN='$domain'" $V_USERS/$user/web_domains.conf )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Checkng key
    if [ ! -z "$value" ] && [ "$value" != 'no' ]; then
        echo "Error: value is not empty = $value"
        log_event 'debug' "$E_VALUE_EXIST $V_EVENT"
        exit $E_VALUE_EXIST
    fi
}

is_dns_record_valid() {
    # Checking record id
    check_id=$(grep "^ID='$id'" $V_USERS/$user/zones/$domain)

    if [ -z "$check_id" ]; then
        echo "Error: ID not exist"
        log_event 'debug' "$E_ID_NOTEXIST $V_EVENT"
        exit $E_ID_NOTEXIST
    fi
}

is_web_domain_value_exist() {
    key="$1"

    # Parsing domains
    string=$( grep "DOMAIN='$domain'" $V_USERS/$user/web_domains.conf )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Checking result
    if [ -z "$value" ] || [ "$value" = 'no' ]; then
        echo "Error: ${key//$/} is empty"
        log_event 'debug' "$E_VALUE_EMPTY $V_EVENT"
        exit $E_VALUE_EMPTY
    fi
}

is_dns_domain_value_exist() {
    key="$1"

    # Parsing domains
    string=$( grep "DOMAIN='$domain'" $V_USERS/$user/dns.conf )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Checking result
    if [ -z "$value" ] || [ "$value" = 'no' ]; then
        echo "Error: ${key//$/} is empty"
        log_event 'debug' "$E_VALUE_EMPTY $V_EVENT"
        exit $E_VALUE_EXIST
    fi
}


httpd_del_config() {
    # Get ServerName line
    serv_line=$(grep -n 'ServerName %domain_idn%' "$tpl_file" |cut -f 1 -d :)

    # Get tpl_file last line
    last_line=$(wc -l $tpl_file|cut -f 1 -d ' ')

    # Get before line
    bfr_line=$((serv_line - 1))

    # Parsing httpd.conf
    str=$(grep -B $bfr_line -n "ServerName $domain_idn" $conf |\
            grep '<VirtualHost')

    # Checking result
    if [ -z "$str" ] || [ -z "$serv_line" ] || [ -z "$bfr_line" ]; then
        echo "Error: httpd parsing error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi

    # String number
    top_line=$(echo $str | sed -e "s/-/+/" | cut -f 1 -d '+')
    bottom_line=$((top_line + last_line - 1))
    sed -i "$top_line,$bottom_line d" $conf
}

del_dns_domain() {
    conf="$V_USERS/$user/dns.conf"

    # Parsing domains
    string=$( grep -n "DOMAIN='$domain'" $conf | cut -f 1 -d : )
    if [ -z "$string" ]; then
        echo "Error: parse error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi
    sed -i "$string d" $conf
    rm -f $V_USERS/$user/zones/$domain
}

del_web_domain() {
    conf="$V_USERS/$user/web_domains.conf"

    # Parsing domains
    string=$( grep -n "DOMAIN='$domain'" $conf | cut -f 1 -d : )
    if [ -z "$string" ]; then
        echo "Error: parse error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi
    sed -i "$string d" $conf
}


dns_shell_list() {
    i='1'       # iterator
    end=$(($limit + $offset))   # last string

    # Print brief info
    echo "${fields//$/}"
    for a in $fields; do
        echo -e "------ \c"
    done
    echo

    # Reading file line by line
    while read line ; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Defining new delimeter
            IFS=$'\n'
            # Parsing key=value
            for key in $(echo $line|sed -e "s/' /'\n/g"); do
                eval ${key%%=*}="${key#*=}"
            done
            # Print result line
            eval echo "\"$fields\""|sed -e "s/%quote%/'/g"
        fi
        i=$(($i + 1))
    done < $conf
}

dns_json_list() {
    i='1'        # iterator
    end=$(($limit + $offset))   # last string

    # Print top bracket
    echo '{'

    # Reading file line by line
    while read line ; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Defining new delimeter
            IFS=$'\n'
            # Parsing key=value
            for key in $(echo $line|sed -e "s/' /'\n/g"); do
                eval ${key%%=*}="${key#*=}"
            done

            # Checking !first line to print bracket
            if [ "$i" -ne "$offset" ]; then
                echo -e "\t},"
            fi

            j=1                 # local loop iterator
            last_word=$(echo "$fields" | wc -w)

            # Restoring old delimeter
            IFS=' '
            # Print data
            for field in $fields; do
                eval value=\"$field\"
                value=$(echo "$value"|sed -e 's/"/\\"/g' -e "s/%quote%/'/g")

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
    done < $conf

    # If there was any output
    if [ -n "$value" ]; then
        echo -e "\t}"
    fi

    # Printing bottom json bracket
    echo -e "}"
}

# Shell list for dns domain templates
dnstpl_shell_list() {
    # Definigng variables
    i='1'                       # iterator
    end=$(($limit + $offset))	# last string

    # Listing files by mask
    for template in $(ls $V_DNSTPL/| grep '.descr'); do

        # Defining template name
        tpl_name="${template//.descr/}"

        # Defining template description
        tpl_descr=$(cat $V_DNSTPL/$template |grep '#')

        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Print result
            echo "----------"
            echo "TEMPLATE: $tpl_name"
            echo "${tpl_descr//# /}"
        fi
        i=$(($i + 1))
    done
}

# Json list for dns domain templates
dnstpl_json_list() {
    i=1         # iterator
    end=$(($limit + $offset))   # last string

    # Print top bracket
    echo '{'

    # Listing files by mask
    for template in $(ls $V_DNSTPL/| grep '.descr'); do

        # Defining template description
        descr=$(cat $V_DNSTPL/$template |grep '#'|sed -e ':a;N;$!ba;s/\n/ /g')

        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Checking !first line to print bracket
            if [ "$i" -ne "$offset" ]; then
                echo -e "\t},"
            fi

            # Defining template name
            tpl_name="${template//.descr/}"

            # Print result
            echo -e  "\t\"$tpl_name\": {"
            echo -e "\t\t\"DESCR\": \"${descr//# /}\""
        fi
        i=$(($i + 1))
    done

    # If there was any output
    if [ -n "$tpl_name" ]; then
        echo -e "\t}"
    fi

    echo "}"
}

dom_json_single_list() {
    i=1	        # iterator

    # Define words number
    last_word=$(echo "$fields" | wc -w)

    # Reading file line by line
    line=$(grep "DOMAIN='$domain'" $conf)

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

dom_shell_single_list() {

    # Reading file line by line
    line=$(grep "DOMAIN='$domain'" $conf)

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

webtpl_json_list() {
    i='1'       # iterator
    end=$(($limit + $offset))   # last string

    # Print top bracket
    echo '{'

    # Listing files by mask
    for template in $(echo "$templates" |sed -e "s/,/\n/g"); do
        # Defining template description
        descr=$(cat $V_WEBTPL/apache_$template.descr|grep '#'|\
            sed -e ':a;N;$!ba;s/\n/ /g')
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Checking !first line to print bracket
            if [ "$i" -ne "$offset" ]; then
                echo -e "\t},"
            fi

            # Print result
            echo -e  "\t\"$template\": {"
            echo -e "\t\t\"DESCR\": \"${descr//# /}\""
        fi
        i=$(($i + 1))
    done

    # If there was any output
    if [ -n "$template" ]; then
        echo -e "\t}"
    fi
    echo "}"
}

webtpl_shell_list() {
    i='1'       # iterator
    end=$(($limit + $offset))   # last string

    # Listing files by mask
    for template in $(echo "$templates" |sed -e "s/,/\n/g"); do
        # Defining template description
        tpl_descr=$(cat $V_WEBTPL/apache_$template.descr |grep '#')
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Print result
            echo "----------"
            echo "TEMPLATE: $template"
            echo "${tpl_descr//# /}"
        fi
        i=$(($i + 1))
    done
}

dom_clear_search(){
    # Defining delimeter
    IFS=$'\n'

    # Reading file line by line
    for line in $(grep $search_string $conf); do
        # Parsing key=val
        for key in $line; do
            eval ${key%%=*}=${key#*=}
        done
        # Print result line
        eval echo "$field"
    done
}

dom_clear_list() {
    # Reading file line by line
    while read line ; do

        # Parsing key=value
        for key in $line; do
            eval ${key%%=*}=${key#*=}
        done

        # Print result line
        eval echo "$field"
    done < $conf
}

namehost_ip_support() {
    #Checking web system
    if [ "$WEB_SYSTEM" = 'apache' ]; then
        # Checking httpd config for NameHost string number
        conf_line=$(grep -n "NameVirtual" $conf|tail -n 1|cut -f 1 -d ':')
        if [ ! -z "$conf_line" ]; then
            conf_ins=$((conf_line + 1))	        # inster into next line
        else
            conf_ins='1'        # insert into first line
        fi

        # Checking ssl support
        if [ "$WEB_SSL" = 'mod_ssl' ]; then
            ssl_port=$(get_web_port_ssl)        # calling internal function
            sed -i "$conf_ins i NameVirtualHost $ip:$ssl_port" $conf
        fi
        port=$(get_web_port)    # calling internal function
        sed -i "$conf_ins i NameVirtualHost $ip:$port" $conf

        # Checking proxy support
        if [ "$PROXY_SYSTEM" = 'nginx' ]; then
            cat $V_WEBTPL/ngingx_ip.tpl | sed -e "s/%ip%/$ip/g" \
             -e "s/%web_port%/$port/g" -e "s/%proxy_port%/80/g" >>$nconf

            # Adding to rpaf ip pool as well
            ips=$(grep 'RPAFproxy_ips' $rconf)
            sed -i "s/$ips/$ips $ip/g" $rconf
        fi

        # Scheduling restart
        web_restart='yes'
    fi
}

namehost_ip_disable() {
    #Checking web system
    if [ "$WEB_SYSTEM" = 'apache' ]; then
        sed -i "/NameVirtualHost $ip:/d" $conf

        # Checking proxy support
        if [ "$PROXY_SYSTEM" = 'nginx' ]; then
            tpl_ln=$(wc -l $V_WEBTPL/ngingx_ip.tpl | cut -f 1 -d ' ')
            ip_line=$(grep -n "%ip%" $V_WEBTPL/ngingx_ip.tpl |head -n1 |\
                cut -f 1 -d :)

            conf_line=$(grep -n -w $ip $nconf|head -n1|cut -f 1 -d :)

            # Checking parsed lines
            if [ -z "$tpl_ln" ] || [ -z "$ip_line" ] || [ -z "$conf_line" ]
            then
                echo "Error: nginx config paring error"
                log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
                exit $E_PARSE_ERROR
            fi

            up_line=$((ip_line - 1))
            first_line=$((conf_line - up_line))
            last_line=$((conf_line - ip_line + tpl_ln))

            # Checking parsed lines
            if [ -z "$first_line" ] || [ -z "$last_line" ]; then
                echo "Error: nginx config paring error"
                log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
                exit $E_PARSE_ERROR
            fi
            sed -i "$first_line,$last_line d" $nconf

            # Deleting from rpaf ip pool as well
            ips=$(grep 'RPAFproxy_ips' $rconf)
            new_ips=$(echo "$ips"|sed -e "s/$ip//")
            sed -i "s/$ips/$new_ips/g" $rconf
        fi

        # Scheduling restart
        web_restart='yes'
    fi
}
