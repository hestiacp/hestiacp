#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - IP/Network Function Library                        #
#                                                                           #
#===========================================================================#

# === Global definitions ===
REGEX_IPV4="^((1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])\.){3}(1?[0-9][0-9]?|2[0-4][0-9]|25[0-5])$"

# === IPV4 specific functions ===

# Check ip ownership
is_ip_owner() {
	# ip address (ipv4/ipv6) as first parameter, otherwise $ip (ipv4)
	ip_for_test="${1-$ip}"
	owner=$(grep 'OWNER=' $HESTIA/data/ips/$ip_for_test | cut -f 2 -d \')
	if [ "$owner" != "$user" ]; then
		check_result "$E_FORBIDEN" "$ip_for_test is not owned by $user"
	fi
}

# Check if ip address is free
is_ip_free() {
	ip_for_test="${1-$ip}" # ip address (ipv4/ipv6) as first parameter, otherwise $ip (ipv4)
	if [ -e "$HESTIA/data/ips/$ip_for_test" ]; then
		check_result "$E_EXISTS" "$ip_for_test is already exists"
	fi
}

# Check ip address specific value
is_ip_key_empty() {
	key="$1"
	ip_for_test="${2-$ip}" # ip address (ipv4/ipv6) as second parameter, otherwise $ip (ipv4)
	if [ -n "$ip_for_test" ]; then
		string=$(cat $HESTIA/data/ips/$ip_for_test)
		eval $string
		eval value="$key"
		if [ -n "$value" ] && [ "$value" != '0' ]; then
			key="$(echo $key | sed -e "s/\$U_//")"
			check_result "$E_EXISTS" "IP is in use / $key = $value"
		fi
	else
		check_result 1 "is_ip_key_empty(): IP address is empty!"
	fi
}

is_ip_rdns_valid() {
	local ip="$1"
	local network_ip=$(echo $ip | cut -d"." -f1-3)
	local awk_ip=$(echo $network_ip | sed 's|\.|/\&\&/|g')
	local rev_awk_ip=$(echo $awk_ip | rev)

	if [ -z "$rdns" ]; then
		local rdns=$(dig +short -x "$ip" | head -n 1 | sed 's/.$//') || unset rdns
	fi

	if [ -n "$rdns" ] && [ ! $(echo $rdns | awk "/$awk_ip/ || /$rev_awk_ip/") ]; then
		echo $rdns
		return 0 # True
	fi

	return 1 # False
}

# Update ip address value
update_ip_value() {
	key="$1"
	value="$2"
	ip_for_update="${3-$ip}" # ip address (ipv4/ipv6) as third parameter, otherwise $ip (ipv4)
	if [ -n "$ip_for_update" ]; then
		conf="$HESTIA/data/ips/$ip_for_update"
		str=$(cat $conf)
		eval $str
		c_key=$(echo "${key//$/}")
		eval old="${key}"
		old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
		new=$(echo "$value" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
		sed -i "$str_number s/$c_key='${old//\*/\\*}'/$c_key='${new//\*/\\*}'/g" \
			$conf
	else
		check_result 1 "is_ip_key_empty(): IP address is empty!"
	fi
}

# New method that is improved on a later date we need to check if we can improve it for other locations
update_ip_value_new() {
	key="$1"
	value="$2"
	conf="$HESTIA/data/ips/$ip"
	check_ckey=$(grep "^$key='" $conf)
	if [ -z "$check_ckey" ]; then
		echo "$key='$value'" >> $conf
	else
		sed -i "s|^$key=.*|$key='$value'|g" $conf
	fi
}

# Get ip name
get_ip_alias() {
	# ip address (ipv4/ipv6) as second parameter, otherwise $local_ip (ipv4)
	ip_for_test="${2-$local_ip}"
	if [ -n "$ip_for_test" ]; then
		ip_name=$(grep "NAME=" $HESTIA/data/ips/${ip_for_test} | cut -f 2 -d \')
		if [ -n "$ip_name" ]; then
			echo "${1//./-}.$ip_name"
		fi
	else
		ip_name=""
	fi
}

# Increase ip value
increase_ip_value() {
	sip=${1-ip}
	USER=${2-$user}
	web_key='U_WEB_DOMAINS'
	usr_key='U_SYS_USERS'
	current_web=$(grep "$web_key=" $HESTIA/data/ips/$sip | cut -f 2 -d \')
	current_usr=$(grep "$usr_key=" $HESTIA/data/ips/$sip | cut -f 2 -d \')
	if [ -z "$current_web" ]; then
		echo "Error: Parsing error"
		log_event "$E_PARSING" "$ARGUMENTS"
		exit "$E_PARSING"
	fi
	if (($current_web <= 0)); then
		new_web=1
	else
		new_web=$((current_web + 1))
	fi
	if [ -z "$current_usr" ]; then
		new_usr="$USER"
	else
		check_usr=$(echo -e "${current_usr//,/\\n}" | grep -x "$USER")
		if [ -z "$check_usr" ]; then
			new_usr="$current_usr,$USER"
		else
			new_usr="$current_usr"
		fi
	fi

	# Make sure users list does not contain duplicates
	new_usr=$(echo "$new_usr" \
		| sed "s/,/\n/g" \
		| sort -u \
		| sed ':a;N;$!ba;s/\n/,/g')

	sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" \
		$HESTIA/data/ips/$sip
	sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
		$HESTIA/data/ips/$sip
}

# Decrease ip value
decrease_ip_value() {
	sip=${1-ip}
	local user=${2-$user}
	web_key='U_WEB_DOMAINS'
	usr_key='U_SYS_USERS'

	current_web=$(grep "$web_key=" $HESTIA/data/ips/$sip | cut -f 2 -d \')
	current_usr=$(grep "$usr_key=" $HESTIA/data/ips/$sip | cut -f 2 -d \')

	if [ -z "$current_web" ]; then
		check_result $E_PARSING "Parsing error"
	fi

	if (($current_web <= 0)); then
		new_web=0
	else
		new_web=$((current_web - 1))
	fi
	check_ip=$(grep $sip $USER_DATA/web.conf | wc -l)
	if [[ $check_ip = 0 ]]; then
		new_usr=$(echo "$current_usr" \
			| sed "s/,/\n/g" \
			| sed "s/^$user$//g" \
			| sed "/^$/d" \
			| sort -u \
			| sed ':a;N;$!ba;s/\n/,/g')
	else
		new_usr="$current_usr"
	fi

	sed -i "s/$web_key='$current_web'/$web_key='$new_web'/g" \
		$HESTIA/data/ips/$sip
	sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
		$HESTIA/data/ips/$sip
}

# Get ip address value
get_ip_value() {
	key="$1"
	string=$(cat $HESTIA/data/ips/$ip)
	eval $string
	eval value="$key"
	echo "$value"
}

# Get real ip address
get_real_ip() {
	if [ -e "$HESTIA/data/ips/$1" ]; then
		echo "$1"
	else
		nat=$(grep -H "^NAT='$1'" $HESTIA/data/ips/* | head -n1)
		if [ -n "$nat" ]; then
			echo "$nat" | cut -f 1 -d : | cut -f 7 -d /
		fi
	fi
}

# Convert CIDR to netmask
convert_cidr() {
	# CIDR can be defined as /32 (with leading /) or as 32 (number without leading /)
	# please check the value range of cidr before converting!
	set ${1#/} # allow to use cidr format with leading /
	set -- $((5 - ($1 / 8))) 255 255 255 255 \
		$(((255 << (8 - ($1 % 8))) & 255)) 0 0 0
	if [[ $1 -gt 1 ]]; then
		shift $1
	else
		shift
	fi
	echo ${1-0}.${2-0}.${3-0}.${4-0}
}

# Convert netmask to CIDR
convert_netmask() {
	nbits=0
	IFS=.
	for dec in $1; do
		case $dec in
			255) let nbits+=8 ;;
			254) let nbits+=7 ;;
			252) let nbits+=6 ;;
			248) let nbits+=5 ;;
			240) let nbits+=4 ;;
			224) let nbits+=3 ;;
			192) let nbits+=2 ;;
			128) let nbits+=1 ;;
			0) ;;
		esac
	done
	echo "/$nbits"
}

# Calculate broadcast address
get_broadcast() {
	OLD_IFS=$IFS
	IFS=.
	typeset -a I=($1)
	typeset -a N=($2)
	IFS=$OLD_IFS

	echo "$((${I[0]} | (255 ^ ${N[0]}))).$((${I[1]} | (255 ^ ${N[1]}))).$((${I[2]} | (255 ^ ${N[2]}))).$((${I[3]} | (255 ^ ${N[3]})))"
}

# Get user ips
get_user_ips() {
	dedicated=$(grep -H "OWNER='$user'" $HESTIA/data/ips/*)
	dedicated=$(echo "$dedicated" | cut -f 1 -d : | sed 's=.*/==' | grep -E ${REGEX_IPV4})
	shared=$(grep -H -A1 "OWNER='$ROOT_USER'" $HESTIA/data/ips/* | grep shared)
	shared=$(echo "$shared" | cut -f 1 -d : | sed 's=.*/==' | cut -f 1 -d \- | grep -E ${REGEX_IPV4})
	for dedicated_ip in $dedicated; do
		shared=$(echo "$shared" | grep -v $dedicated_ip)
	done
	echo -e "$dedicated\n$shared" | sed "/^$/d"
}

# Get user ip
get_user_ip() {
	ip=$(get_user_ips | head -n1)
	if [ -z "$ip" ]; then
		check_result $E_NOTEXIST "no IP is available"
	fi
	local_ip=$ip
	nat=$(grep "^NAT" $HESTIA/data/ips/$ip | cut -f 2 -d \')
	if [ -n "$nat" ]; then
		ip=$nat
	fi
}

# Validate ip address
is_ip_valid() {
	local_ip="$1"
	if [ ! -e "$HESTIA/data/ips/$1" ]; then
		nat=$(grep -H "^NAT='$1'" $HESTIA/data/ips/*)
		if [ -z "$nat" ]; then
			check_result "$E_NOTEXIST" "IP $1 doesn't exist"
		else
			nat=$(echo "$nat" | cut -f1 -d: | cut -f7 -d/)
			local_ip=$nat
		fi
	fi
	if [ -n "$2" ]; then
		if [ -z "$nat" ]; then
			ip_data=$(cat $HESTIA/data/ips/$1)
		else
			ip_data=$(cat $HESTIA/data/ips/$nat)
		fi
		ip_owner=$(echo "$ip_data" | grep OWNER= | cut -f2 -d \')
		ip_status=$(echo "$ip_data" | grep STATUS= | cut -f2 -d \')
		if [ "$ip_owner" != "$user" ] && [ "$ip_status" = 'dedicated' ]; then
			check_result "$E_FORBIDEN" "$user user can't use IP $1"
		fi
		get_user_owner
		if [ "$ip_owner" != "$user" ] && [ "$ip_owner" != "$owner" ]; then
			check_result "$E_FORBIDEN" "$user user can't use IP $1"
		fi
	fi
}

# === IPV6 specific functions ===

# Get full interface name
get_ipv6_iface() {
	i=$(/sbin/ip addr | grep -w $interface \
		| awk '{print $NF}' | tail -n 1 | cut -f 2 -d :)
	if [ "$i" = "$interface" ]; then
		n=0
	else
		n=$((i + 1))
	fi
	echo "$interface:$n"
}

# Get user ip6s
get_user_ip6s() {
	dedicated=$(grep -H -A10 "OWNER='$user'" $HESTIA/data/ips/* | grep "VERSION='6'")
	dedicated=$(echo "$dedicated" | cut -f 1 -d '-' | sed 's=.*/==')
	shared=$(grep -H -A10 "OWNER='admin'" $HESTIA/data/ips/* | grep -A10 shared | grep "VERSION='6'")
	shared=$(echo "$shared" | cut -f 1 -d '-' | sed 's=.*/==' | cut -f 1 -d \-)
	for dedicated_ip in $dedicated; do
		shared=$(echo "$shared" | grep -v $dedicated_ip)
	done
	echo -e "$dedicated\n$shared" | sed "/^$/d"
}

# Get user ipv6
get_user_ipv6() {
	ipv6=$(get_user_ip6s | head -n1)
	local_ipv6="$ipv6"
}

# Validate ipv6 address
is_ipv6_valid() {
	local_ipv6="$1"
	if [ -z "$local_ipv6" ]; then
		check_result $E_NOTEXIST "IPV6 address is empty"
	fi
	if [ ! -e "$HESTIA/data/ips/$1" ]; then
		check_result $E_NOTEXIST "IPV6 $1 doesn't exist"
	fi
	if [ ! -z $2 ]; then
		ip_data=$(cat $HESTIA/data/ips/$1)
		ip_owner=$(echo "$ip_data" | grep OWNER= | cut -f2 -d \')
		ip_status=$(echo "$ip_data" | grep STATUS= | cut -f2 -d \')
		if [ "$ip_owner" != "$user" ] && [ "$ip_status" = 'dedicated' ]; then
			check_result $E_FORBIDEN "$user user can't use IPV6 $1"
		fi
		get_user_owner
		if [ "$ip_owner" != "$user" ] && [ "$ip_owner" != "$owner" ]; then
			check_result $E_FORBIDEN "$user user can't use IPV6 $1"
		fi
	fi
}
