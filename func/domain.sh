# Web template check
is_apache_template_valid() {
    t="$WEBTPL/apache_$template.tpl"
    s="$WEBTPL/apache_$template.stpl"
    if [ ! -e $t ] || [ ! -e $s ]; then
        template='default'
        t="$WEBTPL/apache_$template.tpl"
        s="$WEBTPL/apache_$template.stpl"
        if [ ! -e $t ] || [ ! -e $s ]; then
            echo "Error: template $template not found"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    fi
}

# Nginx template check
is_nginx_template_valid() {
    t="$WEBTPL/nginx_$template.tpl"
    s="$WEBTPL/nginx_$template.stpl"
    if [ ! -e $t ] || [ ! -e $s ]; then
        template='default'
        t="$WEBTPL/nginx_$template.tpl"
        s="$WEBTPL/nginx_$template.stpl"
        if [ ! -e $t ] || [ ! -e $s ]; then
            echo "Error: nginx $template not found"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    fi
}

# DNS template check
is_dns_template_valid() {
    tpl="$DNSTPL/$template.tpl"
    if [ ! -e $tpl ]; then
        template='default'
        tpl="$DNSTPL/$template.tpl"
        if [ ! -e $tpl ]; then
            echo "Error: template not found"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    fi
}

# Checking domain existance
is_domain_new() {
    type="$1"
    dom=${2-$domain}

    web=$(grep -H "DOMAIN='$dom'" $VESTA/data/users/*/web.conf)
    dns=$(grep -H "DOMAIN='$dom'" $VESTA/data/users/*/dns.conf)
    mail=$(grep -H "DOMAIN='$dom'" $VESTA/data/users/*/mail.conf)

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
        exit
    fi
    check_als=$(awk -F "ALIAS='" '{print $2}' $USER_DATA/mail/$domain.conf )
    check_als=$(echo "$check_als" | cut -f 1 -d "'" | grep -w $1)
    if [ ! -z "$check_als" ]; then
        echo "Error: mail alias $1 exist"
        log_event "$E_EXISTS" "$EVENT"
        exit
    fi
}

# Update domain zone
update_domain_zone() {
    conf="$HOMEDIR/$user/conf/dns/$domain.db"
    line=$(grep "DOMAIN='$domain'" $USER_DATA/dns.conf)
    fields='$RECORD\t$TTL\tIN\t$TYPE\t$PRIORITY\t$VALUE'
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
        for key in $(echo $line|sed -e "s/' /'\n/g"); do
            eval ${key%%=*}="${key#*=}"
        done

        RECORD=$(idn --quiet -a -t "$RECORD")
        if [ "$SUSPENDED" != 'yes' ]; then
            eval echo -e "\"$fields\""|sed -e "s/%quote%/'/g" >> $conf
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
        sed -e "s/%ip%/$ip/g" \
            -e "s/%web_port%/$WEB_PORT/g" \
            -e "s/%web_ssl_port%/$WEB_SSL_PORT/g" \
            -e "s/%proxy_port%/$PROXY_PORT/g" \
            -e "s/%proxy_ssl_port%/$PROXY_SSL_PORT/g" \
            -e "s/%domain_idn%/$domain_idn/g" \
            -e "s/%domain%/$domain/g" \
            -e "s/%user%/$user/g" \
            -e "s/%group%/$group/g" \
            -e "s/%home%/${HOMEDIR////\/}/g" \
            -e "s/%docroot%/${docroot////\/}/g" \
            -e "s/%sdocroot%/${sdocroot////\/}/g" \
            -e "s/%email%/$email/g" \
            -e "s/%alias_string%/$alias_string/g" \
            -e "s/%alias_idn%/${aliases_idn//,/ }/g" \
            -e "s/%alias%/${aliases//,/ }/g" \
            -e "s/%ssl_crt%/${ssl_crt////\/}/g" \
            -e "s/%ssl_key%/${ssl_key////\/}/g" \
            -e "s/%ssl_pem%/${ssl_pem////\/}/g" \
            -e "s/%ssl_ca_str%/${ssl_ca_str////\/}/g" \
            -e "s/%ssl_ca%/${ssl_ca////\/}/g" \
            -e "s/%nginx_extentions%/${NGINX_EXT//,/|}/g" \
            -e "s/%elog%/$elog/g" \
            -e "s/%cgi%/$cgi/g" \
            -e "s/%cgi_option%/$cgi_option/g" \
    >> $conf
}

# Get config top and bottom line numbers
get_web_config_brds() {
    serv_line=$(grep -ni 'Name %domain_idn%' "$tpl_file" |cut -f 1 -d :)
    if [ -z "$serv_line" ]; then
        log_event "$E_PARSING" "$EVENT"
        return $E_PARSING
    fi

    last_line=$(wc -l $tpl_file|cut -f 1 -d ' ')
    bfr_line=$((serv_line - 1))
    aftr_line=$((last_line - serv_line - 1))

    str=$(grep -ni "Name $domain_idn" $conf | cut -f 1 -d :)
    top_line=$((str - serv_line + 1))
    bottom_line=$((top_line + last_line -1))

    multi=$(sed -n "$top_line,$bottom_line p" $conf |grep ServerAlias |wc -l)
    if [ "$multi" -ge 2 ]; then
        bottom_line=$((bottom_line + multi -1))
    fi

}

# Change web config
change_web_config() {
    get_web_config_brds || exit $?
    vhost=$(grep -A $aftr_line -B $bfr_line -ni "Name $domain_idn" $conf)
    str=$(echo "$vhost" | grep -F "$search_phrase" | head -n 1)
    str_numb=$(echo "$str" | sed -e "s/-/=/" | cut -f 1 -d '=')
    str_cont=$(echo "$str" | sed -e "s/-/=/" | cut -f 2 -d '=')

    str_repl=$(echo "$str_repl" | sed \
        -e 's/\\/\\\\/g' \
        -e 's/&/\\&/g' \
        -e 's/\//\\\//g')

    if [ ! -z "$str" ]; then
        sed -i  "$str_numb s/.*/$str_repl/" $conf
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
        echo "Error: certificate is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi

    if [ ! -z "$(echo $crt_vrf | grep 'unable to get local issuer')" ]; then
        if [ ! -e "$ssl_dir/$domain.ca" ]; then
            echo "Error: certificate authority not found"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    fi

    if [ -e "$ssl_dir/$domain.ca" ]; then
        ca_vrf=$(openssl verify $ssl_dir/$domain.ca 2>/dev/null |grep 'OK')
        if [ -z "$ca_vrf" ]; then
            echo "Error: ssl certificate authority is not valid"
            log_event "$E_INVALID" "$EVENT"
            exit $E_INVALID
        fi

        crt_vrf=$(openssl verify -untrusted $ssl_dir/$domain.ca \
            $ssl_dir/$domain.crt 2>/dev/null |grep 'OK')
        if [ -z "$crt_vrf" ]; then
            echo "Error: root or/and intermediate cerificate not found"
            log_event "$E_NOTEXIST" "$EVENT"
            exit $E_NOTEXIST
        fi
    fi

    key_vrf=$(grep 'RSA PRIVATE KEY' $ssl_dir/$domain.key | wc -l)
    if [ "$key_vrf" -ne 2 ]; then
        echo "Error: ssl key is not valid"
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

# Add ip virtual hosting support
namehost_ip_support() {
    if [ "$WEB_SYSTEM" = 'apache' ]; then
        conf_line=$(grep -n "NameVirtual" $conf|tail -n 1|cut -f 1 -d ':')
        if [ ! -z "$conf_line" ]; then
            conf_ins=$((conf_line + 1))
        else
            conf_ins='1'
        fi

        if [ "$WEB_SSL" = 'mod_ssl' ]; then
            sed -i "$conf_ins i NameVirtualHost $ip:$WEB_SSL_PORT" $conf
            sed -i "$conf_ins i Listen $ip:$WEB_SSL_PORT" $conf
        fi

        sed -i "$conf_ins i NameVirtualHost $ip:$WEB_PORT" $conf
        sed -i "$conf_ins i Listen $ip:$WEB_PORT" $conf

        if [ "$PROXY_SYSTEM" = 'nginx' ]; then
            cat $WEBTPL/ngingx.ip.tpl | sed -e "s/%ip%/$ip/g" \
             -e "s/%web_port%/$WEB_PORT/g" \
            -e "s/%proxy_port%/$PROXY_PORT/g" >>$nconf

            ips=$(grep 'MEFaccept ' $rconf |grep -v '#'| head -n1)
            sed -i "s/$ips/$ips $ip/g" $rconf
        fi
        web_restart='yes'
    fi
}

# Disable virtual ip hosting support
namehost_ip_disable() {
    if [ "$WEB_SYSTEM" = 'apache' ]; then
        sed -i "/NameVirtualHost $ip:/d" $conf
        sed -i "/Listen $ip:/d" $conf

        if [ "$PROXY_SYSTEM" = 'nginx' ]; then
            tpl_ln=$(wc -l $WEBTPL/ngingx.ip.tpl | cut -f 1 -d ' ')
            ip_line=$(grep -n "%ip%" $WEBTPL/ngingx.ip.tpl |head -n1 |\
                cut -f 1 -d :)
            conf_line=$(grep -n -w $ip $nconf|head -n1|cut -f 1 -d :)
            if [ -z "$tpl_ln" ] || [ -z "$ip_line" ] || [ -z "$conf_line" ]
            then
                echo "Error: nginx config paring error"
                log_event "$E_PARSING" "$EVENT"
                exit $E_PARSING
            fi
            up_line=$((ip_line - 1))
            first_line=$((conf_line - up_line))
            last_line=$((conf_line - ip_line + tpl_ln))

            if [ -z "$first_line" ] || [ -z "$last_line" ]; then
                echo "Error: nginx config paring error"
                log_event "$E_PARSING" "$EVENT"
                exit $E_PARSING
            fi
            sed -i "$first_line,$last_line d" $nconf
            ips=$(grep 'RPAFproxy_ips' $rconf)
            new_ips=$(echo "$ips"|sed -e "s/$ip//")
            sed -i "s/$ips/$new_ips/g" $rconf
        fi
        web_restart='yes'
    fi
}

# Update web domain values
upd_web_domain_values() {
    ip=$IP
    group="$user"
    email="$user@$domain"
    docroot="$HOMEDIR/$user/web/$domain/public_html"
    sdocroot=$docroot
    if [ "$SSL_HOME" = 'single' ]; then
        sdocroot="$HOMEDIR/$user/web/$domain/public_shtml" ;
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

