#----------------------------------------------------------#
#                        WEB                               #
#----------------------------------------------------------#

# Web template check
is_web_template_valid() {
    if [ ! -z "$WEB_SYSTEM" ]; then
        tpl="$WEBTPL/$WEB_SYSTEM/$WEB_BACKEND/$1.tpl"
        stpl="$WEBTPL/$WEB_SYSTEM/$WEB_BACKEND/$1.stpl"
        if [ ! -e "$tpl" ] || [ ! -e "$stpl" ]; then
            check_result $E_NOTEXIST "$1 web template doesn't exist"
        fi
    fi
}

# Proxy template check
is_proxy_template_valid() {
    if [ ! -z "$PROXY_SYSTEM" ]; then
        tpl="$WEBTPL/$PROXY_SYSTEM/$1.tpl"
        stpl="$WEBTPL/$PROXY_SYSTEM/$1.stpl"
        if [ ! -e "$tpl" ] || [ ! -e "$stpl" ]; then
            check_result $E_NOTEXIST "$1 proxy template doesn't exist"
        fi
    fi
}

# Backend template check
is_backend_template_valid() {
    if [ ! -z "$WEB_BACKEND" ]; then
        if [ ! -e "$WEBTPL/$WEB_BACKEND/$1.tpl" ]; then
            check_result $E_NOTEXIST "$1 backend template doesn't exist"
        fi
    fi
}

# Web domain existence check
is_web_domain_new() {
    web=$(grep -F -H "DOMAIN='$1'" $VESTA/data/users/*/web.conf)
    if [ ! -z "$web" ]; then
        if [ "$type" == 'web' ]; then
            check_result $E_EXISTS "Web domain $1 exist"
        fi
        web_user=$(echo "$web" |cut -f 7 -d /)
        if [ "$web_user" != "$user" ]; then
            check_result $E_EXISTS "Web domain $1 exist"
        fi
    fi
}

# Web alias existence check
is_web_alias_new() {
    web_alias=$(grep -wH "$1" $VESTA/data/users/*/web.conf)
    if [ ! -z "$web_alias" ]; then
        a1=$(echo "$web_alias" |grep -F "'$1'" |cut -f 7 -d /)
        if [ ! -z "$a1" ] && [ "$2" == "web"  ]; then
            check_result $E_EXISTS "Web alias $1 exists"
        fi
        if [ ! -z "$a1" ] && [ "$a1" != "$user" ]; then
            check_result $E_EXISTS "Web alias $1 exists"
        fi
        a2=$(echo "$web_alias" |grep -F "'$1," |cut -f 7 -d /)
        if [ ! -z "$a2" ] && [ "$2" == "web"  ]; then
            check_result $E_EXISTS "Web alias $1 exists"
        fi
        if [ ! -z "$a2" ] && [ "$a2" != "$user" ]; then
            check_result $E_EXISTS "Web alias $1 exists"
        fi
        a3=$(echo "$web_alias" |grep -F ",$1," |cut -f 7 -d /)
        if [ ! -z "$a3" ] && [ "$2" == "web"  ]; then
            check_result $E_EXISTS "Web alias $1 exists"
        fi
        if [ ! -z "$a3" ] && [ "$a3" != "$user" ]; then
            check_result $E_EXISTS "Web alias $1 exists"
        fi
        a4=$(echo "$web_alias" |grep -F ",$1'" |cut -f 7 -d /)
        if [ ! -z "$a4" ] && [ "$2" == "web"  ]; then
            check_result $E_EXISTS "Web alias $1 exists"
        fi
        if [ ! -z "$a4" ] && [ "$a4" != "$user" ]; then
            check_result $E_EXISTS "Web alias $1 exists"
        fi
    fi
}

# Prepare web backend
prepare_web_backend() {
    if [ -d "/etc/php-fpm.d" ]; then
        pool="/etc/php-fpm.d"
    fi
    if [ -d "/etc/php5/fpm/pool.d" ]; then
        pool="/etc/php5/fpm/pool.d"
    fi
    if [ ! -e "$pool" ]; then
        pool=$(find /etc/php* -type d \( -name "pool.d" -o -name "*fpm.d" \))
        if [ ! -e "$pool" ]; then
            check_result $E_NOTEXIST "php-fpm pool doesn't exist"
        fi
    fi

    backend_type="$domain"
    if [ "$WEB_BACKEND_POOL" = 'user' ]; then
        backend_type="$user"
    fi
    if [ -e "$pool/$backend_type.conf" ]; then
        backend_lsnr=$(grep "listen =" $pool/$backend_type.conf)
        backend_lsnr=$(echo "$backend_lsnr" |cut -f 2 -d = |sed "s/ //")
        if [ ! -z "$(echo $backend_lsnr |grep /)" ]; then
            backend_lsnr="unix:$backend_lsnr"
        fi
    fi
}

