# Log event function
log_event() {
    # Argument defenition
    level="$1"
    event="$2"

    # Checking logging system
    log_system=$(grep 'LOG_SYSTEM=' $V_CONF/vesta.conf | cut -f 2 -d \' )

    if [ "$log_system" = 'yes' ]; then
        # Checking logging level
        log=$(grep 'LOG_LEVEL=' $V_CONF/vesta.conf|\
            cut -f 2 -d \'|grep -w "$level" )
        if [ ! -z "$log" ]; then
            echo "$event" >> $V_LOG/$level.log
        fi
    fi

}

# Log user history
log_history() {
    event="$1"
    undo="$2"

    # Checking logging system
    log_history=$(grep 'LOG_HISTORY=' $V_CONF/vesta.conf | cut -f 2 -d \' )
    if [ "$log_history" = 'yes' ]; then
        echo "$event [$undo]" >> $V_USERS/$user/history.log
    fi
}

# External function result checker
check_func_result() {

    return_code="$1"

    if [[ "$return_code" -ne "$OK" ]]; then
        log_event 'debug' "$return_code $V_EVENT"
        exit $return_code
    fi
}

# Argument list checker
check_args() {

    sys_args="$1"
    user_args="$2"
    usage="$3"

    if [ "$user_args" -lt "$sys_args" ]; then
        echo "Error: bad args"
        echo "Usage: $V_SCRIPT $usage"
        log_event 'debug' "$E_BAD_ARGS $V_EVENT"
        exit $E_BAD_ARGS
    fi
}

# Format validator
format_validation() {

    # Defining url function
    format_url() {
        val="$1"

        # Checking url
        check_http=$( echo "$val" |grep "^https://" )
        needed_chars=$(echo "$val" | cut -s -f 2 -d '.')
        if [ -z "$check_http" ] || [ -z "$needed_chars" ]; then
            echo "Error: shell not found"
            log_event 'debug' "$E_SHELL_INVALID $V_EVENT"
            exit $E_SHELL_INVALID
        fi
    }

    # Defining shell function
    format_sh() {
        val="$1"

        # Checking shell
        check_shell=$(/usr/bin/chsh --list-shells | grep -w "$val" )
        if [ -z "$check_shell" ]; then
            echo "Error: shell not found"
            log_event 'debug' "$E_SHELL_INVALID $V_EVENT"
            exit $E_SHELL_INVALID
        fi
    }

    # Defining password function
    format_pwd() {
        val="$1"

        # Checking password lenght
        if [ "${#val}" -lt '6' ]; then
            echo "Error: password is shorter than 6 chars"
            log_event 'debug' "$E_PASSWORD_SHORT $V_EVENT"
            exit $E_PASSWORD_SHORT
        fi
    }

    # Defining integer function
    format_int() {
        val="$1"

        # Defining exlude mask
        special_chars=$(echo "$val" | \
         grep -c "[!|@|#|$|^|&|*|(|)|-|+|=|{|}|:|_|,|.|<|>|?|/|\|\"|'|;|%]" )

        if [[ 0 -ne "$special_chars" ]]; then
            echo "Error: $var out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi

        # Checking letters
        letters=$(echo "$val" | grep -c "[a-Z]")
        if [ 0 -ne "$letters" ]; then
            echo "Error: $var out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi

        # Checking -zero
        if [[ 0 -ne "$val" ]] && [[ 0 -gt "$val" ]]; then
            echo "Error: $var out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining ip function
    format_ip() {
        val="$1"

        oc1=$(echo $val | cut -s -f 1 -d . )
        oc2=$(echo $val | cut -s -f 2 -d . )
        oc3=$(echo $val | cut -s -f 3 -d . )
        oc4=$(echo $val | cut -s -f 4 -d . )

        # Checking octets
        if [ -z "$oc1" ] || [ -z "$oc2" ] || [ -z "$oc3" ] || [ -z "$oc4" ]
        then
            echo "Error: $var out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining ip_status function
    format_ips() {
        val="$1"

        check_status=$(echo "shared, exclusive" | grep -w "$val" )

        # Checking status
        if [ -z "$check_status" ]; then
            echo "Error: $var out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining email function
    format_eml() {
        val="$1"

        check_at=$(echo "$val" | cut -s -f 1 -d @)
        check_dt=$(echo "$val" | cut -s -f 2 -d @|cut -s -f 2 -d .)

        # Checking format
        if [ -z "$check_at" ] ||\
           [ -z "$check_dt" ] ||\
           [ "${#check_dt}" -lt 2 ] &&\
           [ "$val" != 'vesta@localhost' ]; then
            echo "Error: email format is wrong"
            log_event 'debug' "$E_EMAIL_INVALID $V_EVENT"
            exit $E_EMAIL_INVALID
        fi
    }

    # Defining interface function
    format_ifc() {
        val="$1"

        # Parsing ifconfig
        /sbin/ifconfig "$val" > /dev/null 2>&1
        return_val="$?"

        if [ "$return_val" -ne 0 ]; then
            echo "Error: intreface not exist"
            log_event 'debug' "$E_INTERFACE_NOTEXIST"
            exit $E_INTERFACE_NOTEXIST
        fi
    }

    # Defining user function
    format_usr() {
        val="$1"

        # Defining exlude mask
        special_chars=$(echo "$val" | \
            grep -c "[!|@|#|$|^|&|*|(|)|+|=|{|}|:| |,|<|>|?|/|\|\"|'|;|%]" )

        # Checking result
        if [[ 0 -ne "$special_chars" ]]; then
            echo "Error: $var is out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining domain function
    format_dom() {
        val="$1"

        # Defining exlude mask
        special_chars=$(echo "$val" | \
            grep -c "[!|@|#|$|^|&|*|(|)|+|=|{|}|:|,|<|>|?|_|/|\|\"|'|;|%]" )
        needed_chars=$(echo "$val" | cut -s -f 2 -d '.')

        # Checking result
        if [[ 0 -ne "$special_chars" ]] || [ -z "$needed_chars" ]; then
            echo "Error: $var is out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining format_db function
    format_db() {
        val="$1"

        # Defining exlude mask
        special_chars=$(echo "$val" | \
            grep -c "[!|@|#|$|^|&|*|(|)|+|=|{|}|:|,|.|<|>|?|/|\|\"|'|;|%]" )

        # Checking result
        if [[ 0 -ne "$special_chars" ]] || [ 17 -le ${#val} ]; then
            echo "Error: $var is out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining format_db function
    format_dbu() {
        val="$1"

        # Checking result
        if [ 17 -le ${#val} ]; then
            echo "Error: $var is out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining dns record function
    format_rcd() {
        val="$1"

        case $val in 
            A) known='yes';;
            NS) known='yes';;
            CNAME) known='yes';;
            AAAA) known='yes';;
            MX) known='yes';;
            TXT) known='yes';;
            SRV) known='yes';;
            DNSKEY) known='yes';;
            KEY) known='yes';;
            IPSECKEY) known='yes';;
            PTR) known='yes';;
            SPF) known='yes';;
            *)  known='no';;
        esac

        if [[ "$known" != 'yes' ]]; then
            echo "Error: $var is out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining format_ext function
    format_ext() {
        val="$1"

        # Checking result
        if [ 200 -le ${#val} ]; then
            echo "Error: $var is out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi
    }

    # Defining format_dvl function
    format_dvl() {
        val="$1"

        # Checking spaces
	check_spaces="$(echo "$val"|grep ' ')"
	check_rtype="$(echo "A AAAA NS CNAME" | grep -i -w "$rtype")"
        if [ ! -z "$check_spaces" ] && [ ! -z "$check_rtype" ]; then
            echo "Error: $var is out of range"
            log_event 'debug' "$E_OUTOFRANGE $V_EVENT"
            exit $E_OUTOFRANGE
        fi

        # Checking ip
        if [ "$rtype" = 'A' ]; then
            format_ip "$val"
        fi

        # Checking domain
        if [ "$rtype" = 'NS' ]; then
            format_dom "$val"
        fi

    }

    # Lopp on all variables
    for var in $*; do
        # Parsing reference
        eval v=\$$var

        # Checking variable format
        case $var in
            dom_alias)          format_dom "$v" ;;
            auth_pass)          format_pwd "$v" ;;
            auth_user)          format_usr "$v" ;;
            certificate)        format_usr "$v" ;;
            domain)             format_dom "$v" ;;
            database)           format_db  "$v" ;;
            db_user)            format_dbu "$v" ;;
            dvalue)             format_dvl "$v" ;;
            fname)              format_usr "$v" ;;
            job)                format_int "$v" ;;
            ns)                 format_dom "$v" ;;
            ns1)                format_dom "$v" ;;
            ns2)                format_dom "$v" ;;
            ns3)                format_dom "$v" ;;
            ns4)                format_dom "$v" ;;
            ns5)                format_dom "$v" ;;
            ns6)                format_dom "$v" ;;
            ns7)                format_dom "$v" ;;
            ns8)                format_dom "$v" ;;
            email)              format_eml "$v" ;;
            extentions)         format_ext "$v" ;;
            host)               format_usr "$v" ;;
            interface)          format_ifc "$v" ;;
            ip)                 format_ip  "$v" ;;
            ip_status)          format_ips "$v" ;;
            ip_name)            format_dom "$v" ;;
            id)                 format_int "$v" ;;
            mask)               format_ip  "$v" ;;
            max_usr)            format_int "$v" ;;
            max_db)             format_int "$v" ;;
            limit)              format_int "$v" ;;
            lname)              format_usr "$v" ;;
            offset)             format_int "$v" ;;
            owner)              format_usr "$v" ;;
            package)            format_usr "$v" ;;
            password)           format_pwd "$v" ;;
            port)               format_int "$v" ;;
            rtype)              format_rcd "$v" ;;
            shell)              format_sh  "$v" ;;
            soa)                format_dom "$v" ;;
            suspend_url)        format_url "$v" ;;
            template)           format_usr "$v" ;;
            ttl)                format_int "$v" ;;
            user)               format_usr "$v" ;;
        esac
    done
}

