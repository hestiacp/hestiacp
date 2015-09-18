# Internal variables
DATE=$(date +%F)
TIME=$(date +%T)
SCRIPT=$(basename $0)
A1=$1
A2=$2
A3=$3
A4=$4
A5=$5
A6=$6
A7=$7
A8=$8
A9=$9
EVENT="$DATE $TIME $SCRIPT $A1 $A2 $A3 $A4 $A5 $A6 $A7 $A8 $A9"
HOMEDIR='/home'
BACKUP='/backup'
BACKUP_GZIP=5
BACKUP_DISK_LIMIT=95
BACKUP_LA_LIMIT=5
RRD_STEP=300
RRD_IFACE_EXCLUDE=lo
PW_MATRIX='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
PW_LENGHT='10'
BIN=$VESTA/bin
USER_DATA=$VESTA/data/users/$user
WEBTPL=$VESTA/data/templates/web
DNSTPL=$VESTA/data/templates/dns
RRD=$VESTA/web/rrd
send_mail="$VESTA/web/inc/mail-wrapper.php"

# Return codes
OK=0
E_ARGS=1
E_INVALID=2
E_NOTEXIST=3
E_EXISTS=4
E_SUSPENDED=5
E_UNSUSPENDED=6
E_INUSE=7
E_LIMIT=8
E_PASSWORD=9
E_FORBIDEN=10
E_DISABLED=11
E_PARSING=12
E_DISK=13
E_LA=14
E_CONNECT=15
E_FTP=16
E_DB=17
E_RRD=18
E_UPDATE=19
E_RESTART=20

# Log event function
log_event() {
    if [ "$1" -eq 0 ]; then
        echo "$2" >> $VESTA/log/system.log
    else
        echo "$2 [Error $1]" >> $VESTA/log/error.log
    fi
}

# Log user history
log_history() {
    cmd=$1
    undo=${2-no}
    log_user=${3-$user}
    log=$VESTA/data/users/$log_user/history.log

    touch $log
    if [ '99' -lt "$(wc -l $log |cut -f 1 -d ' ')" ]; then
        tail -n 49 $log > $log.moved
        mv -f $log.moved $log
        chmod 660 $log
    fi

    curr_str=$(grep "ID=" $log | cut -f 2 -d \' | sort -n | tail -n1)
    id="$((curr_str +1))"
    echo "ID='$id' DATE='$DATE' TIME='$TIME' CMD='$cmd' UNDO='$undo'" >> $log
}

# Argument list checker
check_args() {
    if [ "$1" -gt "$2" ]; then
        echo "Error: not enought arguments"
        echo "Usage: $SCRIPT $3"
        log_event "$E_ARGS" "$EVENT"
        exit $E_ARGS
    fi
}

# Subsystem checker
is_system_enabled() {
    if [ -z "$1" ] || [ "$1" = no ]; then
        echo "Error: $2 is not enabled in the $VESTA/conf/vesta.conf"
        log_event "$E_DISABLED" "$EVENT"
        exit $E_DISABLED
    fi
}

# User package check
is_package_full() {
    case "$1" in
        WEB_DOMAINS) used=$(wc -l $USER_DATA/web.conf|cut -f1 -d \ );;
        WEB_ALIASES) used=$(grep "DOMAIN='$domain'" $USER_DATA/web.conf |\
             awk -F "ALIAS='" '{print $2}' | cut -f 1 -d \' | tr ',' '\n' |\
             wc -l );;
        DNS_DOMAINS) used=$(wc -l $USER_DATA/dns.conf |cut -f1 -d \ );;
        DNS_RECORDS) used=$(wc -l $USER_DATA/dns/$domain.conf |cut -f1 -d \ );;
        MAIL_DOMAINS) used=$(wc -l $USER_DATA/mail.conf |cut -f1 -d \ );;
        MAIL_ACCOUNTS) used=$(wc -l $USER_DATA/mail/$domain.conf |\
            cut -f1 -d \ );;
        DATABASES) used=$(wc -l $USER_DATA/db.conf |cut -f1 -d \ );;
        CRON_JOBS) used=$(wc -l $USER_DATA/cron.conf |cut -f1 -d \ );;
    esac
    limit=$(grep "^$1=" $USER_DATA/user.conf | cut -f 2 -d \' )
    if [ "$limit" != 'unlimited' ] && [ "$used" -ge "$limit" ]; then
        echo "Error: Limit is reached, please upgrade hosting package"
        log_event "$E_LIMIT" "$EVENT"
        exit $E_LIMIT
    fi
}

