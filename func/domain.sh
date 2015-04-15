# Web template check
is_web_template_valid() {
    t="$WEBTPL/$WEB_SYSTEM/$WEB_BACKEND/$template.tpl"
    s="$WEBTPL/$WEB_SYSTEM/$WEB_BACKEND/$template.stpl"
    if [ ! -e $t ] || [ ! -e $s ]; then
        echo "Error: web template $template not found"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
}

# Proxy template check
is_proxy_template_valid() {
    t="$WEBTPL/$PROXY_SYSTEM/$template.tpl"
    s="$WEBTPL/$PROXY_SYSTEM/$template.stpl"
    if [ ! -e $t ] || [ ! -e $s ]; then
        echo "Error: proxy template $template not found"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
}

# Backend template check
is_web_backend_template_valid() {
    if [ ! -z "$1" ]; then
        template=$1
    else
        template=$(grep BACKEND_TEMPLATE $USER_DATA/user.conf)
    fi
    if [ -z "$template" ]; then
        if [ -e "$WEBTPL/$WEB_BACKEND/default.tpl" ]; then
            sed -i "s/^WEB_DOMAINS/BACKEND_TEMPLATE='default'\nWEB_DOMAINS/g" \
                $USER_DATA/user.conf
            template='default'
        else
            echo "Error: backend template default not found"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    else
        template=$(echo "$template"|cut -f 2 -d \'|head -n1)
        if [ ! -e "$WEBTPL/$WEB_BACKEND/$template.tpl" ]; then
            echo "Error: backend template $template not found"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    fi
}

# Backend pool check
is_web_backend_pool_valid(){
    if [ -d "/etc/php-fpm.d" ]; then
        pool="/etc/php-fpm.d"
    fi
    if [ -d "/etc/php5/fpm/pool.d" ]; then
        pool="/etc/php5/fpm/pool.d"
    fi
    if [ -d "/etc/php-fpm-5.5.d" ]; then
        pool="/etc/php-fpm-5.5.d"
    fi
    if [ ! -e "$pool" ]; then
        echo "Error: backend pool directory not found"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi

    backend="$domain"
    if [ "$WEB_BACKEND_POOL" = 'user' ]; then
        backend="$user"
    fi
}

# DNS template check
is_dns_template_valid() {
    t="$DNSTPL/$template.tpl"
    if [ ! -e $t ]; then
        echo "Error: dns template $template not found"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
}

# Checking domain existance
is_domain_new() {
    type="$1"
    dom=${2-$domain}

    web=$(grep -F -H "DOMAIN='$dom'" $VESTA/data/users/*/web.conf)
    dns=$(grep -F -H "DOMAIN='$dom'" $VESTA/data/users/*/dns.conf)
    mail=$(grep -F -H "DOMAIN='$dom'" $VESTA/data/users/*/mail.conf)

    # Check web domain
    if [ ! -z "$web" ] && [ "$type" == 'web' ]; then
        echo "Error: domain $dom exist"
        log_event "$E_EXISTS" "$EVENT"
        exit $E_EXISTS
    fi
    if [ ! -z "$web" ]; then
        web_user=$(echo "$web" |cut -f 7 -d /)
        if [ "$web_user" != "$user" ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
    fi

    # Check dns domain
    if [ ! -z "$dns" ] && [ "$type" == 'dns' ]; then
        echo "Error: domain $dom exist"
        log_event "$E_EXISTS" "$EVENT"
        exit $E_EXISTS
    fi
    if [ ! -z "$dns" ]; then
        dns_user=$(echo "$dns" |cut -f 7 -d /)
        if [ "$dns_user" != "$user" ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
    fi

    # Check mail domain
    if [ ! -z "$mail" ] && [ "$type" == 'mail' ]; then
        echo "Error: domain $dom exist"
        log_event "$E_EXISTS" "$EVENT"
        exit $E_EXISTS
    fi
    if [ ! -z "$mail" ]; then
        mail_user=$(echo "$mail" |cut -f 7 -d /)
        if [ "$mail_user" != "$user" ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
    fi

    # Check web aliases
    web_alias=$(grep -w $dom $VESTA/data/users/*/web.conf)
    if [ ! -z "$web_alias" ]; then
        c1=$(grep -H "'$dom'" $VESTA/data/users/*/web.conf | cut -f 7 -d /)
        c2=$(grep -H "'$dom," $VESTA/data/users/*/web.conf | cut -f 7 -d /)
        c3=$(grep -H ",$dom," $VESTA/data/users/*/web.conf | cut -f 7 -d /)
        c4=$(grep -H ",$dom'" $VESTA/data/users/*/web.conf | cut -f 7 -d /)
        if [ ! -z "$c1" ] && [ "$type" == "web"  ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
        if [ ! -z "$c1" ] && [ "$c1" != "$user" ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi

        if [ ! -z "$c2" ] && [ "$type" == "web"  ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
        if [ ! -z "$c2" ] && [ "$c2" != "$user" ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi

        if [ ! -z "$c3" ] && [ "$type" == "web"  ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
        if [ ! -z "$c3" ] && [ "$c3" != "$user" ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi

        if [ ! -z "$c4" ] && [ "$type" == "web"  ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
        if [ ! -z "$c4" ] && [ "$c4" != "$user" ]; then
            echo "Error: domain $dom exist"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
    fi
}

# Checking mail account existance
is_mail_new() {
    check_acc=$(grep "ACCOUNT='$1'" $USER_DATA/mail/$domain.conf)
    if [ ! -z "$check_acc" ]; then
        echo "Error: mail account $1 exist"
        log_event "$E_EXISTS" "$EVENT"
        exit $E_EXISTS
    fi
    check_als=$(awk -F "ALIAS='" '{print $2}' $USER_DATA/mail/$domain.conf )
    check_als=$(echo "$check_als" | cut -f 1 -d "'" | grep -w $1)
    if [ ! -z "$check_als" ]; then
        echo "Error: mail alias $1 exist"
        log_event "$E_EXISTS" "$EVENT"
        exit $E_EXISTS
    fi
}

# Update domain zone
update_domain_zone() {
    conf="$HOMEDIR/$user/conf/dns/$domain.db"
    line=$(grep "DOMAIN='$domain'" $USER_DATA/dns.conf)
    fields='$RECORD\t$TTL\tIN\t$TYPE\t$PRIORITY\t$VALUE'
    if [ -e $conf ]; then
        zn_serial=$(head $conf|grep 'SOA' -A1|tail -n 1|sed "s/ //g")
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

    eval $line
    SOA=$(idn --quiet -a -t "$SOA")
    echo "\$TTL $TTL
@    IN    SOA    $SOA.    root.$domain_idn. (
                                            $serial
                                            7200
                                            3600
                                            1209600
                                            180 )
" > $conf
    while read line ; do
        IFS=$'\n'
        for key in $(echo $line|sed "s/' /'\n/g"); do
            eval ${key%%=*}="${key#*=}"
        done

        RECORD=$(idn --quiet -a -t "$RECORD")
        if [ "$TYPE" = 'CNAME' ] || [ "$TYPE" = 'MX' ]; then
            VALUE=$(idn --quiet -a -t "$VALUE")
        fi

        if [ "$SUSPENDED" != 'yes' ]; then
            eval echo -e "\"$fields\""|sed "s/%quote%/'/g" >> $conf
        fi
    done < $USER_DATA/dns/$domain.conf
}

# Get next DNS record ID
get_next_dnsrecord(){
    if [ -z "$id" ]; then
        curr_str=$(grep "ID=" $USER_DATA/dns/$domain.conf | cut -f 2 -d \' |\
            sort -n|tail -n1)
        id="$((curr_str +1))"
    fi
}

# Sort DNS records
sort_dns_records() {
    conf="$USER_DATA/dns/$domain.conf"
    cat $conf |sort -n -k 2 -t \' >$conf.tmp
    mv -f $conf.tmp $conf
}

# Add web config
add_web_config() {
    cat $tpl_file | \
        sed -e "s|%ip%|$ip|g" \
            -e "s|%web_system%|$WEB_SYSTEM|g" \
            -e "s|%web_port%|$WEB_PORT|g" \
            -e "s|%web_ssl_port%|$WEB_SSL_PORT|g" \
            -e "s|%backend_lsnr%|$backend_lsnr|g" \
            -e "s|%rgroups%|$WEB_RGROUPS|g" \
            -e "s|%proxy_system%|$PROXY_SYSTEM|g" \
            -e "s|%proxy_port%|$PROXY_PORT|g" \
            -e "s|%proxy_ssl_port%|$PROXY_SSL_PORT|g" \
            -e "s/%proxy_extentions%/${PROXY_EXT//,/|}/g" \
            -e "s|%domain_idn%|$domain_idn|g" \
            -e "s|%domain%|$domain|g" \
            -e "s|%user%|$user|g" \
            -e "s|%group%|$group|g" \
            -e "s|%home%|$HOMEDIR|g" \
            -e "s|%docroot%|$docroot|g" \
            -e "s|%sdocroot%|$sdocroot|g" \
            -e "s|%email%|$email|g" \
            -e "s|%alias_string%|$alias_string|g" \
            -e "s|%alias_idn%|${aliases_idn//,/ }|g" \
            -e "s|%alias%|${aliases//,/ }|g" \
            -e "s|%ssl_crt%|$ssl_crt|g" \
            -e "s|%ssl_key%|$ssl_key|g" \
            -e "s|%ssl_pem%|$ssl_pem|g" \
            -e "s|%ssl_ca_str%|$ssl_ca_str|g" \
            -e "s|%ssl_ca%|$ssl_ca|g" \
    >> $conf
}

# Get config top and bottom line numbers
get_web_config_brds() {
    serv_line=$(egrep -ni "Name %domain_idn%($| )" $tpl_file |cut -f 1 -d :)
    if [ -z "$serv_line" ]; then
        log_event "$E_PARSING" "$EVENT"
        return $E_PARSING
    fi

    last_line=$(wc -l $tpl_file|cut -f 1 -d ' ')
    bfr_line=$((serv_line - 1))
    aftr_line=$((last_line - serv_line - 1))

    str=$(egrep -ni "Name $domain_idn($| )" $conf | cut -f 1 -d :)
    top_line=$((str - serv_line + 1))
    bottom_line=$((top_line + last_line -1))

    multi=$(sed -n "$top_line,$bottom_line p" $conf |grep ServerAlias |wc -l)
    if [ "$multi" -ge 2 ]; then
        bottom_line=$((bottom_line + multi -1))
    fi

}

# Replace web config
replace_web_config() {
    get_web_config_brds || exit $?
    clean_new=$(echo "$new" | sed \
        -e 's/\\/\\\\/g' \
        -e 's/&/\\&/g' \
        -e 's/\//\\\//g')
    clean_old=$(echo "$old" | sed \
        -e 's/\\/\\\\/g' \
        -e 's/&/\\&/g' \
        -e 's/\//\\\//g')

    sed -i  "$top_line,$bottom_line s/$clean_old/$clean_new/" $conf
}

# Get domain variables
get_domain_values() {
    for line in $(grep "DOMAIN='$domain'" $USER_DATA/$1.conf); do
        eval $line
    done
}

# Get backend values
get_domain_backend_values() {
    lsnr=$(grep "listen =" $pool/$backend.conf |cut -f 2 -d = |sed "s/ //")
    backend_lsnr="$lsnr"
    if [ ! -z "$(echo $lsnr |grep /)" ]; then
        backend_lsnr="unix:$backend_lsnr"
    fi
}

# SSL certificate verification
is_web_domain_cert_valid() {
    if [ ! -e "$ssl_dir/$domain.crt" ]; then
        echo "Error: $ssl_dir/$domain.crt not found"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi

    if [ ! -e "$ssl_dir/$domain.key" ]; then
        echo "Error: $ssl_dir/$domain.key not found"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi

    crt_vrf=$(openssl verify $ssl_dir/$domain.crt 2>&1)
    if [ ! -z "$(echo $crt_vrf | grep 'unable to load')" ]; then
        echo "Error: SSL Certificate is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi

    if [ ! -z "$(echo $crt_vrf | grep 'unable to get local issuer')" ]; then
        if [ ! -e "$ssl_dir/$domain.ca" ]; then
            echo "Error: Certificate Authority not found"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    fi

    if [ -e "$ssl_dir/$domain.ca" ]; then
        s1=$(openssl x509 -text -in $ssl_dir/$domain.crt 2>/dev/null)
        s1=$(echo "$s1" |grep Issuer  |awk -F = '{print $6}' |head -n1)
        s2=$(openssl x509 -text -in $ssl_dir/$domain.ca 2>/dev/null)
        s2=$(echo "$s2" |grep Subject  |awk -F = '{print $6}' |head -n1)
        if [ "$s1" != "$s2" ]; then
            echo "Error: SSL intermediate chain is not valid"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    fi

    key_vrf=$(grep 'PRIVATE KEY' $ssl_dir/$domain.key | wc -l)
    if [ "$key_vrf" -ne 2 ]; then
        echo "Error: SSL Key is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi

    openssl s_server -quiet -cert $ssl_dir/$domain.crt \
        -key $ssl_dir/$domain.key >> /dev/null 2>&1 &
    pid=$!
    sleep 0.5
    disown &> /dev/null
    kill $pid &> /dev/null
    if [ "$?" -ne '0' ]; then
        echo "Error: ssl certificate key pair is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Delete web configuartion
del_web_config() {
    get_web_config_brds || exit $?
    sed -i "$top_line,$bottom_line d" $conf
}

# Update web domain values
upd_web_domain_values() {
    group="$user"
    email="info@$domain"
    docroot="$HOMEDIR/$user/web/$domain/public_html"
    sdocroot=$docroot
    if [ "$SSL_HOME" = 'single' ]; then
        sdocroot="$HOMEDIR/$user/web/$domain/public_shtml" ;
    fi
    if [ ! -z "$WEB_BACKEND" ]; then
        is_web_backend_pool_valid
        get_domain_backend_values
    fi
    i=1
    j=1
    OLD_IFS="$IFS"
    IFS=','
    server_alias=''
    alias_string=''
    aliases_idn=''

    for dalias in $ALIAS; do
        dalias=$(idn -t --quiet -a $dalias)
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
    if [ "$ELOG" = 'no' ]; then
        elog='#'
    else
        elog=''
    fi

    if [ "$CGI" != 'yes' ]; then
        cgi='#'
        cgi_option='-ExecCGI'
    else
        cgi=''
        cgi_option='+ExecCGI'
    fi

    ssl_crt="$HOMEDIR/$user/conf/web/ssl.$domain.crt"
    ssl_key="$HOMEDIR/$user/conf/web/ssl.$domain.key"
    ssl_pem="$HOMEDIR/$user/conf/web/ssl.$domain.pem"
    ssl_ca="$HOMEDIR/$user/conf/web/ssl.$domain.ca"
    if [ ! -e "$USER_DATA/ssl/$domain.ca" ]; then
        ssl_ca_str='#'
    fi

    if [ "$SUSPENDED" = 'yes' ]; then
        docroot="$VESTA/data/templates/web/suspend"
        sdocroot="$VESTA/data/templates/web/suspend"
    fi
}

# Check if this is a last record
is_dns_record_critical() {
    str=$(grep "ID='$id'" $USER_DATA/dns/$domain.conf)
    eval $str
    if [ "$TYPE" = 'A' ] || [ "$TYPE" = 'NS' ]; then
        records=$(grep "TYPE='$TYPE'" $USER_DATA/dns/$domain.conf| wc -l)
        if [ $records -le 1 ]; then
            echo "Error: at least one $TYPE record should remain active"
            log_event "$E_INVALID" "$EVENT"
            exit $E_INVALID
        fi
    fi
}

# Check if dns record is valid
is_dns_fqnd() {
    t=$1
    r=$2
    fqdn_type=$(echo $t | grep "NS\|CNAME\|MX\|PTR\|SRV")
    tree_length=3
    if [ $t = 'CNAME' ]; then
        tree_length=2
    fi

    if [ ! -z "$fqdn_type" ]; then
        dots=$(echo $dvalue | grep -o "\." | wc -l)
        if [ "$dots" -lt "$tree_length" ]; then
            r=$(echo $r|sed -e "s/\.$//")
            msg="$t record $r should be a fully qualified domain name (FQDN)"
            echo "Error: $msg"
            log_event "$E_INVALID" "$EVENT"
            exit $E_INVALID
        fi
    fi
}

# Validate nameserver
is_dns_nameserver_valid() {
    d=$1
    t=$2
    r=$3
    if [ "$t" = 'NS' ]; then
        remote=$(echo $r |grep ".$domain.$")
        if [ ! -z "$remote" ]; then
            zone=$USER_DATA/dns/$d.conf
            a_record=$(echo $r |cut -f 1 -d '.')
            n_record=$(grep "RECORD='$a_record'" $zone| grep "TYPE='A'")
            if [ -z "$n_record" ]; then
                echo "Error: corresponding A record $a_record.$d does not exist"
                log_event "$E_NOTEXIST" "$EVENT"
                exit $E_NOTEXIST
            fi
        fi
    fi
}