# Sub system checker
is_system_enabled() {

    stype="$1"

    web_function() {
        # Parsing config
        web_system=$(grep "WEB_SYSTEM=" $V_CONF/vesta.conf|cut -f 2 -d \' )

        # Checking result
        if [ -z "$web_system" ] || [ "$web_system" = "no" ]; then
            echo "Error: web hosting support disabled"
            log_event 'debug' "$E_WEB_DISABLED $V_EVENT"
            exit $E_WEB_DISABLED
        fi
    }

    proxy_function() {
        # Parsing config
        proxy_system=$(grep "PROXY_SYSTEM=" $V_CONF/vesta.conf|cut -f 2 -d \' )

        # Checking result
        if [ "$proxy_system" != 'nginx' ]; then			# only nginx
            echo "Error: proxy hosting support disabled"	# support for
            log_event 'debug' "$E_PROXY_DISABLED $V_EVENT"	# now
            exit $E_PROXY_DISABLED
        fi
    }

    dns_function() {
        # Parsing config
        dns_system=$(grep "DNS_SYSTEM=" $V_CONF/vesta.conf|cut -f 2 -d \' )

        # Checking result
        if [ -z "$dns_system" ] || [ "$cron_system" = "no" ]; then
            echo "Error: dns support disabled"
            log_event 'debug' "$E_DNS_DISABLED $V_EVENT"
            exit $E_DNS_DISABLED
        fi
    }

    cron_function() {
        # Parsing config
        cron_system=$(grep "CRON_SYSTEM=" $V_CONF/vesta.conf|cut -f 2 -d \' )

        # Checking result
        if [ -z "$cron_system" ] || [ "$cron_system" = "no" ]; then
            echo "Error: crond support disabled"
            log_event 'debug' "$E_CRON_DISABLED $V_EVENT"
            exit $E_CRON_DISABLED
        fi
    }

    db_function() {
        # Parsing config
        db_system=$(grep "DB_SYSTEM=" $V_CONF/vesta.conf|cut -f 2 -d \' )

        # Checking result
        if [ -z "$db_system" ] || [ "$db_system" = "no" ]; then
            echo "Error: db support disabled"
            log_event 'debug' "$E_DB_DISABLED $V_EVENT"
            exit $E_DB_DISABLED
        fi
    }

    backup_function() {
        # Parsing config
        bck_system=$(grep "BACKUP_SYSTEM=" $V_CONF/vesta.conf|cut -f 2 -d \' )

        # Checking result
        if [ -z "$bck_system" ] || [ "$bck_system" = "no" ]; then
            echo "Error: backup support disabled"
            log_event 'debug' "$E_BACKUP_DISABLED $V_EVENT"
            exit $E_BACKUP_DISABLED
        fi
    }

    case $stype in
        web) web_function ;;
        proxy) proxy_function ;;
        dns) dns_function ;;
        cron) cron_function ;;
        db) db_function ;;
        backup) backup_function ;;
        *) check_args '1' '0' 'system'
    esac
}

