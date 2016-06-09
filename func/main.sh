# Internal variables
HOMEDIR='/home'
BACKUP='/backup'
BACKUP_GZIP=5
BACKUP_DISK_LIMIT=95
BACKUP_LA_LIMIT=5
RRD_STEP=300
BIN=$VESTA/bin
USER_DATA=$VESTA/data/users/$user
WEBTPL=$VESTA/data/templates/web
DNSTPL=$VESTA/data/templates/dns
RRD=$VESTA/web/rrd
SENDMAIL="$VESTA/web/inc/mail-wrapper.php"

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

# Event string for logger
for ((I=1; I <= $# ; I++)); do
    if [[ "$HIDE" != $I ]]; then
        ARGUMENTS="$ARGUMENTS '$(eval echo \$${I})'"
    else
        ARGUMENTS="$ARGUMENTS '******'"
    fi
done

# Log event function
log_event() {
    if [ -z "$time" ]; then
        LOG_TIME="$(date +'%F %T') $(basename $0)"
    else
        LOG_TIME="$date $time $(basename $0)"
    fi
    if [ "$1" -eq 0 ]; then
        echo "$LOG_TIME $2" >> $VESTA/log/system.log
    else
        echo "$LOG_TIME $2 [Error $1]" >> $VESTA/log/error.log
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
    if [ -z "$date" ]; then
        time_n_date=$(date +'%T %F')
        time=$(echo "$time_n_date" |cut -f 1 -d \ )
        date=$(echo "$time_n_date" |cut -f 2 -d \ )
    fi
    curr_str=$(grep "ID=" $log | cut -f 2 -d \' | sort -n | tail -n1)
    id="$((curr_str +1))"
    echo "ID='$id' DATE='$date' TIME='$time' CMD='$cmd' UNDO='$undo'" >> $log
}

# Result checker
check_result() {
    if [ $1 -ne 0 ]; then
        echo "Error: $2"
        if [ ! -z "$3" ]; then
            log_event "$3" "$ARGUMENTS"
            exit $3
        else
            log_event "$1" "$ARGUMENTS"
            exit $1
        fi
    fi
}

# Argument list checker
check_args() {
    if [ "$1" -gt "$2" ]; then
        echo "Usage: $SCRIPT $3"
        check_result $E_ARGS "not enought arguments" >/dev/null
    fi
}

# Subsystem checker
is_system_enabled() {
    if [ -z "$1" ] || [ "$1" = no ]; then
        check_result $E_DISABLED "$2 is not enabled"
    fi
}

# User package check
is_package_full() {
    case "$1" in
        WEB_DOMAINS) used=$(wc -l $USER_DATA/web.conf |cut -f1 -d \ );;
        WEB_ALIASES) used=$(echo $aliases |tr ',' '\n' |wc -l);;
        DNS_DOMAINS) used=$(wc -l $USER_DATA/dns.conf |cut -f1 -d \ );;
        DNS_RECORDS) used=$(wc -l $USER_DATA/dns/$domain.conf|cut -f1 -d \ );;
        MAIL_DOMAINS) used=$(wc -l $USER_DATA/mail.conf |cut -f1 -d \ );;
        MAIL_USER) used=$(wc -l $USER_DATA/mail/$domain.conf |cut -f1 -d \ );;
        DATABASES) used=$(wc -l $USER_DATA/db.conf |cut -f1 -d \ );;
        CRON_JOBS) used=$(wc -l $USER_DATA/cron.conf |cut -f1 -d \ );;
    esac
    limit=$(grep "^$1=" $USER_DATA/user.conf |cut -f 2 -d \')
    if [ "$limit" != 'unlimited' ] && [ "$used" -ge "$limit" ]; then
        check_result $E_LIMIT "$1 limit is reached :: upgrade user package"
    fi
}