# Random password generator
gen_password() {
    pw_matrix=${1-$PW_MATRIX}
    pw_lenght=${2-$PW_LENGHT}
    while [ ${n:=1} -le $pw_lenght ]; do
        pass="$pass${pw_matrix:$(($RANDOM%${#pw_matrix})):1}"
        let n+=1
    done
    echo "$pass"
}

# Package existance check
is_package_valid() {
    if [ -z "$1" ]; then
        pkg_dir="$VESTA/data/packages"
    fi
    if [ ! -e "$pkg_dir/$package.pkg" ]; then
        echo "Error: package $package doesn't exist"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
}

# Validate system type
is_type_valid() {
    if [ -z "$(echo $1 | grep -w $2)" ]; then
        echo "Error: $2 is unknown type"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Check if backup is available for user
is_backup_available() {
    b_owner=$(echo $user |\
        sed -e "s/\.[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].tar//")
    if [ "$user" != "$b_owner" ]; then
        echo "Error: User $user don't have permission to use $backup"
        log_event "$E_FORBIDEN" "$EVENT"
        exit $E_FORBIDEN
    fi
}

# Check user backup settings
is_backup_enabled() {
    BACKUPS=$(grep "^BACKUPS=" $USER_DATA/user.conf | cut -f2 -d \')
    if [ -z "$BACKUPS" ] || [[ "$BACKUPS" -le '0' ]]; then
        echo "Error: user backup disabled"
        log_event "$E_DISABLED" "$EVENT"
        exit $E_DISABLED
    fi
}

# Check user backup settings
is_backup_scheduled() {
    if [ -e "$VESTA/data/queue/backup.pipe" ]; then
        check_q=$(grep " $user " $VESTA/data/queue/backup.pipe | grep $1)
        if [ ! -z "$check_q" ]; then
            echo "Error: $1 is already scheduled"
            log_event "$E_EXISTS" "$EVENT"
            exit $E_EXISTS
        fi
    fi
}

# Check if object is new
is_object_new() {
    if [ $2 = 'USER' ]; then
        if [ -d "$USER_DATA" ]; then
            object="OK"
        fi
    else
        object=$(grep "$2='$3'" $USER_DATA/$1.conf)
    fi
    if [ ! -z "$object" ]; then
        echo "Error: $2 with value $3 exists"
        log_event "$E_EXISTS" "$EVENT"
        exit $E_EXISTS
    fi
}

# Check if object exists and can be used
is_object_valid() {
    if [ $2 = 'USER' ]; then
        if [ -d "$VESTA/data/users/$user" ]; then
            sobject="OK"
        fi
    else
        if [ $2 = 'DBHOST' ]; then
            sobject=$(grep "HOST='$host'" $VESTA/conf/$type.conf)
        else
            sobject=$(grep "$2='$3'" $VESTA/data/users/$user/$1.conf)
        fi
    fi
    if [ -z "$sobject" ]; then
        echo "Error: $2 $3 doesn't exist"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
}

# Check if object is supended
is_object_suspended() {
    if [ $2 = 'USER' ]; then
        spnd=$(cat $USER_DATA/$1.conf|grep "SUSPENDED='yes'")
    else
        spnd=$(grep "$2='$3'" $USER_DATA/$1.conf|grep "SUSPENDED='yes'")
    fi
    if [ -z "$spnd" ]; then
        echo "Error: $(basename $1) $3 is not suspended"
        log_event "$E_SUSPENDED" "$EVENT"
        exit $E_SUSPENDED
    fi
}

# Check if object is unsupended
is_object_unsuspended() {
    if [ $2 = 'USER' ]; then
        spnd=$(cat $USER_DATA/$1.conf|grep "SUSPENDED='yes'")
    else
        spnd=$(grep "$2='$3'" $USER_DATA/$1.conf|grep "SUSPENDED='yes'")
    fi
    if [ ! -z "$spnd" ]; then
        echo "Error: $(basename $1) $3 is suspended"
        log_event "$E_UNSUSPENDED" "$EVENT"
        exit $E_UNSUSPENDED
    fi
}

# Check if object value is empty
is_object_value_empty() {
    str=$(grep "$2='$3'" $USER_DATA/$1.conf)
    eval $str
    eval value=$4
    if [ ! -z "$value" ] && [ "$value" != 'no' ]; then
        echo "Error: ${4//$}=$value (not empty)"
        log_event "$E_EXISTS" "$EVENT"
        exit $E_EXISTS
    fi
}

# Check if object value is empty
is_object_value_exist() {
    str=$(grep "$2='$3'" $USER_DATA/$1.conf)
    eval $str
    eval value=$4
    if [ -z "$value" ] || [ "$value" = 'no' ]; then
        echo "Error: ${4//$}=$value (doesn't exist)"
        log_event "$E_NOTEXIST" "$EVENT"
        exit $E_NOTEXIST
    fi
}

# Check if password is transmitted via file
is_password_valid() {
    if [[ "$password" =~ ^/tmp/ ]]; then
        if [ -f "$password" ]; then
            password=$(head -n1 $password)
        fi
    fi
}

# Get object value
get_object_value() {
    object=$(grep "$2='$3'" $USER_DATA/$1.conf)
    eval "$object"
    eval echo $4
}

# Update object value
update_object_value() {
    row=$(grep -n "$2='$3'" $USER_DATA/$1.conf)
    lnr=$(echo $row | cut -f 1 -d ':')
    object=$(echo $row | sed "s/^$lnr://")
    eval "$object"
    eval old="$4"
    old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    new=$(echo "$5" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
    sed -i "$lnr s/${4//$/}='${old//\*/\\*}'/${4//$/}='${new//\*/\\*}'/g" \
        $USER_DATA/$1.conf
}

# Add object key
add_object_key() {
    row=$(grep -n "$2='$3'" $USER_DATA/$1.conf)
    lnr=$(echo $row | cut -f 1 -d ':')
    object=$(echo $row | sed "s/^$lnr://")
    if [ -z "$(echo $object |grep $4=)" ]; then
        eval old="$4"
        sed -i "$lnr s/$5='/$4='' $5='/" $USER_DATA/$1.conf
    fi
}

# Search objects
search_objects() {
    OLD_IFS="$IFS"
    IFS=$'\n'
    for line in $(grep $2=\'$3\' $USER_DATA/$1.conf); do
        eval $line
        eval echo \$$4
    done
    IFS="$OLD_IFS"
}

# Get user value
get_user_value() {
    grep "^${1//$/}=" $USER_DATA/user.conf| cut -f 2 -d \'
}

# Update user value in user.conf
update_user_value() {
    key="${2//$}"
    lnr=$(grep -n "^$key='" $VESTA/data/users/$1/user.conf |cut -f 1 -d ':')
    if [ ! -z "$lnr" ]; then
        sed -i "$lnr d" $VESTA/data/users/$1/user.conf
        sed -i "$lnr i\\$key='${3}'" $VESTA/data/users/$1/user.conf
    fi
}

# Increase user counter
increase_user_value() {
    key="${2//$}"
    factor="${3-1}"
    conf="$VESTA/data/users/$1/user.conf"
    old=$(grep "$key=" $conf | cut -f 2 -d \')
    if [ -z "$old" ]; then
        old=0
    fi
    new=$((old + factor))
    sed -i "s/$key='$old'/$key='$new'/g" $conf
}

# Decrease user counter
decrease_user_value() {
    key="${2//$}"
    factor="${3-1}"
    conf="$VESTA/data/users/$1/user.conf"
    old=$(grep "$key=" $conf | cut -f 2 -d \')
    if [ -z "$old" ]; then
        old=0
    fi
    if [ "$old" -le 1 ]; then
        new=0
    else
        new=$((old - factor))
    fi
    if [ "$new" -lt 0 ]; then
        new=0
    fi
    sed -i "s/$key='$old'/$key='$new'/g" $conf
}

# Json listing function
json_list() {
    echo '{'
    fileds_count=$(echo $fields| wc -w )
    #for line in $(cat $conf); do
    while read line; do
        eval $line
        if [ -n "$data_output" ]; then
            echo -e '        },'
        fi
        i=1
        for field in $fields; do
            eval value=$field
            if [ $i -eq 1 ]; then
                (( ++i))
                echo -e "\t\"$value\": {"
            else
                if [ $i -lt $fileds_count ]; then
                    (( ++i))
                    echo -e "\t\t\"${field//$/}\": \"$value\","
                else
                    echo -e "\t\t\"${field//$/}\": \"$value\""
                    data_output=yes
                fi
            fi
        done
    done < $conf

    if [ "$data_output" = 'yes' ]; then
        echo -e '        }'
    fi
    echo -e '}'
}

# Shell listing function
shell_list() {
    if [ -z "$nohead" ] ; then
        echo "${fields//$/}"
        for a in $fields; do
            echo -e "------ \c"
        done
        echo
    fi
    while read line ; do
        eval $line
        for field in $fields; do
            eval value=$field
            if [ -z "$value" ]; then
                value='NULL'
            fi
            echo -n "$value "
        done
        echo
    done < $conf
}

# Recalculate U_DISK value
recalc_user_disk_usage() {
    u_usage=0
    if [ -f "$USER_DATA/web.conf" ]; then
        usage=0
        dusage=$(grep 'U_DISK=' $USER_DATA/web.conf |\
            awk -F "U_DISK='" '{print $2}' | cut -f 1 -d \')
        for disk_usage in $dusage; do 
                usage=$((usage + disk_usage))
        done
        d=$(grep "U_DISK_WEB='" $USER_DATA/user.conf | cut -f 2 -d \')
        sed -i "s/U_DISK_WEB='$d'/U_DISK_WEB='$usage'/g" $USER_DATA/user.conf
        u_usage=$((u_usage + usage))
    fi

    if [ -f "$USER_DATA/mail.conf" ]; then
        usage=0
        dusage=$(grep 'U_DISK=' $USER_DATA/mail.conf |\
            awk -F "U_DISK='" '{print $2}' | cut -f 1 -d \')
        for disk_usage in $dusage; do 
                usage=$((usage + disk_usage))
        done
        d=$(grep "U_DISK_MAIL='" $USER_DATA/user.conf | cut -f 2 -d \')
        sed -i "s/U_DISK_MAIL='$d'/U_DISK_MAIL='$usage'/g" $USER_DATA/user.conf
        u_usage=$((u_usage + usage))
    fi

    if [ -f "$USER_DATA/db.conf" ]; then
        usage=0
        dusage=$(grep 'U_DISK=' $USER_DATA/db.conf |\
            awk -F "U_DISK='" '{print $2}' | cut -f 1 -d \')
        for disk_usage in $dusage; do 
                usage=$((usage + disk_usage))
        done
        d=$(grep "U_DISK_DB='" $USER_DATA/user.conf | cut -f 2 -d \')
        sed -i "s/U_DISK_DB='$d'/U_DISK_DB='$usage'/g" $USER_DATA/user.conf
        u_usage=$((u_usage + usage))
    fi
    usage=$(grep 'U_DISK_DIRS=' $USER_DATA/user.conf | cut -f 2 -d "'")
    u_usage=$((u_usage + usage))
    old=$(grep "U_DISK='" $USER_DATA/user.conf | cut -f 2 -d \')
    sed -i "s/U_DISK='$old'/U_DISK='$u_usage'/g" $USER_DATA/user.conf
}

# Recalculate U_BANDWIDTH value
recalc_user_bandwidth_usage() {
    usage=0
    bandwidth_usage=$(grep 'U_BANDWIDTH=' $USER_DATA/web.conf |\
        awk -F "U_BANDWIDTH='" '{print $2}'|cut -f 1 -d \')
    for bandwidth in $bandwidth_usage; do 
        usage=$((usage + bandwidth))
    done
    old=$(grep "U_BANDWIDTH='" $USER_DATA/user.conf | cut -f 2 -d \')
    sed -i "s/U_BANDWIDTH='$old'/U_BANDWIDTH='$usage'/g" $USER_DATA/user.conf
}

# Get next cron job id
get_next_cronjob() {
    if [ -z "$job" ]; then
        curr_str=$(grep "JOB=" $USER_DATA/cron.conf|cut -f 2 -d \'|\
                 sort -n|tail -n1)
        job="$((curr_str +1))"
    fi
}

# Sort cron jobs by id
sort_cron_jobs() {
    cat $USER_DATA/cron.conf |sort -n -k 2 -t \' > $USER_DATA/cron.tmp
    mv -f $USER_DATA/cron.tmp $USER_DATA/cron.conf
}

# Sync cronjobs with system cron
sync_cron_jobs() {
    source $USER_DATA/user.conf

    if [ -e "/var/spool/cron/crontabs" ]; then
        sys_cron="/var/spool/cron/crontabs/$user"
    else
        sys_cron="/var/spool/cron/$user"
    fi
    rm -f $sys_cron
    if [ "$CRON_REPORTS" = 'yes' ]; then
        echo "MAILTO=$CONTACT" > $sys_cron
    fi
    while read line; do
        eval $line
        if [ "$SUSPENDED" = 'no' ]; then
            echo "$MIN $HOUR $DAY $MONTH $WDAY $CMD" |\
                sed -e "s/%quote%/'/g" -e "s/%dots%/:/g" \
                >> $sys_cron
        fi
    done < $USER_DATA/cron.conf

    # Set proper permissions
    chown $user:$user $sys_cron
    chmod 600 $sys_cron
}


### Format Validators ###
# Shell
validate_format_shell() {
    if [ -z "$(grep -w $1 /etc/shells)" ]; then
        echo "Error: shell $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Password
validate_format_password() {
    if [ "${#1}" -lt '6' ]; then
        echo "Error: password is too short"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Integer
validate_format_int() {
    if ! [[ "$1" =~ ^[0-9]+$ ]] ; then 
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Boolean
validate_format_boolean() {
    if [ "$1" != 'yes' ] && [ "$1" != 'no' ]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Network interface
validate_format_interface() {
    netdevices=$(cat /proc/net/dev | grep : | cut -f 1 -d : | tr -d ' ')
    if [ -z $(echo "$netdevices"| grep -x $1) ]; then
        echo "Error: intreface $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# IP address
validate_format_ip() {
    t_ip=$(echo $1 |awk -F / '{print $1}')
    t_cidr=$(echo $1 |awk -F / '{print $2}')
    valid_octets=0
    valid_cidr=1
    for octet in ${t_ip//./ }; do
        if [[ $octet =~ ^[0-9]{1,3}$ ]] && [[ $octet -le 255 ]]; then
            ((++valid_octets))
        fi
    done

    if [ ! -z "$(echo $1|grep '/')" ]; then
        if [[ "$t_cidr" -lt 0 ]] || [[ "$t_cidr" -gt 32 ]]; then
            valid_cidr=0
        fi
        if ! [[ "$t_cidr" =~ ^[0-9]+$ ]]; then
            valid_cidr=0
        fi
    fi
    if [ "$valid_octets" -lt 4 ] || [ "$valid_cidr" -eq 0 ]; then
        echo "Error: ip $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# IP address status
validate_format_ip_status() {
    if [ -z "$(echo shared,dedicated | grep -w $1 )" ]; then
        echo "Error: ip_status $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Email address
validate_format_email() {
    if [[ ! "$1" =~ "@" ]] ; then
        echo "Error: email $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Name
validate_format_name() {
    if ! [[ "$1" =~ ^[[:alnum:]][-|\.|_[:alnum:]]{0,28}[[:alnum:]]$ ]]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Name with space
validate_format_name_s() {
    if ! [[ "$1" =~ ^[[:alnum:]][-|\ |\.|_[:alnum:]]{0,28}[[:alnum:]]$ ]]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Username
validate_format_username() {
    if [ "${#1}" -eq 1 ]; then
        if ! [[ "$1" =~ [a-z] ]]; then
            echo "Error: $2 $1 is not valid"
            log_event "$E_INVALID" "$EVENT"
            exit 1
        fi
    else
        if ! [[ "$1" =~ ^[a-zA-Z0-9][-|\.|_|a-zA-Z0-9]{0,28}[a-zA-Z0-9]$ ]]
        then
            echo "Error: $2 $1 is not valid"
            log_event "$E_INVALID" "$EVENT"
            exit 1
        fi
    fi
}

# Domain
validate_format_domain() {
    exclude="[!|@|#|$|^|&|*|(|)|+|=|{|}|:|,|<|>|?|_|/|\|\"|'|;|%|\`| ]"
    if [[ "$1" =~ $exclude ]] || [[ "$1" =~ "^[0-9]+$" ]]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Domain alias
validate_format_domain_alias() {
    exclude="[!|@|#|$|^|&|(|)|+|=|{|}|:|,|<|>|?|_|/|\|\"|'|;|%|\`| ]"
    if [[ "$1" =~ $exclude ]] || [[ "$1" =~ "^[0-9]+$" ]]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Database
validate_format_database() {
    exclude="[!|@|#|$|^|&|*|(|)|+|=|{|}|:|,|<|>|?|/|\|\"|'|;|%|\`| ]"
    if [[ "$1" =~ $exclude ]] || [ 65 -le ${#1} ]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Database user
validate_format_dbuser() {
    exclude="[!|@|#|$|^|&|*|(|)|+|=|{|}|:|,|<|>|?|/|\|\"|'|;|%|\`| ]"
    if [[ "$1" =~ $exclude ]] || [ 17 -le ${#1} ]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# DNS type
validate_format_dns_type() {
    known_dnstype='A,AAAA,NS,CNAME,MX,TXT,SRV,DNSKEY,KEY,IPSECKEY,PTR,SPF'
    if [ -z "$(echo $known_dnstype | grep -w $1)" ]; then
        echo "Error: dnstype $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# DKIM key size
validate_format_key_size() {
    known_size='128,256,512,768,1024,2048'
    if [ -z "$(echo $known_size | grep -w $1)" ]; then
        echo "Error: key_size $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Minute / Hour / Day / Month / Day of Week
validate_format_mhdmw() {
    limit=60
    check_format=''
    if [ "$2" = 'day' ]; then
        limit=31
    fi
    if [ "$2" = 'month' ]; then
        limit=12
    fi
    if [ "$2" = 'wday' ]; then
        limit=7
    fi
    if [ "$1" = '*' ]; then
        check_format='ok'
    fi
    if [[ "$1" =~ ^[\*]+[/]+[0-9] ]]; then
        if [ "$(echo $1 |cut -f 2 -d /)" -lt $limit ]; then
            check_format='ok'
        fi
    fi
    if [[ "$1" =~ ^[0-9][-|,|0-9]{0,28}[0-9]$ ]]; then
        check_format='ok'
        crn_values=${1//,/ }
        crn_values=${crn_values//-/ }
        for crn_vl in $crn_values; do
            if [ "$crn_vl" -gt $limit ]; then
                check_format='invalid'
            fi
        done
    fi
    if [[ "$1" =~ ^[0-9]+$ ]] && [ "$1" -lt $limit ]; then
        check_format='ok'
    fi
    if [ "$check_format" != 'ok' ]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# proxy extention or DNS record
validate_format_common() {
    exclude="[!|#|$|^|&|(|)|+|=|{|}|:|<|>|?|/|\|\"|'|;|%|\`| ]"
    if [[ "$1" =~ $exclude ]]; then
        echo "Error: $2 $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
    if [ 400 -le ${#1} ]; then
        echo "Error: $2 $1 is too long"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
    if [[ "$1" =~ @ ]] && [ ${#1} -gt 1 ] ; then
        echo "Error: @ can not be mixed"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
    if [[ $1 =~ \* ]]; then
        if [ "$(echo $1 | grep -o '*'|wc -l)" -gt 1 ]; then
            log_event "$E_INVALID" "$EVENT"
            echo "Error: * can be used only once"
        fi
    fi
}

# DNS record value
validate_format_dvalue() {
    record_types="$(echo A,AAAA,NS,CNAME | grep -w "$rtype")"
    if [[ "$1" =~ [\ ] ]] && [ ! -z "$record_types" ]; then
        echo "Error: dvalue $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
    if [ "$rtype" = 'A' ]; then
        validate_format_ip "$1"
    fi
    if [ "$rtype" = 'NS' ]; then
        validate_format_domain "$1" 'ns_record'
    fi
    if [ "$rtype" = 'MX' ]; then
        validate_format_domain "$1" 'mx_record'
        validate_format_int "$priority" 'priority_record'
    fi

}

# Date
validate_format_date() {
    if ! [[ "$1" =~ ^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$ ]]; then
        echo "Error: date $1 is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Autoreply
validate_format_autoreply() {
    exclude="[$|\`]"
    if [[ "$1" =~ $exclude ]] || [ 10240 -le ${#1} ]; then
        echo "Error: autoreply is not valid"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Firewall action
validate_format_fw_action() {
    if [ "$1" != "ACCEPT" ] && [ "$1" != 'DROP' ] ; then
        echo "Error: $1 is not valid action"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Firewall protocol
validate_format_fw_protocol() {
    if [ "$1" != "ICMP" ] && [ "$1" != 'UDP' ] && [ "$1" != 'TCP' ] ; then
        echo "Error: $1 is not valid protocol"
        log_event "$E_INVALID" "$EVENT"
        exit $E_INVALID
    fi
}

# Firewall port
validate_format_fw_port() {
    if [ "${#1}" -eq 1 ]; then
        if ! [[ "$1" =~ [0-9] ]]; then
            echo "Error: port $1 is not valid"
            log_event "$E_INVALID" "$EVENT"
            exit 1
        fi
    else
        if ! [[ "$1" =~ ^[0-9][-|,|:|0-9]{0,30}[0-9]$ ]]
        then
            echo "Error: port $1 is not valid"
            log_event "$E_INVALID" "$EVENT"
            exit 1
        fi
    fi
}

# Format validation controller
validate_format(){
    for arg_name in $*; do
        eval arg=\$$arg_name
        if [ -z "$arg" ]; then
            echo "Error: argument $arg_name is not valid (empty)"
            log_event "$E_INVALID" "$EVENT"
            exit $E_INVALID
        fi

        case $arg_name in
            account)        validate_format_username "$arg" "$arg_name" ;;
            action)         validate_format_fw_action "$arg";;
            antispam)       validate_format_boolean "$arg" 'antispam' ;;
            antivirus)      validate_format_boolean "$arg" 'antivirus' ;;
            autoreply)      validate_format_autoreply "$arg" ;;
            backup)         validate_format_domain "$arg" 'backup' ;;
            charset)        validate_format_name "$arg" "$arg_name" ;;
            charsets)       validate_format_common "$arg" 'charsets' ;;
            comment)        validate_format_name "$arg" 'comment' ;;
            database)       validate_format_database "$arg" 'database';;
            day)            validate_format_mhdmw "$arg" $arg_name ;;
            dbpass)         validate_format_password "$arg" ;;
            dbuser)         validate_format_dbuser "$arg" 'db_user';;
            dkim)           validate_format_boolean "$arg" 'dkim' ;;
            dkim_size)      validate_format_key_size "$arg" ;;
            domain)         validate_format_domain "$arg" 'domain';;
            dom_alias)      validate_format_domain_alias "$arg" 'alias';;
            dvalue)         validate_format_dvalue "$arg";;
            email)          validate_format_email "$arg" ;;
            exp)            validate_format_date "$arg" ;;
            extentions)     validate_format_common "$arg" 'extentions' ;;
            fname)          validate_format_name_s "$arg" "$arg_name" ;;
            forward)        validate_format_email "$arg" ;;
            ftp_password)   validate_format_password "$arg" ;;
            ftp_user)       validate_format_username "$arg" "$arg_name" ;;
            host)           validate_format_domain "$arg" "$arg_name" 'host';;
            hour)           validate_format_mhdmw "$arg" $arg_name ;;
            id)             validate_format_int "$arg" 'id' ;;
            interface)      validate_format_interface "$arg" ;;
            ip)             validate_format_ip "$arg" ;;
            ip_name)        validate_format_domain "$arg" 'domain';;
            ip_status)      validate_format_ip_status "$arg" ;;
            job)            validate_format_int "$arg" 'job' ;;
            key)            validate_format_username "$arg" "$arg_name" ;;
            lname)          validate_format_name_s "$arg" "$arg_name" ;;
            malias)         validate_format_username "$arg" "$arg_name" ;;
            max_db)         validate_format_int "$arg" 'max db';;
            min)            validate_format_mhdmw "$arg" $arg_name ;;
            month)          validate_format_mhdmw "$arg" $arg_name ;;
            nat_ip)         validate_format_ip "$arg" ;;
            netmask)        validate_format_ip "$arg" ;;
            newid)          validate_format_int "$arg" 'id' ;;
            ns1)            validate_format_domain "$arg" 'name_server';;
            ns2)            validate_format_domain "$arg" 'name_server';;
            ns3)            validate_format_domain "$arg" 'name_server';;
            ns4)            validate_format_domain "$arg" 'name_server';;
            object)         validate_format_name_s "$arg" 'object';;
            package)        validate_format_name "$arg" "$arg_name" ;;
            password)       validate_format_password "$arg" ;;
            port)           validate_format_int "$arg" 'port' ;;
            port_ext)       validate_format_fw_port "$arg";;
            protocol)       validate_format_fw_protocol "$arg" ;;
            quota)          validate_format_int "$arg" 'quota' ;;
            restart)        validate_format_boolean "$arg" 'restart' ;;
            record)         validate_format_common "$arg" 'record';;
            rtype)          validate_format_dns_type "$arg" ;;
            rule)           validate_format_int "$arg" "rule id" ;;
            shell)          validate_format_shell "$arg" ;;
            soa)            validate_format_domain "$arg" 'soa_record';;
            stats_pass)     validate_format_password "$arg" ;;
            stats_user)     validate_format_username "$arg" "$arg_name" ;;
            template)       validate_format_name "$arg" "$arg_name" ;;
            ttl)            validate_format_int "$arg" 'ttl';;
            user)           validate_format_username "$arg" "$arg_name" ;;
            wday)           validate_format_mhdmw "$arg" $arg_name ;;
        esac
    done
}