# System user check
is_user_valid() {
    search_user="${1-$user}"
    check_user=$(cut -f 1 -d : /etc/passwd | grep -w "$search_user" )
    if [ -z "$check_user" ]; then
        echo "Error: user not found"
        log_event 'debug' "$E_USER_NOTEXIST $V_EVENT"
        exit $E_USER_NOTEXIST
    fi

    if [ ! -d "$V_USERS/$search_user" ]; then
        echo "Error: unknown user"
        log_event 'debug' "$E_USER_UNKNOWN $V_EVENT"
        exit $E_USER_UNKNOWN
    fi
}

# Specific key check
is_user_suspended() {
    check_suspend=$(grep "SUSPENDED='yes'" $V_USERS/$user/user.conf)
    if [ ! -z "$check_suspend" ]; then
        echo "Error: User is suspended"
        log_event 'debug' "$E_USER_SUSPENDED $V_EVENT"
        exit $E_USER_SUSPENDED
    fi
}

# User package check
is_package_full() {
    stype="$1"

    web_domain() {
        # Checking zero domains
        domain_number=$(wc -l $V_USERS/$user/web.conf|cut -f 1 -d ' ')

        # Comparing current val with conf
        val=$(grep '^WEB_DOMAINS=' $V_USERS/$user/user.conf|cut -f 2 -d \' )
        if [ "$domain_number" -ge "$val" ]; then
            echo "Error: Upgrade package"
            log_event 'debug' "$E_PKG_UPGRADE $v_log"
            exit $E_PKG_UPGRADE
        fi
    }

    web_alias() {
        # Parsing aliases
        alias_nmb=$(grep "DOMAIN='$domain'" $V_USERS/$user/web.conf|\
            awk -F "ALIAS=" '{print $2}' | cut -f 2 -d \' |\
            sed -e "s/,/\n/g" | wc -l )

        # Parsing config
        val=$(grep 'WEB_ALIASES=' $V_USERS/$user/user.conf | cut -f 2 -d \' )
        if [ "$alias_nmb" -ge "$val" ]; then
            echo "Error: Upgrade package"
            log_event 'debug' "$E_PKG_UPGRADE $v_log"
            exit $E_PKG_UPGRADE
        fi
    }

    web_ssl() {
        # Parsing config
        val=$(grep '^WEB_SSL=' $V_USERS/$user/user.conf | cut -f 2 -d \' )
        if [ "$val" -eq '0' ]; then
            echo "Error: Upgrade package"
            log_event 'debug' "$E_PKG_UPGRADE $v_log"
            exit $E_PKG_UPGRADE
        fi

        # Checking domains
        domain_nmb=$(grep "SSL='yes'" $V_USERS/$user/web.conf | wc -l)
        # Comparing current val with conf
        if [ "$domain_nmb" -ge "$val" ]; then
            echo "Error: Upgrade package"
            log_event 'debug' "$E_PKG_UPGRADE $v_log"
            exit $E_PKG_UPGRADE
        fi
    }

    dns_domain() {
        # Checking zero domains
        domain_number=$(wc -l $V_USERS/$user/dns.conf | cut -f 1 -d " ")

        # Comparing current val with conf
        val=$(grep '^DNS_DOMAINS=' $V_USERS/$user/user.conf | cut -f 2 -d \' )
        if [ "$domain_number" -ge "$val" ]; then
            echo "Error: Upgrade package"
            log_event 'debug' "$E_PKG_UPGRADE $v_log"
            exit $E_PKG_UPGRADE
        fi
    }

    db_base() {
        # Checking zero domains
        db_number=$(wc -l $V_USERS/$user/db.conf | cut -f 1 -d " ")

        # Comparing current val with conf
        val=$(grep '^DATABASES=' $V_USERS/$user/user.conf | cut -f 2 -d \' )
        if [ "$db_number" -ge "$val" ]; then
            echo "Error: Upgrade package"
            log_event 'debug' "$E_PKG_UPGRADE $v_log"
            exit $E_PKG_UPGRADE
        fi
    }

    # FIXME - should finish other functions

    # Switching
    case "$stype" in
        web_domain) web_domain "$user" ;;
        web_alias) web_alias "$user" "$domain" ;;
        web_ssl) web_ssl "$user" ;;
        dns) dns_domain "$user" ;;
        db_base) db_base "$user" ;;
        mail_domain) mail_domain "$user" ;;
        mail_box) mail_box "$user" "$domain";;
        mail_forwarder) mail_forwarder "$user" "$domain";;
        *)
            echo "Error: bad type"
            log_event 'debug' "$E_BAD_TYPE $V_EVENT"
            exit $E_BAD_TYPE 
            ;;
    esac
}