# Prepare web aliases
prepare_web_aliases() {
    i=1
    for tmp_alias in ${1//,/ }; do
        tmp_alias_idn="$tmp_alias"
        if [[ "$tmp_alias" = *[![:ascii:]]* ]]; then
            tmp_alias_idn=$(idn -t --quiet -a $tmp_alias)
        fi
        if [[ $i -eq 1 ]]; then
            aliases="$tmp_alias"
            aliases_idn="$tmp_alias_idn"
            alias_string="ServerAlias $tmp_alias_idn"
        else
            aliases="$aliases,$tmp_alias"
            aliases_idn="$aliases_idn,$tmp_alias_idn"
            if (( $i % 100 == 0 )); then
                alias_string="$alias_string\n    ServerAlias $tmp_alias_idn"
            else
                alias_string="$alias_string $tmp_alias_idn"
            fi
        fi
        alias_number=$i
        ((i++))
    done
}

# Update web domain values
prepare_web_domain_values() {
    if [[ "$domain" = *[![:ascii:]]* ]]; then
        domain_idn=$(idn -t --quiet -a $domain)
    else
        domain_idn=$domain
    fi
    group="$user"
    email="info@$domain"
    docroot="$HOMEDIR/$user/web/$domain/public_html"
    sdocroot="$docroot"
    if [ "$SSL_HOME" = 'single' ]; then
        sdocroot="$HOMEDIR/$user/web/$domain/public_shtml" ;
    fi

    if [ ! -z "$WEB_BACKEND" ]; then
        prepare_web_backend
    fi

    server_alias=''
    alias_string=''
    aliases_idn=''
    prepare_web_aliases $ALIAS

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

# Add web config
add_web_config() {
    conf="$HOMEDIR/$user/conf/web/$1.conf"
    if [[ "$2" =~ stpl$ ]]; then
        conf="$HOMEDIR/$user/conf/web/s$1.conf"
    fi

    cat $WEBTPL/$1/$WEB_BACKEND/$2 | \
        sed -e "s|%ip%|$local_ip|g" \
            -e "s|%domain%|$domain|g" \
            -e "s|%domain_idn%|$domain_idn|g" \
            -e "s|%alias%|${aliases//,/ }|g" \
            -e "s|%alias_idn%|${aliases_idn//,/ }|g" \
            -e "s|%alias_string%|$alias_string|g" \
            -e "s|%email%|info@$domain|g" \
            -e "s|%web_system%|$WEB_SYSTEM|g" \
            -e "s|%web_port%|$WEB_PORT|g" \
            -e "s|%web_ssl_port%|$WEB_SSL_PORT|g" \
            -e "s|%backend_lsnr%|$backend_lsnr|g" \
            -e "s|%rgroups%|$WEB_RGROUPS|g" \
            -e "s|%proxy_system%|$PROXY_SYSTEM|g" \
            -e "s|%proxy_port%|$PROXY_PORT|g" \
            -e "s|%proxy_ssl_port%|$PROXY_SSL_PORT|g" \
            -e "s/%proxy_extentions%/${PROXY_EXT//,/|}/g" \
            -e "s|%user%|$user|g" \
            -e "s|%group%|$user|g" \
            -e "s|%home%|$HOMEDIR|g" \
            -e "s|%docroot%|$docroot|g" \
            -e "s|%sdocroot%|$sdocroot|g" \
            -e "s|%ssl_crt%|$ssl_crt|g" \
            -e "s|%ssl_key%|$ssl_key|g" \
            -e "s|%ssl_pem%|$ssl_pem|g" \
            -e "s|%ssl_ca_str%|$ssl_ca_str|g" \
            -e "s|%ssl_ca%|$ssl_ca|g" \
    >> $conf

    chown root:$user $conf
    chmod 640 $conf

    if [ -z "$(grep "$conf" /etc/$1/conf.d/vesta.conf)" ]; then
        if [ "$1" != 'nginx' ]; then
            echo "Include $conf" >> /etc/$1/conf.d/vesta.conf
        else
            echo "include $conf;" >> /etc/$1/conf.d/vesta.conf
        fi
    fi

    trigger="${2/.*pl/.sh}"
    if [ -x "$WEBTPL/$1/$WEB_BACKEND/$trigger" ]; then
        $WEBTPL/$1/$WEB_BACKEND/$trigger \
            $user $domain $local_ip $HOMEDIR $HOMEDIR/$user/web/$domain/public_html
    fi
}

# Get config top and bottom line number
get_web_config_lines() {
    tpl_lines=$(egrep -ni "name %domain_idn%" $1 |grep -w %domain_idn%)
    tpl_lines=$(echo "$tpl_lines" |cut -f 1 -d :)
    tpl_last_line=$(wc -l $1 |cut -f 1 -d ' ')
    if [ -z "$tpl_lines" ]; then
        check_result $E_PARSING "can't parse template $1"
    fi

    vhost_lines=$(grep -niF "name $domain_idn" $2)
    vhost_lines=$(echo "$vhost_lines" |egrep "$domain_idn($| |;)") #"
    vhost_lines=$(echo "$vhost_lines" |cut -f 1 -d :)
    if [ -z "$vhost_lines" ]; then
        check_result $E_PARSING "can't parse config $2"
    fi

    top_line=$((vhost_lines + 1 - tpl_lines))
    bottom_line=$((top_line - 1 + tpl_last_line))
    multi=$(sed -n "$top_line,$bottom_line p" $2 |grep ServerAlias |wc -l)
    if [ "$multi" -ge 2 ]; then
        bottom_line=$((bottom_line + multi -1))
    fi
}

# Replace web config
replace_web_config() {
    conf="$HOMEDIR/$user/conf/web/$1.conf"
    if [[ "$2" =~ stpl$ ]]; then
        conf="$HOMEDIR/$user/conf/web/s$1.conf"
    fi
    get_web_config_lines $WEBTPL/$1/$WEB_BACKEND/$2 $conf
    sed -i  "$top_line,$bottom_line s|$old|$new|g" $conf
}

# Delete web configuartion
del_web_config() {
    conf="$HOMEDIR/$user/conf/web/$1.conf"
    if [[ "$2" =~ stpl$ ]]; then
        conf="$HOMEDIR/$user/conf/web/s$1.conf"
    fi

    get_web_config_lines $WEBTPL/$1/$WEB_BACKEND/$2 $conf
    sed -i "$top_line,$bottom_line d" $conf

    web_domain=$(grep $domain $USER_DATA/web.conf |wc -l)
    if [ "$web_domain" -eq '0' ]; then
        sed -i "/.*\/$user\/.*$1.conf/d" /etc/$1/conf.d/vesta.conf
        rm -f $conf
    fi
}

# SSL certificate verification
is_web_domain_cert_valid() {
    if [ ! -e "$ssl_dir/$domain.crt" ]; then
        check_result $E_NOTEXIST "$ssl_dir/$domain.crt not found"
    fi

    if [ ! -e "$ssl_dir/$domain.key" ]; then
        check_result $E_NOTEXIST "$ssl_dir/$domain.key not found"
    fi

    crt_vrf=$(openssl verify $ssl_dir/$domain.crt 2>&1)
    if [ ! -z "$(echo $crt_vrf |grep 'unable to load')" ]; then
        check_result $E_INVALID "SSL Certificate is not valid"
    fi

    if [ ! -z "$(echo $crt_vrf |grep 'unable to get local issuer')" ]; then
        if [ ! -e "$ssl_dir/$domain.ca" ]; then
            check_result $E_NOTEXIST "Certificate Authority not found"
        fi
    fi

    if [ -e "$ssl_dir/$domain.ca" ]; then
        s1=$(openssl x509 -text -in $ssl_dir/$domain.crt 2>/dev/null)
        s1=$(echo "$s1" |grep Issuer  |awk -F = '{print $6}' |head -n1)
        s2=$(openssl x509 -text -in $ssl_dir/$domain.ca 2>/dev/null)
        s2=$(echo "$s2" |grep Subject  |awk -F = '{print $6}' |head -n1)
        if [ "$s1" != "$s2" ]; then
            check_result $E_NOTEXIST "SSL intermediate chain is not valid"
        fi
    fi

    key_vrf=$(grep 'PRIVATE KEY' $ssl_dir/$domain.key |wc -l)
    if [ "$key_vrf" -ne 2 ]; then
        check_result $E_INVALID "SSL Key is not valid"
    fi
    if [ ! -z "$(grep 'ENCRYPTED' $ssl_dir/$domain.key)" ]; then
        check_result $E_FORBIDEN "SSL Key is protected (remove pass_phrase)"
    fi

    openssl s_server -quiet -cert $ssl_dir/$domain.crt \
        -key $ssl_dir/$domain.key >> /dev/null 2>&1 &
    pid=$!
    sleep 0.5
    disown &> /dev/null
    kill $pid &> /dev/null
    check_result $? "ssl certificate key pair is not valid" $E_INVALID
}


#----------------------------------------------------------#
#                        DNS                               #
#----------------------------------------------------------#

# DNS template check
is_dns_template_valid() {
    if [ ! -e "$DNSTPL/$1.tpl" ]; then
        check_result $E_NOTEXIST "$1 dns template doesn't exist"
    fi
}

# DNS domain existence check
is_dns_domain_new() {
    dns=$(ls $VESTA/data/users/*/dns/$1.conf 2>/dev/null)
    if [ ! -z "$dns" ]; then
        if [ "$2" == 'dns' ]; then
            check_result $E_EXISTS "DNS domain $1 exists"
        fi
        dns_user=$(echo "$dns" |cut -f 7 -d /)
        if [ "$dns_user" != "$user" ]; then
            check_result $E_EXISTS "DNS domain $1 exists"
        fi
    fi
}

# Update domain zone
update_domain_zone() {
    domain_param=$(grep "DOMAIN='$domain'" $USER_DATA/dns.conf)
    eval $domain_param
    SOA=$(idn --quiet -a -t "$SOA")
    if [ -z "$SERIAL" ]; then
        SERIAL=$(date +'%Y%m%d01')
    fi
    zn_conf="$HOMEDIR/$user/conf/dns/$domain.db"
    echo "\$TTL $TTL
@    IN    SOA    $SOA.    root.$domain_idn. (
                                            $SERIAL
                                            7200
                                            3600
                                            1209600
                                            180 )
" > $zn_conf
    fields='$RECORD\t$TTL\tIN\t$TYPE\t$PRIORITY\t$VALUE'
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
            eval echo -e "\"$fields\""|sed "s/%quote%/'/g" >> $zn_conf
        fi
    done < $USER_DATA/dns/$domain.conf
}

# Update zone serial
update_domain_serial() {
    zn_conf="$HOMEDIR/$user/conf/dns/$domain.db"
    if [ -e $zn_conf ]; then
        zn_serial=$(head $zn_conf |grep 'SOA' -A1 |tail -n 1 |sed "s/ //g")
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
    add_object_key "dns" 'DOMAIN' "$domain" 'SERIAL' 'RECORDS'
    update_object_value 'dns' 'DOMAIN' "$domain" '$SERIAL' "$serial"
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

# Check if this is a last record
is_dns_record_critical() {
    str=$(grep "ID='$id'" $USER_DATA/dns/$domain.conf)
    eval $str
    if [ "$TYPE" = 'A' ] || [ "$TYPE" = 'NS' ]; then
        records=$(grep "TYPE='$TYPE'" $USER_DATA/dns/$domain.conf| wc -l)
        if [ $records -le 1 ]; then
            echo "Error: at least one $TYPE record should remain active"
            log_event "$E_INVALID" "$ARGUMENTS"
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
            log_event "$E_INVALID" "$ARGUMENTS"
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
                check_result $E_NOTEXIST "IN A $a_record.$d does not exist"
            fi
        fi
    fi
}



#----------------------------------------------------------#
#                       MAIL                               #
#----------------------------------------------------------#

# Mail domain existence check
is_mail_domain_new() {
    mail=$(ls $VESTA/data/users/*/mail/$1.conf 2>/dev/null)
    if [ ! -z "$mail" ]; then
        if [ "$2" == 'mail' ]; then
            check_result $E_EXISTS "Mail domain $1 exists"
        fi
        mail_user=$(echo "$mail" |cut -f 7 -d /)
        if [ "$mail_user" != "$user" ]; then
            check_result $E_EXISTS "Mail domain $1 exists"
        fi
    fi
}

# Checking mail account existance
is_mail_new() {
    check_acc=$(grep "ACCOUNT='$1'" $USER_DATA/mail/$domain.conf)
    if [ ! -z "$check_acc" ]; then
        check_result $E_EXIST "mail account $1 is already exists"
    fi
    check_als=$(awk -F "ALIAS='" '{print $2}' $USER_DATA/mail/$domain.conf )
    check_als=$(echo "$check_als" | cut -f 1 -d "'" | grep -w $1)
    if [ ! -z "$check_als" ]; then
        check_result $E_EXIST "mail alias $1 is already exists"
    fi
}


#----------------------------------------------------------#
#                        CMN                               #
#----------------------------------------------------------#

# Checking domain existance
is_domain_new() {
    type=$1
    for object in ${2//,/ }; do
        if [ ! -z "$WEB_SYSTEM" ]; then
            is_web_domain_new $object $type
            is_web_alias_new $object $type
        fi
        if [ ! -z "$DNS_SYSTEM" ]; then
            is_dns_domain_new $object $type
        fi
        if [ ! -z "$MAIL_SYSTEM" ]; then
            is_mail_domain_new $object $type
        fi
    done
}

# Get domain variables
get_domain_values() {
    eval $(grep "DOMAIN='$domain'" $USER_DATA/$1.conf)
}
