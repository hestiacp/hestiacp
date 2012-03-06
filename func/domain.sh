# Checking domain existance
is_domain_new() {
    config_type="$1"
    dom=${2-$domain}
    check_all=$(grep -w $dom $V_USERS/*/*.conf)
    if [ ! -z "$check_all" ]; then
        check_ownership=$(grep -w $dom $USER_DATA/*.conf)
        if [ ! -z "$check_ownership" ]; then
            check_type=$(grep -w $dom $USER_DATA/$config_type.conf)
            if [ ! -z "$check_type" ]; then
                echo "Error: domain $dom exist"
                log_event 'debug' "$E_EXISTS $EVENT"
                exit $E_EXISTS
            fi
        else
            echo "Error: domain $dom exist"
            log_event 'debug' "$E_EXISTS $EVENT"
            exit $E_EXISTS
        fi
    fi
}

is_domain_suspended() {
    config_type="$1"
    # Parsing domain values
    check_domain=$(grep "DOMAIN='$domain'" $USER_DATA/$config_type.conf|\
        grep "SUSPENDED='yes'")

    # Checking result
    if [ ! -z "$check_domain" ]; then
        echo "Error: domain $domain is suspended"
        log_event 'debug' "$E_SUSPENDED $EVENT"
        exit $E_SUSPENDED
    fi
}

is_domain_unsuspended() {
    config_type="$1"
    # Parsing domain values
    check_domain=$(grep "DOMAIN='$domain'" $USER_DATA/$config_type.conf|\
        grep "SUSPENDED='no'")

    # Checking result
    if [ ! -z "$check_domain" ]; then
        echo "Error: domain unsuspended"
        log_event 'debug' "$E_UNSUSPENDED $EVENT"
        exit $E_UNSUSPENDED
    fi
}

update_domain_zone() {
    # Definigng variables
    line=$(grep "DOMAIN='$domain'" $USER_DATA/dns.conf)
    fields='$RECORD\t$TTL\tIN\t$TYPE\t$VALUE'

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
    eval $line

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
        #VALUE=$(idn --quiet -a -t "$VALUE")
        eval echo -e "\"$fields\""|sed -e "s/%quote%/'/g" >> $conf
    done < $USER_DATA/dns/$domain
}

get_next_dns_record() {
    # Parsing config
    curr_str=$(grep "ID=" $USER_DATA/dns/$domain|cut -f 2 -d \'|\
        sort -n|tail -n1)

    # Print result
    echo "$((curr_str +1))"
}

is_dns_record_free() {
    # Checking record id
    check_id=$(grep "ID='$id'" $USER_DATA/dns/$domain)

    if [ ! -z "$check_id" ]; then
        echo "Error: ID exist"
        log_event 'debug' "$E_EXISTS $EVENT"
        exit  $E_EXISTS
    fi
}

sort_dns_records() {
    # Defining conf
    conf="$USER_DATA/dns/$domain"
    cat $conf |sort -n -k 2 -t \' >$conf.tmp
    mv -f $conf.tmp $conf
}

add_web_config() {
    # Adding template to config
    cat $tpl_file | \
        sed -e "s/%ip%/$ip/g" \
            -e "s/%web_port%/$WEB_PORT/g" \
            -e "s/%web_ssl_port%/$WEB_SSL_PORT/g" \
            -e "s/%proxy_string%/${proxy_string////\/}/g" \
            -e "s/%proxy_port%/$PROXY_PORT/g" \
            -e "s/%proxy_ssl_port%/$PROXY_SSL_PORT/g" \
            -e "s/%domain_idn%/$domain_idn/g" \
            -e "s/%domain%/$domain/g" \
            -e "s/%user%/$user/g" \
            -e "s/%group%/$group/g" \
            -e "s/%home%/${HOMEDIR////\/}/g" \
            -e "s/%docroot%/${docroot////\/}/g" \
            -e "s/%docroot_string%/${docroot_string////\/}/g" \
            -e "s/%email%/$email/g" \
            -e "s/%alias_string%/$alias_string/g" \
            -e "s/%alias_idn%/${aliases_idn//,/ }/g" \
            -e "s/%alias%/${aliases//,/ }/g" \
            -e "s/%ssl_crt%/${ssl_crt////\/}/g" \
            -e "s/%ssl_key%/${ssl_key////\/}/g" \
            -e "s/%ssl_pem%/${ssl_pem////\/}/g" \
            -e "s/%ssl_ca_str%/${ssl_ca_str////\/}/g" \
            -e "s/%nginx_extentions%/${NGINX_EXT//,/|}/g" \
            -e "s/%elog%/$elog/g" \
            -e "s/%cgi%/$cgi/g" \
            -e "s/%cgi_option%/$cgi_option/g" \
    >> $conf
}

get_web_config_brds() {
    # Defining template borders
    serv_line=$(grep -ni 'Name %domain_idn%' "$tpl_file" |cut -f 1 -d :)
    if [ -z "$serv_line" ]; then
        log_event 'debug' "$E_PARSING $EVENT"
        return $E_PARSING
    fi

    # Template lines
    last_line=$(wc -l $tpl_file|cut -f 1 -d ' ')
    bfr_line=$((serv_line - 1))
    aftr_line=$((last_line - serv_line - 1))

    # Config lines
    str=$(grep -ni "Name $domain_idn" $conf | cut -f 1 -d :)
    top_line=$((str - serv_line + 1))
    bottom_line=$((top_line + last_line -1))

    # Check for multialias (8k alias issue)
    multi=$(sed -n "$top_line,$bottom_line p" $conf |grep ServerAlias |wc -l)
    if [ "$multi" -ge 2 ]; then
        bottom_line=$((bottom_line + multi -1))
    fi

}

change_web_config() {
    # Get config borders
    get_web_config_brds || exit $?

    # Parsing config
    vhost=$(grep -A $aftr_line -B $bfr_line -ni "Name $domain_idn" $conf)
    str=$(echo "$vhost" | grep -F "$search_phrase" | head -n 1)

    # Parsing string position and content
    str_numb=$(echo "$str" | sed -e "s/-/=/" | cut -f 1 -d '=')
    str_cont=$(echo "$str" | sed -e "s/-/=/" | cut -f 2 -d '=')

    # Escaping chars
    str_repl=$(echo "$str_repl" | sed \
        -e 's/\\/\\\\/g' \
        -e 's/&/\\&/g' \
        -e 's/\//\\\//g')

    # Changing config
    if [ ! -z "$str" ]; then
        sed -i  "$str_numb s/.*/$str_repl/" $conf
    fi
}

replace_web_config() {
    # Get config borders
    get_web_config_brds || exit $?

    # Escaping chars
    clean_new=$(echo "$new" | sed \
        -e 's/\\/\\\\/g' \
        -e 's/&/\\&/g' \
        -e 's/\//\\\//g')

    clean_old=$(echo "$old" | sed \
        -e 's/\\/\\\\/g' \
        -e 's/&/\\&/g' \
        -e 's/\//\\\//g')

    # Replacing string in config
    sed -i  "$top_line,$bottom_line s/$clean_old/$clean_new/" $conf
}

get_domain_value() {
    conf_type="$1"
    key="$2"
    default_str="DOMAIN='$domain'"
    search_str="${3-DOMAIN=$search_str}"

    # Parsing config
    string=$(grep "$search_str" $USER_DATA/$conf_type.conf )

    # Parsing key=value
    eval $string

    # Self reference
    eval value="$key"

    # Print value
    echo "$value"
}

get_domain_values() {
    # Defining domain parameters
    for line in $(grep "DOMAIN='$domain'" $USER_DATA/$1.conf); do
        # Assing key=value
        eval $line
    done
}

update_domain_value() {
    conf_type="$1"
    key="$2"
    value="$3"
    default_str="DOMAIN='$domain'"
    search_str=${4-$default_str}

    # Defining conf
    conf="$USER_DATA/$conf_type.conf"

    # Parsing conf
    domain_str=$(grep -n "$search_str" $conf)
    str_number=$(echo $domain_str | cut -f 1 -d ':')
    str=$(echo $domain_str | cut -f 2 -d ':')

    # Reading key=values
    eval $str

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

is_domain_key_empty() {
    conf_type="$1"
    key="$2"

    # Parsing domains
    string=$( grep "DOMAIN='$domain'" $USER_DATA/$conf_type.conf )

    # Parsing key=value
    eval $string

    # Self reference
    eval value="$key"

    # Checkng key
    if [ ! -z "$value" ] && [ "$value" != 'no' ]; then
        echo "Error: ${key//$} is not empty = $value"
        log_event 'debug' "$E_EXISTS $EVENT"
        exit $E_EXISTS
    fi
}

is_web_domain_cert_valid() {

    # Checking file existance
    if [ ! -e "$ssl_dir/$domain.crt" ] || [ ! -e "$ssl_dir/$domain.key" ]; then
        echo "Error: ssl certificate not exist"
        log_event 'debug' "$E_NOTEXIST $EVENT"
        exit $E_NOTEXIST
    fi

    # Checking certificate
    crt=$(openssl verify $ssl_dir/$domain.crt 2>/dev/null |grep '/C=')
    if [ -z "$crt" ]; then
        echo "Error: ssl certificate invalid"
        log_event 'debug' "$E_INVALID $EVENT"
        exit $E_INVALID
    fi

    # Checking certificate key
    openssl rsa -in "$ssl_dir/$domain.key" -check >/dev/null 2>/dev/null
    if [ "$?" -ne 0 ]; then
        echo "Error: ssl key invalid"
        log_event 'debug' "$E_INVALID $EVENT"
        exit $E_INVALID
    fi

    # Checking certificate authority
    if [ -e "$ssl_dir/$domain.ca" ]; then
        ca=$(openssl verify $ssl_dir/$domain.ca 2>/dev/null |grep '/C=')
        if [ -z "$ca" ]; then
            echo "Error: ssl certificate invalid"
            log_event 'debug' "$E_INVALID $EVENT"
            exit $E_INVALID
        fi
    fi

    # Checking server
    openssl s_server -quiet \
        -cert $ssl_dir/$domain.crt -key $ssl_dir/$domain.key &
    pid=$!
    sleep 1
    disown > /dev/null 2>&1
    kill $pid > /dev/null 2>&1
    result=$?
    if [ "$result" -ne '0' ]; then
        echo "Error: ssl certificate key pair invalid"
        log_event 'debug' "$E_INVALID $EVENT"
        exit $E_INVALID
    fi
}

is_dns_record_valid() {
    # Checking record id
    check_id=$(grep "^ID='$id'" $USER_DATA/dns/$domain)

    if [ -z "$check_id" ]; then
        echo "Error: ID not exist"
        log_event 'debug' "$E_NOTEXIST $EVENT"
        exit $E_NOTEXIST
    fi
}

is_domain_value_exist() {
    domain_type="$1"
    key="$2"

    # Parsing domains
    string=$( grep "DOMAIN='$domain'" $USER_DATA/$domain_type.conf )

    # Parsing key=value
    eval $string

    # Self reference
    eval value="$key"

    # Checking result
    if [ -z "$value" ] || [ "$value" = 'no' ]; then
        echo "Error: ${key//$/} is empty"
        log_event 'debug' "$E_NOTEXIST $EVENT"
        exit $E_NOTEXIST
    fi
}

del_web_config() {
    # Get config borders
    get_web_config_brds || exit $?

    # Deleting lines from config
    sed -i "$top_line,$bottom_line d" $conf
}

dom_clear_search(){
    # Defining delimeter
    IFS=$'\n'

    # Reading file line by line
    for line in $(grep $search_string $conf); do
        # Parsing key=val
        eval $line

        # Print result line
        eval echo "$field"
    done
}

dom_clear_list() {
    # Reading file line by line
    while read line ; do
        # Parsing key=value
        eval $line

        # Print result line
        eval echo "$field"
    done < $conf
}

namehost_ip_support() {
    # Checking httpd config for NameHost string number
    if [ "$WEB_SYSTEM" = 'apache' ]; then
        conf_line=$(grep -n "NameVirtual" $conf|tail -n 1|cut -f 1 -d ':')
        if [ ! -z "$conf_line" ]; then
            conf_ins=$((conf_line + 1))
        else
            conf_ins='1'
        fi

        # Checking ssl support
        if [ "$WEB_SSL" = 'mod_ssl' ]; then
            sed -i "$conf_ins i NameVirtualHost $ip:$WEB_SSL_PORT" $conf
            sed -i "$conf_ins i Listen $ip:$WEB_SSL_PORT" $conf
        fi

        sed -i "$conf_ins i NameVirtualHost $ip:$WEB_PORT" $conf
        sed -i "$conf_ins i Listen $ip:$WEB_PORT" $conf

        # Checking proxy support
        if [ "$PROXY_SYSTEM" = 'nginx' ]; then
            cat $WEBTPL/ngingx_ip.tpl | sed -e "s/%ip%/$ip/g" \
             -e "s/%web_port%/$WEB_PORT/g" \
            -e "s/%proxy_port%/$PROXY_PORT/g" >>$nconf

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
        sed -i "/Listen $ip:/d" $conf

        # Checking proxy support
        if [ "$PROXY_SYSTEM" = 'nginx' ]; then
            tpl_ln=$(wc -l $WEBTPL/ngingx_ip.tpl | cut -f 1 -d ' ')
            ip_line=$(grep -n "%ip%" $WEBTPL/ngingx_ip.tpl |head -n1 |\
                cut -f 1 -d :)

            conf_line=$(grep -n -w $ip $nconf|head -n1|cut -f 1 -d :)

            # Checking parsed lines
            if [ -z "$tpl_ln" ] || [ -z "$ip_line" ] || [ -z "$conf_line" ]
            then
                echo "Error: nginx config paring error"
                log_event 'debug' "$E_PARSING $EVENT"
                exit $E_PARSING
            fi

            up_line=$((ip_line - 1))
            first_line=$((conf_line - up_line))
            last_line=$((conf_line - ip_line + tpl_ln))

            # Checking parsed lines
            if [ -z "$first_line" ] || [ -z "$last_line" ]; then
                echo "Error: nginx config paring error"
                log_event 'debug' "$E_PARSING $EVENT"
                exit $E_PARSING
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

upd_web_domain_values() {
    ip=$IP
    group="$user"
    email="$user@$domain"
    docroot="$HOMEDIR/$user/web/$domain/public_html"
    docroot_string="DocumentRoot $docroot"
    proxy_string="proxy_pass     http://$ip:$WEB_PORT;"

    # Parsing domain aliases
    i=1
    j=1
    OLD_IFS="$IFS"
    IFS=','
    server_alias=''
    alias_string=''
    for dalias in $ALIAS; do
        dalias=$(idn -t --quiet -a $dalias)
        # Spliting ServerAlias lines
        check_8k="$server_alias $dalias"
        if [ "${#check_8k}" -ge '8100' ]; then
            if [ "$j" -eq 1 ]; then
                alias_string="ServerAlias $server_alias"
            else
                alias_string="$alias_string\n    ServerAlias $server_alias"
            fi
            j=2
            server_alias=''
        fi
        if [ "$i" -eq 1 ]; then
            aliases_idn="$dalias"
            server_alias="$dalias"
            alias_string="ServerAlias $server_alias"
        else
            aliases_idn="$aliases_idn,$dalias"
            server_alias="$server_alias $dalias"
        fi
        i=2
    done

    if [ $j -gt 1 ]; then
        alias_string="$alias_string\n    ServerAlias $server_alias"
    else
        alias_string="ServerAlias $server_alias"
    fi

    IFS=$OLD_IFS

    # Checking error log status
    if [ "$ELOG" = 'no' ]; then
        elog='#'
    else
        elog=''
    fi

    # Checking cgi
    if [ "$CGI" != 'yes' ]; then
        cgi='#'
        cgi_option='-ExecCGI'
    else
        cgi=''
        cgi_option='+ExecCGI'
    fi

    # Checking suspend
    if [ "$SUSPENDED" = 'yes' ]; then
        docroot_string="Redirect / http://$url"
        proxy_string="rewrite ^(.*)\$ http://$url;"
    fi

    # Defining SSL vars
    ssl_crt="$HOMEDIR/$user/conf/web/ssl.$domain.crt"
    ssl_key="$HOMEDIR/$user/conf/web/ssl.$domain.key"
    ssl_pem="$HOMEDIR/$user/conf/web/ssl.$domain.pem"
    ssl_ca="$HOMEDIR/$user/conf/web/ssl.$domain.ca"
    if [ ! -e "$USER_DATA/ssl/$domain.ca" ]; then
        ssl_ca_str='#'
    fi

    case $SSL_HOME in
        single) docroot="$HOMEDIR/$user/web/$domain/public_shtml" ;;
        same) docroot="$HOMEDIR/$user/web/$domain/public_html" ;;
    esac
}

is_mail_account_free() {
    acc=${1-$account}
    check_acc=$(grep -w $acc $USER_DATA/mail/$domain.conf)
    if [ ! -z "$check_acc" ]; then
        echo "Error: account $acc exists"
        log_event 'debug' "$E_EXISTS $EVENT"
        exit $E_EXISTS
    fi
}

is_mail_account_valid() {
    acc=${1-$account}
    check_acc=$(grep -w $acc $USER_DATA/mail/$domain.conf)
    if [ -z "$check_acc" ]; then
        echo "Error: account $acc not exist"
        log_event 'debug' "$E_NOTEXIST $EVENT"
        exit $E_NOTEXIST
    fi
}