is_package_avalable() {
    # Parsing user data
    usr_data=$(cat $V_USERS/$user/user.conf)
    for key in $usr_data; do
        eval ${key%%=*}=${key#*=}
    done

    # Clearing vars
    WEB_DOMAINS='0'
    WEB_SSL='0'
    DATABASES='0'
    MAIL_DOMAINS='0'
    MAIL_BOXES='0'
    MAIL_FORWARDERS='0'
    DNS_DOMAINS='0'
    DISK_QUOTA='0'
    BANDWIDTH='0'
    MAX_CHILDS='0'

    # Parsing package
    pkg_data=$(cat $V_PKG/$package.pkg)
    for key in $pkg_data; do
        eval ${key%%=*}=${key#*=}
    done

    # Comparing user data with package
    if [ "$WEB_DOMAINS" -lt "$U_WEB_DOMAINS" ] ||\
       [ "$WEB_SSL" -lt "$U_WEB_SSL" ] ||\
       [ "$DATABASES" -lt "$U_DATABASES" ] ||\
       [ "$MAIL_DOMAINS" -lt "$U_MAIL_DOMAINS" ] ||\
       [ "$DNS_DOMAINS" -lt "$U_DNS_DOMAINS" ] ||\
       [ "$DISK_QUOTA" -lt "$U_DISK" ] ||\
       [ "$BANDWIDTH" -lt "$U_BANDWIDTH" ] ||\
       [ "$MAX_CHILDS" -lt "$U_CHILDS" ]; then
        echo "Error: Upgrade package"
        log_event 'debug' "$E_PKG_UPGRADE $v_log"
        exit $E_PKG_UPGRADE
    fi
}

is_template_valid() {
    stype="$1"

    web_template() {
        check_tpl=$(echo "$templates"|sed -e "s/,/\n/g"|grep  "^$template$")

        tpl="$V_WEBTPL/apache_$template.tpl"
        descr="$V_WEBTPL/apache_$template.descr"
        ssl="$V_WEBTPL/apache_$template.stpl"

        if [ -z "$check_tpl" ] || [ ! -e $tpl ] || \
           [ ! -e $descr ] || [ ! -e $ssl ]; then
            echo "Error: template not found"
            log_event 'debug' "$E_TPL_NOTEXIST"
            exit $E_TPL_NOTEXIST
        fi
    }

    proxy_template() {
        tpl="$V_WEBTPL/ngingx_vhost_$template.tpl"
        descr="$V_WEBTPL/ngingx_vhost_$template.descr"
        ssl="$V_WEBTPL/ngingx_vhost_$template.stpl"

        if [ ! -e $tpl ] || [ ! -e $descr ] || [ ! -e $ssl ]; then
            echo "Error: template not found"
            log_event 'debug' "$E_TPL_NOTEXIST"
            exit $E_TPL_NOTEXIST
        fi
    }

    dns_template() {
        tpl="$V_DNSTPL/$template.tpl"
        descr="$V_DNSTPL/$template.descr"

        if [ ! -e $tpl ] || [ ! -e $descr ]; then
            echo "Error: template not found"
            log_event 'debug' "$E_TPL_NOTEXIST"
            exit $E_TPL_NOTEXIST
        fi
    }

    # Switching config
    case $stype in
        web) web_template "$template" ;;
        proxy) proxy_template "$template" ;;
        dns) dns_template "$template" ;;
    esac
}


