#!/bin/bash

#===========================================================================#
#                                                                           #
# DevIT Control Panel - IP/Network Function Library                        #
#                                                                           #
#===========================================================================#

# Global definitions
REGEX_IPV4="^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}$"

# Check ip ownership
is_ip_owner() {
	owner=$(grep 'OWNER=' $DevIT/data/ips/$ip | cut -f 2 -d \')
	if [ "$owner" != "$user" ]; then
		check_result "$E_FORBIDEN" "$ip is not owned by $user"
	fi
}

# Check if ip address is free
is_ip_free() {
	if [ -e "$DevIT/data/ips/$ip" ]; then
		check_result "$E_EXISTS" "$ip is already exists"
	fi
}

# Check ip address specific value
is_ip_key_empty() {
	key="$1"
	string=$(cat $DevIT/data/ips/$ip)
	eval $string
	eval value="$key"
	if [ -n "$value" ] && [ "$value" != '0' ]; then
		key="$(echo $key | sed -e "s/\$U_//")"
		check_result "$E_EXISTS" "IP is in use / $key = $value"
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
	conf="$DevIT/data/ips/$ip"
	str=$(cat $conf)
	eval $str
	c_key=$(echo "${key//$/}")
	eval old="${key}"
	old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
	new=$(echo "$value" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
	sed -i "$str_number s/$c_key='${old//\*/\\*}'/$c_key='${new//\*/\\*}'/g" \
		$conf
}

# New method that is improved on a later date we need to check if we can improve it for other locations
update_ip_value_new() {
	key="$1"
	value="$2"
	conf="$DevIT/data/ips/$ip"
	check_ckey=$(grep "^$key='" $conf)
	if [ -z "$check_ckey" ]; then
		echo "$key='$value'" >> $conf
	else
		sed -i "s|^$key=.*|$key='$value'|g" $conf
	fi
}

# Get ip name
get_ip_alias() {
	ip_name=$(grep "NAME=" $DevIT/data/ips/$local_ip | cut -f 2 -d \')
	if [ -n "$ip_name" ]; then
		echo "${1//./-}.$ip_name"
	fi
}

# Increase ip value
increase_ip_value() {
	sip=${1-ip}
	USER=${2-$user}
	web_key='U_WEB_DOMAINS'
	usr_key='U_SYS_USERS'
	current_web=$(grep "$web_key=" $DevIT/data/ips/$sip | cut -f 2 -d \')
	current_usr=$(grep "$usr_key=" $DevIT/data/ips/$sip | cut -f 2 -d \')
	if [ -z "$current_web" ]; then
		echo "Error: Parsing error"
		log_event "$E_PARSING" "$ARGUMENTS"
		exit "$E_PARSING"
	fi
	new_web=$((current_web + 1))
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
		$DevIT/data/ips/$sip
	sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
		$DevIT/data/ips/$sip
}

# Decrease ip value
decrease_ip_value() {
	sip=${1-ip}
	local user=${2-$user}
	web_key='U_WEB_DOMAINS'
	usr_key='U_SYS_USERS'

	current_web=$(grep "$web_key=" $DevIT/data/ips/$sip | cut -f 2 -d \')
	current_usr=$(grep "$usr_key=" $DevIT/data/ips/$sip | cut -f 2 -d \')

	if [ -z "$current_web" ]; then
		check_result $E_PARSING "Parsing error"
	fi

	new_web=$((current_web - 1))
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
		$DevIT/data/ips/$sip
	sed -i "s/$usr_key='$current_usr'/$usr_key='$new_usr'/g" \
		$DevIT/data/ips/$sip
}

# Get ip address value
get_ip_value() {
	key="$1"
	string=$(cat $DevIT/data/ips/$ip)
	eval $string
	eval value="$key"
	echo "$value"
}

# Get real ip address
get_real_ip() {
	if [ -e "$DevIT/data/ips/$1" ]; then
		echo "$1"
	else
		nat=$(grep -H "^NAT='$1'" $DevIT/data/ips/* | head -n1)
		if [ -n "$nat" ]; then
			echo "$nat" | cut -f 1 -d : | cut -f 7 -d /
		fi
	fi
}

# Convert CIDR to netmask
convert_cidr() {
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
	echo "$nbits"
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
	dedicated=$(grep -H "OWNER='$user'" $DevIT/data/ips/*)
	dedicated=$(echo "$dedicated" | cut -f 1 -d : | sed 's=.*/==' | grep -E ${REGEX_IPV4})
	shared=$(grep -H -A1 "OWNER='$ROOT_USER'" $DevIT/data/ips/* | grep shared)
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
	nat=$(grep "^NAT" $DevIT/data/ips/$ip | cut -f 2 -d \')
	if [ -n "$nat" ]; then
		ip=$nat
	fi
}

# Validate ip address
is_ip_valid() {
	local_ip="$1"
	if [ ! -e "$DevIT/data/ips/$1" ]; then
		nat=$(grep -H "^NAT='$1'" $DevIT/data/ips/*)
		if [ -z "$nat" ]; then
			check_result "$E_NOTEXIST" "IP $1 doesn't exist"
		else
			nat=$(echo "$nat" | cut -f1 -d: | cut -f7 -d/)
			local_ip=$nat
		fi
	fi
	if [ -n "$2" ]; then
		if [ -z "$nat" ]; then
			ip_data=$(cat $DevIT/data/ips/$1)
		else
			ip_data=$(cat $DevIT/data/ips/$nat)
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