# User owner for reseller plugin
get_user_owner() {
    if [ -z "$RESELLER_KEY" ]; then
        owner='admin'
    else
        owner=$(grep "^OWNER" $USER_DATA/user.conf| cut -f 2 -d \')
        if [ -z "$owner" ]; then
            owner='admin'
        fi
    fi
}

# Random password generator
gen_password() {
    PW_MATRIX='0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'
    PW_LENGHT='10'
    pw_matrix=${1-$PW_MATRIX}
    pw_lenght=${2-$PW_LENGHT}
    while [ ${n:=1} -le $pw_lenght ]; do
        pass="$pass${pw_matrix:$(($RANDOM%${#pw_matrix})):1}"
        let n+=1
    done
    echo "$pass"
}

# Package existence check
is_package_valid() {
    if [ -z "$1" ]; then
        pkg_dir="$VESTA/data/packages"
    fi
    if [ ! -e "$pkg_dir/$package.pkg" ]; then
        check_result $E_NOTEXIST "package $package doesn't exist"
    fi
}

# Validate system type
is_type_valid() {
    if [ -z "$(echo $1 | grep -w $2)" ]; then
        check_result $E_INVALID "$2 type is invalid"
    fi
}

# Check if backup is available for user
is_backup_available() {
    b_owner=$(echo $user |\
        sed -e "s/\.[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].tar//")
    if [ "$user" != "$b_owner" ]; then
        check_result $E_FORBIDEN "permission denied"
    fi
}

# Check user backup settings
is_backup_enabled() {
    BACKUPS=$(grep "^BACKUPS=" $USER_DATA/user.conf | cut -f2 -d \')
    if [ -z "$BACKUPS" ] || [[ "$BACKUPS" -le '0' ]]; then
        check_result $E_DISABLED "user backup is disabled"
    fi
}

# Check user backup settings
is_backup_scheduled() {
    if [ -e "$VESTA/data/queue/backup.pipe" ]; then
        check_q=$(grep " $user " $VESTA/data/queue/backup.pipe | grep $1)
        if [ ! -z "$check_q" ]; then
            check_result $E_EXISTS "$1 is already scheduled"
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
        check_result $E_EXISTS "$2=$3 is already exists"
    fi
}

# Check if object is valid
is_object_valid() {
    if [ $2 = 'USER' ]; then
        if [ ! -d "$VESTA/data/users/$3" ]; then
            check_result $E_NOTEXIST "$1 $3 doesn't exist"
        fi
    else
        object=$(grep "$2='$3'" $VESTA/data/users/$user/$1.conf)
        if [ -z "$object" ]; then
            arg1=$(basename $1)
            arg2=$(echo $2 |tr '[:upper:]' '[:lower:]')
            check_result $E_NOTEXIST "$arg1 $arg2 $3 doesn't exist"
        fi
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
        check_result $E_UNSUSPENDED "$(basename $1) $3 is not suspended"
    fi
}

# Check if object is unsupended
is_object_unsuspended() {
    if [ $2 = 'USER' ]; then
        spnd=$(cat $USER_DATA/$1.conf |grep "SUSPENDED='yes'")
    else
        spnd=$(grep "$2='$3'" $USER_DATA/$1.conf |grep "SUSPENDED='yes'")
    fi
    if [ ! -z "$spnd" ]; then
        check_result $E_SUSPENDED "$(basename $1) $3 is suspended"
    fi
}

# Check if object value is empty
is_object_value_empty() {
    str=$(grep "$2='$3'" $USER_DATA/$1.conf)
    eval $str
    eval value=$4
    if [ ! -z "$value" ] && [ "$value" != 'no' ]; then
        check_result $E_EXISTS "${4//$}=$value is already exists"
    fi
}

# Check if object value is empty
is_object_value_exist() {
    str=$(grep "$2='$3'" $USER_DATA/$1.conf)
    eval $str
    eval value=$4
    if [ -z "$value" ] || [ "$value" = 'no' ]; then
        check_result $E_NOTEXIST "${4//$}=$value doesn't exist"
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
    row=$(grep -nF "$2='$3'" $USER_DATA/$1.conf)
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
    grep "^${1//$/}=" $USER_DATA/user.conf |awk -F "'" '{print $2}'
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
        crontab="/var/spool/cron/crontabs/$user"
    else
        crontab="/var/spool/cron/$user"
    fi
    rm -f $crontab
    if [ "$CRON_REPORTS" = 'yes' ]; then
        echo "MAILTO=$CONTACT" > $sys_cron
    fi
    while read line; do
        eval $line
        if [ "$SUSPENDED" = 'no' ]; then
            echo "$MIN $HOUR $DAY $MONTH $WDAY $CMD" |\
                sed -e "s/%quote%/'/g" -e "s/%dots%/:/g" \
                >> $crontab
        fi
    done < $USER_DATA/cron.conf
    chown $user:$user $crontab
    chmod 600 $crontab
}

# User format validator
is_user_format_valid() {
    if ! [[ "$1" =~ ^[a-zA-Z0-9][-|\.|_|a-zA-Z0-9]{0,28}[a-zA-Z0-9]$ ]]; then
        check_result $E_INVALID "invalid user format :: $1"
    fi
}

# Domain format validator
is_domain_format_valid() {
    exclude="[!|@|#|$|^|&|*|(|)|+|=|{|}|:|,|<|>|?|_|/|\|\"|'|;|%|\`| ]"
    if [[ "$1" =~ $exclude ]]; then
        check_result $E_INVALID "invalid domain format :: $1"
    fi
}

# Alias forman validator
is_alias_format_valid() {
    for object in ${1//,/ }; do
        exclude="[!|@|#|$|^|&|(|)|+|=|{|}|:|<|>|?|_|/|\|\"|'|;|%|\`| ]"
        if [[ "$object" =~ $exclude ]]; then
            check_result $E_INVALID "invalid alias format :: $object"
        fi
        if [[ "$object" =~ [*] ]] && ! [[ "$object" =~ ^[*]\..* ]]; then
            check_result $E_INVALID "invalid alias format :: $object"
        fi
    done
}

# IP format validator
is_ip_format_valid() {
    ip_regex='([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])'
    if ! [[ $1 =~ ^$ip_regex\.$ip_regex\.$ip_regex\.$ip_regex$ ]]; then
        check_result $E_INVALID "invalid IP format :: $1"
    fi
}

# Proxy extention format validator
is_extention_format_valid() {
    exclude="[!|#|$|^|&|(|)|+|=|{|}|:|@|<|>|?|/|\|\"|'|;|%|\`| ]"
    if [[ "$1" =~ $exclude ]]; then
        check_result $E_INVALID "invalid proxy extention format :: $1"
    fi
}

# Number format validator
is_number_format_valid() {
    object_name=${2-number}
    if ! [[ "$1" =~ ^[0-9]+$ ]] ; then
       check_result $E_INVALID "invalid $object_name format :: $1"
    fi
}

# Format validation controller
is_format_valid() {
    for arg_name in $*; do
        eval arg=\$$arg_name
        if [ !  -z "$arg" ]; then
            case $arg_name in
                aliases)            is_alias_format_valid "$arg" ;;
                domain)             is_domain_format_valid "$arg" ;;
                proxy_ext)          is_extention_format_valid "$arg" ;;
                ip)                 is_ip_format_valid "$arg" ;;
                user)               is_user_format_valid "$arg" ;;
            esac
        fi
    done
}