get_user_value() {
    key="$1"
    USER="$user"

    # Parsing domains
    string=$( cat $V_USERS/$user/user.conf )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Print value
    echo "$value"
}

restart_schedule() {
    type="$1"
    period="$2"

    # Checking period
    if [ -z "$period" ]; then
        period=$(grep 'RESTART_PERIOD=' $V_CONF/vesta.conf | cut -f 2 -d \')
    fi

    if [ "$period" -le 0 ]; then
        $V_FUNC/restart_"$type"
    else
        echo "$type" >> $V_QUEUE/restart.pipe
    fi
}

is_user_free() {
    # Parsing domain values
    check_sysuser=$(cut -f 1 -d : /etc/passwd | grep -w "$user" )

    # Checking result
    if [ ! -z "$check_sysuser" ] || [ -e "$V_USERS/$user" ]; then
        echo "Error: user $user exist"
        log_event 'debug' "$E_USER_EXIST $V_EVENT"
        exit $E_USER_EXIST
    fi
}

is_user_privileged() {
    search_user="${1-$user}"

    # Parsing domain values
    user_role=$(grep 'ROLE=' $V_USERS/$search_user/user.conf|cut -f 2 -d \' )

    # Checking role
    if [ "$user_role" != 'reseller' ] && [ "$user_role" != 'admin' ]; then
        echo "Error: user role is $user_role"
        log_event 'debug' "$E_PERMS_REQUEIURED $V_EVENT"
        exit $E_PERMS_REQUEIURED
    fi

    # Checking role permissions
    if [ -n "$role" ]; then
        case "$user_role" in
            admin) rights='reseller, user' ;;
            reseller) rights='user' ;;
            *) rights='no_create' ;;
        esac

        # Comparing rights with role
        check_perms=$(echo "$rights"|grep -w "$role")
        if [ -z  "$check_perms" ]; then
            echo "Error: user rights are '$rights'"
            log_event 'debug' "$E_PERMS_REQUEIURED $V_EVENT"
            exit  $E_PERMS_REQUEIURED
        fi
    fi
}

is_package_valid() {
    if [ ! -e "$V_PKG/$package.pkg" ]; then
        echo "Error: package is not exist"
        log_event 'debug' "$E_PKG_NOTEXIST $v_log"
        exit $E_PKG_NOTEXIST
    fi
}

is_user_key_empty() {
    key="$1"

    # Parsing ip
    string=$(cat $V_USERS/$user/user.conf )

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Checkng key
    if [ ! -z "$value" ] && [ "$value" != 'no' ] && [ "$value" != '0' ]; then
        echo "Error: value is not empty = $value "
        log_event 'debug' "$E_VALUE_EXIST $V_EVENT"
        exit $E_VALUE_EXIST
    fi
}

update_user_value() {
    USER="$1"
    key="$2"
    value="$3"

    # Defining conf
    conf="$V_USERS/$USER/user.conf"

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
    sed -i "s/$c_key='${old//\*/\\*}'/$c_key='${new//\*/\\*}'/g" $conf
}

increase_user_value() {
    USER="$1"
    key="$2"

    # Defining conf
    conf="$V_USERS/$USER/user.conf"

    # Deleting $
    key=$(echo "${key//$/}")

    # Parsing current value
    current_value=$(grep "$key=" $conf |cut -f 2 -d \')

    # Checking result
    if [ -z "$current_value" ]; then
        echo "Error: Parsing error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi

    # Plus one
    new_value=$(expr $current_value + 1 )

    # Changing config
    sed -i "s/$key='$current_value'/$key='$new_value'/g" $conf
}

is_web_domain_cert_valid() {
    # Checking file existance
    path="$V_USERS/$user/cert"
    if [ ! -e "$path/$cert.crt" ] || [ ! -e "$path/$cert.key" ]; then
        echo "Error: certificate not exist"
        log_event 'debug' "$E_CERT_NOTEXIST $V_EVENT"
        exit $E_CERT_NOTEXIST
    fi
}

is_type_valid() {
    # Argument defenition
    sys="$1"
    stype="$2"

    # Switching config
    case $sys in
        stat) skey='STATS_SYSTEM=';;
        db) skey='DB_SYSTEM=' ;;
        *) skey='UNKNOWN' ;;
    esac

    # Parsing domain values
    check_type=$(grep "$skey" $V_CONF/vesta.conf|grep -w $stype)

    # Checking result
    if [ -z "$check_type" ]; then
        echo "Error: unknown type"
        log_event 'debug' "$E_BAD_TYPE $V_EVENT"
        exit $E_BAD_TYPE
    fi
}

change_user_package() {
    # Parsing user data
    usr_data=$(cat $V_USERS/$user/user.conf)
    for key in $usr_data; do
        eval ${key%%=*}=${key#*=}
    done

    # Parsing package
    pkg_data=$(cat $V_PKG/$package.pkg)
    for key in $pkg_data; do
        eval ${key%%=*}=${key#*=}
    done

    echo "FNAME='$FNAME'
LNAME='$LNAME'
PACKAGE='$package'
WEB_DOMAINS='$WEB_DOMAINS'
WEB_SSL='$WEB_SSL'
WEB_ALIASES='$WEB_ALIASES'
DATABASES='$DATABASES'
MAIL_DOMAINS='$MAIL_DOMAINS'
MAIL_BOXES='$MAIL_BOXES'
MAIL_FORWARDERS='$MAIL_FORWARDERS'
DNS_DOMAINS='$DNS_DOMAINS'
DISK_QUOTA='$DISK_QUOTA'
BANDWIDTH='$BANDWIDTH'
NS='$NS'
SHELL='$SHELL'
BACKUPS='$BACKUPS'
WEB_TPL='$WEB_TPL'
MAX_CHILDS='$MAX_CHILDS'
SUSPENDED='$SUSPENDED'
OWNER='$OWNER'
ROLE='$ROLE'
CONTACT='$CONTACT'
REPORTS='$REPORTS'
IP_OWNED='$IP_OWNED'
U_CHILDS='$U_CHILDS'
U_DIR_DISK='$U_DIR_DISK'
U_DISK='$U_DISK'
U_BANDWIDTH='$U_BANDWIDTH'
U_WEB_DOMAINS='$U_WEB_DOMAINS'
U_WEB_SSL='$U_WEB_SSL'
U_DNS_DOMAINS='$U_DNS_DOMAINS'
U_DATABASES='$U_DATABASES'
U_MAIL_DOMAINS='$U_MAIL_DOMAINS'
DATE='$DATE'" > $V_USERS/$user/user.conf
}

get_shell_path() {
    check_shell=$(/usr/bin/chsh --list-shells | grep -w "$shell" )
    echo "$check_shell"
}

is_user_value_exist() {
    key="$1"
    string=$(cat $V_USERS/$user/user.conf )

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

decrease_user_value() {
    USER="$1"
    key="$2"
    conf="$V_USERS/$USER/user.conf"

    # Deleting $
    key=$(echo "${key//$/}")

    # Parsing current value
    current_value=$(grep "$key=" $conf |cut -f 2 -d \')

    # Checking result
    if [ -z "$current_value" ]; then
        echo "Error: Parsing error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi

    # Checking zero val
    if [ "$current_value" -gt 0 ]; then
        # Minus one
        new_value=$(expr $current_value - 1 )
        # Changing config
        sed -i "s/$key='$current_value'/$key='$new_value'/g" $conf
    fi
}

is_user_parent() {
    childs="$(grep "U_CHILDS=" $V_USERS/$user/user.conf |cut -f 2 -d \')"
    if [ -z "$childs" ]; then
        echo "Error: Parsing error"
        log_event 'debug' "$E_PARSE_ERROR $V_EVENT"
        exit $E_PARSE_ERROR
    fi

    if [ "$childs" -gt '0' ]; then
        echo "Error: user have childs"
        log_event 'debug' "$E_CHILD_EXIST $V_EVENT"
        exit $E_CHILD_EXIST
    fi
}

# Json listing function
v_json_list() {
    # Definigng variables
    i='1'       # iterator
    end=$(($limit + $offset))   # last string
    value=''    # clean start value

    # Print top bracket
    echo '{'

    # Reading file line by line
    while read line ; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Parsing key=value
            for key in $line; do
                eval ${key%%=*}=${key#*=}
            done

            # Checking !first line to print bracket
            if [ "$i" -ne "$offset" ]; then
                echo -e "\t},"
            fi

            j=1                 # local loop iterator
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
    done < $conf

    # If there was any output
    if [ -n "$value" ]; then
        echo -e "\t}"
    fi

    # Printing bottom json bracket
    echo -e "}"
}

# Shell listing function
v_shell_list() {

    # Definigng variables
    i='1'                       # iterator
    end=$(($limit + $offset))   # last string
    # Print brief info
    echo "${fields//$/}"
    for a in $fields; do
        echo -e "------ \c"
    done
    echo                        # new line

    # Reading file line by line
    while read line ; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Parsing key=value
            for key in $line; do
                eval ${key%%=*}=${key#*=}
            done
            # Print result line
            eval echo "$fields"
        fi
        i=$(($i + 1))
    done < $conf
}

usr_json_single_list() {
    # Definigng variables
    USER="$user"        # user
    i=1	        # iterator

    # Define words number
    last_word=$(echo "$fields" | wc -w)

    # Reading file line by line
    line=$(cat $V_USERS/$USER/user.conf)

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

usr_shell_single_list() {
    # Definigng variables
    USER="$user"		# user

    # Reading file line by line
    line=$(cat $V_USERS/$USER/user.conf)

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

usr_json_list() {
    i='1'			# iterator
    end=$(($limit + $offset))	# last string

    # Definining user list
    #user_list=$(find $V_USERS/ -maxdepth 1 -mindepth 1 -type d -printf %P\\n )
    user_list=$(ls $V_USERS/)

    # Print top bracket
    echo '{'

    # Starting main loop
    for USER in $user_list; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Reading user data
            user_data=$(cat $V_USERS/$USER/user.conf)

            # Parsing key/value config
            for key in $user_data; do
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

usr_shell_list() {
    i='1'			# iterator
    end=$(($limit + $offset))	# last string

    # Definining user list
    #user_list=$(find $V_USERS/ -maxdepth 1 -mindepth 1 -type d -printf %P\\n )
    user_list=$(ls $V_USERS/)

    # Print brief info
    echo "${fields//$/}"
    for a in $fields; do
        echo -e "--------- \c"
    done
    echo			# new line

    # Starting main loop
    for USER in $user_list; do
        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Reading user data
            user_data=$(cat $V_USERS/$USER/user.conf)

            # Parsing key/value config
            for key in $user_data; do
                eval ${key%%=*}=${key#*=}
            done
            # Print result line
            eval echo "$fields"
        fi
        i=$(($i + 1))
    done
}

usrns_json_list() {
    ns=$(grep "NS[1|2]=" $V_USERS/$user/user.conf |cut -f 2 -d \')
    # Print top bracket
    echo '['
    i=1
    # Listing servers
    for nameserver in $ns;do
        if [ "$i" -eq 1 ]; then
            echo -e  "\t\"$nameserver\","
        else
            echo -e  "\t\"$nameserver\""
        fi
        i=$((i + 1))
    done

    echo "]"
}

usrns_shell_list() {
    ns=$(grep "NS[1|2]=" $V_USERS/$user/user.conf |cut -f 2 -d \')
    # Print result
    echo "NAMESERVER"
    echo "----------"
    for nameserver in $ns;do
        echo "$nameserver"
    done
}

get_usr_disk() {
    size='0'

    # Using tricky way to parse configs
    dir_usage=$(grep 'U_DIR_DISK=' $V_USERS/$user/user.conf |\
	    cut -f 2 -d "'")
    size=$((size + dir_usage))

    # Checking web
    if [ -f "$V_USERS/$user/web.conf" ]; then
	# Using tricky way to parse configs
	disk_usage=$(grep 'U_DISK=' $V_USERS/$user/web.conf |\
	    awk -F "U_DISK='" '{print $2}'|cut -f 1 -d "'")
	for disk in $disk_usage; do 
	    size=$((size + disk))
	done
    fi

    # Checking db
    if [ -f "$V_USERS/$user/db.conf" ]; then
	# Using tricky way to parse configs
	disk_usage=$(grep 'U_DISK=' $V_USERS/$user/db.conf |\
	    awk -F "U_DISK='" '{print $2}'|cut -f 1 -d "'")
	for disk in $disk_usage; do 
	    size=$((size + disk))
	done
    fi

    # Checking mail
    if [ -f "$V_USERS/$user/mail_domains.conf" ]; then
	# Using tricky way to parse configs
	disk_usage=$(grep 'U_DISK=' $V_USERS/$user/mail_domains.conf |\
	    awk -F "U_DISK='" '{print $2}'|cut -f 1 -d "'")
	for disk in $disk_usage; do 
	    size=$((size + disk))
	done
    fi

    echo "$size"
}

get_usr_traff() {
    size='0'
    conf='web.conf'

    # Checking web
    if [ -f "$V_USERS/$user/$conf" ]; then
	# Using tricky way to parse configs
	bandwidth_usage=$(grep 'U_BANDWIDTH=' $V_USERS/$user/$conf|\
	    awk -F "U_BANDWIDTH='" '{print $2}'|cut -f 1 -d "'")
	for bandwidth in $bandwidth_usage; do 
	    size=$((size + bandwidth))
	done
    fi

    echo "$size"
}

pkg_json_list() {
    i='1'                       # iterator
    end=$(($limit + $offset))   # last string

    # Print top bracket
    echo '{'

    # Starting main loop
    for package in $(ls $V_DATA/packages); do
	PACKAGE=${package/.pkg/}

        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
            # Parsing key/value config
            pkg_descr=$(cat $V_DATA/packages/$package)
            for key in $pkg_descr; do
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

pkg_shell_list() {
    i='1'                       # iterator
    end=$(($limit + $offset))   # last string

    # Listing pkg files
    for package in $(ls $V_DATA/packages); do
	PACKAGE=${package/.pkg/}

        # Checking offset and limit
        if [ "$i" -ge "$offset" ] && [ "$i" -lt "$end" ] && [ "$offset" -gt 0 ]
        then
	    # Parsing key=value
            pkg_descr=$(cat $V_DATA/packages/$package)
	    for key in $pkg_descr; do
	        eval ${key%%=*}=${key#*=}
	    done

            echo "----------"

	    # Starting output loop
	    for field in $fields; do
                # Parsing key=value
                eval value=$field
                # Checking first field
                echo -e "${field//$/}: $value"
	    done
	fi
        i=$(($i + 1))
    done
}

get_config_value() {
    key="$1"
    # Parsing config
    string=$(cat $V_CONF/vesta.conf)

    # Parsing key=value
    for keys in $string; do
        eval ${keys%%=*}=${keys#*=}
    done

    # Self reference
    eval value="$key"

    # Print value
    echo "$value"
}

is_backup_enabled() {
    backups=$(grep "BACKUPS='" $V_USERS/$user/user.conf |cut -f 2 -d \')
    if [ -z "$backups" ] || [[ "$backups" -le '0' ]]; then
        echo "Error: User backups are disabled"
        log_event 'debug' "$E_BACKUP_DISABLED $V_EVENT"
        exit $E_BACKUP_DISABLED
    fi
}
