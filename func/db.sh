#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - Domain Function Library                            #
#                                                                           #
#===========================================================================#

# Global
database_set_default_ports() {

	# Set default ports for MySQL, PostgreSQL, and Redis
	mysql_default="3306"
	pgsql_default="5432"
	redis_default="6379"

	# Handle missing values for both $PORT and $port
	# however don't override both at once or custom ports will be overridden.

	if [ -z "$PORT" ]; then
		if [ "$type" = 'mysql' ]; then
			PORT="$mysql_default"
		fi
		if [ "$type" = 'pgsql' ]; then
			PORT="$pgsql_default"
		fi
		if [ "$type" = 'redis' ]; then
			PORT="$redis_default"
		fi
	fi
	if [ -z "$port" ]; then
		if [ "$type" = 'mysql' ]; then
			port="$mysql_default"
		fi
		if [ "$type" = 'pgsql' ]; then
			port="$pgsql_default"
		fi
		if [ "$type" = 'redis' ]; then
			port="$redis_default"
		fi
	fi
}

database_get_default_port() {
	case "$1" in
		mysql) echo "3306" ;;
		pgsql) echo "5432" ;;
		redis) echo "6379" ;;
	esac
}

database_get_endpoint() {
	echo "$1:$2"
}

database_get_endpoint_id() {
	echo "$1_$2" | sed -e "s/[^A-Za-z0-9_.-]/_/g"
}

database_count_host_matches() {
	local search_type="$1"
	local search_host="$2"
	local search_port="$3"
	local default_port row row_host row_port
	local count=0

	default_port=$(database_get_default_port "$search_type")
	while IFS= read -r row; do
		[ -z "$row" ] && continue
		unset HOST PORT
		parse_object_kv_list "$row"
		row_host="$HOST"
		row_port="${PORT:-$default_port}"
		if [ "$row_host" = "$search_host" ]; then
			if [ -z "$search_port" ] || [ "$row_port" = "$search_port" ]; then
				count=$((count + 1))
			fi
		fi
	done < "$HESTIA/conf/$search_type.conf"

	echo "$count"
}

database_get_host_values() {
	local search_type="$1"
	local search_host="$2"
	local search_port="$3"
	local default_port row row_host row_port line
	local count=0

	default_port=$(database_get_default_port "$search_type")
	line=0
	DBHOST_LINE=""
	DBHOST_ROW=""
	DBHOST_ENDPOINT=""

	while IFS= read -r row; do
		line=$((line + 1))
		[ -z "$row" ] && continue
		unset HOST PORT
		parse_object_kv_list "$row"
		row_host="$HOST"
		row_port="${PORT:-$default_port}"
		if [ "$row_host" = "$search_host" ]; then
			if [ -z "$search_port" ] || [ "$row_port" = "$search_port" ]; then
				count=$((count + 1))
				DBHOST_LINE="$line"
				DBHOST_ROW="$row"
			fi
		fi
	done < "$HESTIA/conf/$search_type.conf"

	if [ "$count" -eq 0 ]; then
		check_result "$E_NOTEXIST" "$search_type host $search_host doesn't exist"
	fi
	if [ "$count" -gt 1 ]; then
		check_result "$E_INVALID" "multiple database hosts match $search_host; specify port"
	fi

	parse_object_kv_list "$DBHOST_ROW"
	if [ -z "$PORT" ]; then
		PORT="$default_port"
	fi
	DBHOST_ENDPOINT=$(database_get_endpoint "$HOST" "$PORT")
}

database_update_host_value() {
	local search_type="$1"
	local search_host="$2"
	local search_port="$3"
	local key="${4#\$}"
	local new="$5"
	local old

	database_get_host_values "$search_type" "$search_host" "$search_port"
	old="${!key}"
	old=$(echo "$old" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
	new=$(echo "$new" | sed -e 's/\\/\\\\/g' -e 's/&/\\&/g' -e 's/\//\\\//g')
	sed -i "$DBHOST_LINE s/${key}='${old//\*/\\*}'/${key}='${new//\*/\\*}'/g" \
		"$HESTIA/conf/$search_type.conf"
}

# MySQL
mysql_connect() {
	unset PORT
	database_get_host_values "mysql" "$1" "$2"
	if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ]; then
		echo "Error: mysql config parsing failed"
		log_event "$E_PARSING" "$ARGUMENTS"
		exit $E_PARSING
	fi
	mycnf="$HESTIA/conf/.mysql.$(database_get_endpoint_id "$HOST" "$PORT")"
	if [ ! -e "$mycnf" ]; then
		echo "[client]" > $mycnf
		echo "host='$HOST'" >> $mycnf
		echo "user='$USER'" >> $mycnf
		echo "password='$PASSWORD'" >> $mycnf
		echo "port='$PORT'" >> $mycnf
		chmod 600 $mycnf
	else
		mypw=$(grep password $mycnf | cut -f 2 -d \')
		if [ "$mypw" != "$PASSWORD" ]; then
			echo "[client]" > $mycnf
			echo "host='$HOST'" >> $mycnf
			echo "user='$USER'" >> $mycnf
			echo "password='$PASSWORD'" >> $mycnf
			echo "port='$PORT'" >> $mycnf
			chmod 660 $mycnf
		fi
	fi
	mysql_out=$(mktemp)
	if [ -f '/usr/bin/mariadb' ]; then
		mariadb --defaults-file=$mycnf -e 'SELECT VERSION()' > $mysql_out 2>&1
	else
		mysql --defaults-file=$mycnf -e 'SELECT VERSION()' > $mysql_out 2>&1
	fi
	if [ '0' -ne "$?" ]; then
		if [ "$notify" != 'no' ]; then
			email=$(grep CONTACT "$HESTIA/data/users/$ROOT_USER/user.conf" | cut -f 2 -d \')
			subj="MySQL connection error on $(hostname)"
			echo -e "Can't connect to MySQL $HOST:$PORT\n$(cat $mysql_out)" \
				| $SENDMAIL -s "$subj" $email
		fi
		rm -f $mysql_out
		echo "Error: Connection to $HOST failed"
		log_event "$E_CONNECT" "$ARGUMENTS"
		exit $E_CONNECT
	fi
	mysql_ver=$(cat $mysql_out | tail -n1 | cut -f 1 -d -)
	mysql_fork="mysql"
	check_mysql_fork=$(grep "MariaDB" $mysql_out)
	if [ "$check_mysql_fork" ]; then
		mysql_fork="mariadb"
	fi
	rm -f $mysql_out
}

mysql_query() {
	sql_tmp=$(mktemp)
	echo "$1" > $sql_tmp
	if [ -f '/usr/bin/mariadb' ]; then
		mariadb --defaults-file=$mycnf < "$sql_tmp" 2> /dev/null
		return_code=$?
	else
		mysql --defaults-file=$mycnf < "$sql_tmp" 2> /dev/null
		return_code=$?
	fi
	rm -f "$sql_tmp"
	return $return_code
}

mysql_dump() {
	err="/tmp/e.mysql"
	mysqldmp="mysqldump"
	if [ -f '/usr/bin/mariadb-dump' ]; then
		mysqldmp="/usr/bin/mariadb-dump"
	fi
	$mysqldmp --defaults-file=$mycnf --single-transaction --routines -r $1 $2 2> $err
	if [ '0' -ne "$?" ]; then
		$mysqldmp --defaults-extra-file=$mycnf --single-transaction --routines -r $1 $2 2> $err
		if [ '0' -ne "$?" ]; then
			rm -rf $tmpdir
			if [ "$notify" != 'no' ]; then
				email=$(grep CONTACT "$HESTIA/data/users/$ROOT_USER/user.conf" | cut -f 2 -d \')
				subj="MySQL error on $(hostname)"
				echo -e "Can't dump database $database\n$(cat $err)" \
					| $SENDMAIL -s "$subj" $email
			fi
			echo "Error: dump $database failed"
			log_event "$E_DB" "$ARGUMENTS"
			exit "$E_DB"
		fi
	fi
}

# PostgreSQL
psql_connect() {
	unset PORT
	database_get_host_values "pgsql" "$1" "$2"
	export PGPASSWORD="$PASSWORD"
	if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ] || [ -z $TPL ]; then
		echo "Error: postgresql config parsing failed"
		log_event "$E_PARSING" "$ARGUMENTS"
		exit $E_PARSING
	fi

	psql -h $HOST -U $USER -p $PORT -c "SELECT VERSION()" > /dev/null 2> /tmp/e.psql
	if [ '0' -ne "$?" ]; then
		if [ "$notify" != 'no' ]; then
			email=$(grep CONTACT "$HESTIA/data/users/$ROOT_USER/user.conf" | cut -f 2 -d \')
			subj="PostgreSQL connection error on $(hostname)"
			echo -e "Can't connect to PostgreSQL $HOST:$PORT\n$(cat /tmp/e.psql)" \
				| $SENDMAIL -s "$subj" $email
		fi
		echo "Error: Connection to $HOST failed"
		log_event "$E_CONNECT" "$ARGUMENTS"
		exit "$E_CONNECT"
	fi
}

psql_query() {
	sql_tmp=$(mktemp)
	echo "$1" > $sql_tmp
	psql -h $HOST -U $USER -p $PORT -f "$sql_tmp" 2> /dev/null
	rm -f $sql_tmp
}

psql_dump() {
	pg_dump -h $HOST -U $USER -p $PORT -c --inserts -O -x -f $1 $2 2> /tmp/e.psql
	if [ '0' -ne "$?" ]; then
		rm -rf $tmpdir
		if [ "$notify" != 'no' ]; then
			email=$(grep CONTACT "$HESTIA/data/users/$ROOT_USER/user.conf" | cut -f 2 -d \')
			subj="PostgreSQL error on $(hostname)"
			echo -e "Can't dump database $database\n$(cat /tmp/e.psql)" \
				| $SENDMAIL -s "$subj" $email
		fi
		echo "Error: dump $database failed"
		log_event "$E_DB" "$ARGUMENTS"
		exit "$E_DB"
	fi
}

# Redis
redis_connect() {
	if ! command -v redis-cli > /dev/null 2>&1; then
		echo "Error: redis-cli is not installed"
		log_event "$E_NOTEXIST" "$ARGUMENTS"
		exit "$E_NOTEXIST"
	fi
	unset PORT
	database_get_host_values "redis" "$1" "$2"
	if [ -z "$HOST" ] || [ -z "$USER" ] || [ -z "$PASSWORD" ]; then
		echo "Error: redis config parsing failed"
		log_event "$E_PARSING" "$ARGUMENTS"
		exit $E_PARSING
	fi

	redis_auth_args=(-h "$HOST" -p "$PORT" --user "$USER" -a "$PASSWORD" --no-auth-warning)
	redis_out=$(mktemp)
	redis-cli "${redis_auth_args[@]}" PING > "$redis_out" 2>&1
	if [ '0' -ne "$?" ]; then
		if [ "$notify" != 'no' ]; then
			email=$(grep CONTACT "$HESTIA/data/users/$ROOT_USER/user.conf" | cut -f 2 -d \')
			subj="Redis connection error on $(hostname)"
			echo -e "Can't connect to Redis $HOST:$PORT\n$(cat "$redis_out")" \
				| $SENDMAIL -s "$subj" $email
		fi
		rm -f "$redis_out"
		echo "Error: Connection to $HOST failed"
		log_event "$E_CONNECT" "$ARGUMENTS"
		exit "$E_CONNECT"
	fi
	rm -f "$redis_out"
}

redis_query() {
	redis-cli "${redis_auth_args[@]}" "$@"
}

redis_save_acl() {
	acl_out=$(mktemp)
	redis_query ACL SAVE > "$acl_out" 2>&1
	if [ "$?" -ne 0 ]; then
		echo "Error: Redis ACL changes could not be saved"
		cat "$acl_out"
		rm -f "$acl_out"
		log_event "$E_DB" "$ARGUMENTS"
		exit "$E_DB"
	fi
	rm -f "$acl_out"
}

redis_get_prefix() {
	if [ -n "$PREFIX" ]; then
		redis_prefix="$PREFIX"
	else
		redis_prefix="hestia:${user}:${database}:"
	fi
}

redis_get_acl_hash() {
	md5=$(redis_query ACL LIST | grep "^user $DBUSER " || true)
	if [ -z "$md5" ]; then
		md5=$(redis_query ACL LIST | grep "^user $dbuser " || true)
	fi
}

redis_apply_acl_user() {
	local acl_user="$1"
	local acl_pass="$2"
	local acl_prefix="$3"
	local acl_state="${4-on}"

	redis_query ACL SETUSER "$acl_user" reset "$acl_state" ">$acl_pass" \
		"~$acl_prefix*" "&$acl_prefix*" \
		+@read +@write +@keyspace +@string +@list +@set +@sortedset \
		+@hash +@stream +@pubsub +@transaction +@connection \
		-@admin -@dangerous -ACL -CONFIG -FLUSHALL -FLUSHDB -KEYS -SCAN -RANDOMKEY > /dev/null
}

# Get database host
get_next_dbhost() {
	if [ -z "$host" ] || [ "$host" == 'default' ]; then
		IFS=$'\n'
		host='EMPTY_DB_HOST'
		port=''
		config="$HESTIA/conf/$type.conf"
		host_str=$(grep "SUSPENDED='no'" $config)
		check_row=$(echo "$host_str" | wc -l)

		if [ 0 -lt "$check_row" ]; then
			if [ 1 -eq "$check_row" ]; then
				for db in $host_str; do
					unset HOST PORT
					parse_object_kv_list "$db"
					if [ -z "$PORT" ]; then PORT=$(database_get_default_port "$type"); fi
					if [ "$MAX_DB" -gt "$U_DB_BASES" ]; then
						host=$HOST
						port=$PORT
					fi
				done
			else
				old_weight='100'
				for db in $host_str; do
					unset HOST PORT
					parse_object_kv_list "$db"
					if [ -z "$PORT" ]; then PORT=$(database_get_default_port "$type"); fi
					if [ "$MAX_DB" -le "$U_DB_BASES" ]; then
						continue
					fi
					weight=$((U_DB_BASES * 100 / MAX_DB))
					if [ "$old_weight" -gt "$weight" ]; then
						host="$HOST"
						port="$PORT"
						old_weight="$weight"
					fi
				done
			fi
		fi
	else
		database_get_host_values "$type" "$host" "$port"
		host="$HOST"
		port="$PORT"
	fi
}

# Database charset validation
is_charset_valid() {
	database_get_host_values "$type" "$host" "$port"

	if [ -z "$(echo $CHARSETS | grep -wi $charset)" ]; then
		echo "Error: charset $charset not exist"
		log_event "$E_NOTEXIST" "$ARGUMENTS"
		exit $E_NOTEXIST
	fi
}

# Increase database host value
increase_dbhost_values() {
	database_get_host_values "$type" "$host" "$port"

	if [ -z "$U_SYS_USERS" ]; then
		new_users="$user"
	else
		new_users="$U_SYS_USERS"
		if [ -z "$(echo $U_SYS_USERS | sed "s/,/\n/g" | grep -w $user)" ]; then
			new_users="$U_SYS_USERS,$user"
		fi
	fi

	database_update_host_value "$type" "$host" "$port" '$U_DB_BASES' "$((U_DB_BASES + 1))"
	database_update_host_value "$type" "$host" "$port" '$U_SYS_USERS' "$new_users"
}

# Decrease database host value
decrease_dbhost_values() {
	database_get_host_values "$TYPE" "$HOST" "$PORT"

	U_SYS_USERS=$(echo "$U_SYS_USERS" \
		| sed "s/,/\n/g" \
		| sed "s/^$user$//g" \
		| sed "/^$/d" \
		| sed ':a;N;$!ba;s/\n/,/g')

	database_update_host_value "$TYPE" "$HOST" "$PORT" '$U_DB_BASES' "$((U_DB_BASES - 1))"
	database_update_host_value "$TYPE" "$HOST" "$PORT" '$U_SYS_USERS' "$U_SYS_USERS"
}

# Create MySQL database
add_mysql_database() {
	mysql_connect "$host" "$port"

	mysql_ver_sub=$(echo $mysql_ver | cut -d '.' -f1)
	mysql_ver_sub_sub=$(echo $mysql_ver | cut -d '.' -f2)

	query="CREATE DATABASE \`$database\` CHARACTER SET $charset"
	mysql_query "$query"
	check_result $? "Unable to create database $database"

	if [ "$mysql_fork" = "mysql" ] && [ "$mysql_ver_sub" -ge 8 ]; then
		query="CREATE USER \`$dbuser\`@\`%\`
            IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null

		query="CREATE USER \`$dbuser\`@localhost
            IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@\`%\`"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@localhost"
		mysql_query "$query" > /dev/null
	else
		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@\`%\`
            IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@localhost
            IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null
	fi

	if [ "$mysql_fork" = "mysql" ]; then
		# mysql
		if [ "$mysql_ver_sub" -ge 8 ] || { [ "$mysql_ver_sub" -eq 5 ] && [ "$mysql_ver_sub_sub" -ge 7 ]; }; then
			if [ "$mysql_ver_sub" -ge 8 ]; then
				# mysql >= 8

				# This query will be proceeding with the usage of Print identified with as hex feature
				md5=$(mysql_query "SET print_identified_with_as_hex=ON; SHOW CREATE USER \`$dbuser\`" 2> /dev/null)

				# echo $md5
				if [[ "$md5" =~ 0x([^ ]+) ]]; then
					md5=$(echo "$md5" | grep password | grep -E -o '0x([^ ]+)')
				else
					md5=$(echo "$md5" | grep password | cut -f4 -d \')
				fi
				# echo $md5
			else
				# mysql < 8
				md5=$(mysql_query "SHOW CREATE USER \`$dbuser\`" 2> /dev/null)
				md5=$(echo "$md5" | grep password | cut -f8 -d \')
			fi
		else
			# mysql < 5.7
			md5=$(mysql_query "SHOW GRANTS FOR \`$dbuser\`" 2> /dev/null)
			md5=$(echo "$md5" | grep PASSW | tr ' ' '\n' | tail -n1 | cut -f 2 -d \')
		fi
	else
		# mariadb
		md5=$(mysql_query "SHOW GRANTS FOR \`$dbuser\`" 2> /dev/null)
		md5=$(echo "$md5" | grep PASSW | tr ' ' '\n' | tail -n1 | cut -f 2 -d \')
	fi
}

# Create PostgreSQL database
add_pgsql_database() {
	psql_connect "$host" "$port"

	query="CREATE ROLE $dbuser WITH LOGIN PASSWORD '$dbpass'"
	psql_query "$query" > /dev/null

	query="CREATE DATABASE $database OWNER $dbuser"
	if [ "$TPL" = 'template0' ]; then
		query="$query ENCODING '$charset' TEMPLATE $TPL"
	else
		query="$query TEMPLATE $TPL"
	fi
	psql_query "$query" > /dev/null

	query="GRANT ALL PRIVILEGES ON DATABASE $database TO $dbuser"
	psql_query "$query" > /dev/null

	query="GRANT CONNECT ON DATABASE template1 to $dbuser"
	psql_query "$query" > /dev/null

	query="SELECT rolpassword FROM pg_authid WHERE rolname='$dbuser'"
	md5=$(psql_query "$query" | grep md5 | cut -f 2 -d \ )
}

# Create Redis ACL database
add_redis_database() {
	redis_connect "$host" "$port"
	redis_prefix="hestia:${user}:${database}:"
	redis_apply_acl_user "$dbuser" "$dbpass" "$redis_prefix" "on"
	redis_save_acl
	DBUSER="$dbuser"
	redis_get_acl_hash
	PREFIX="$redis_prefix"
	DB_INDEX="0"
}

add_mysql_database_temp_user() {
	mysql_connect "$HOST" "$PORT"

	mysql_ver_sub=$(echo $mysql_ver | cut -d '.' -f1)
	mysql_ver_sub_sub=$(echo $mysql_ver | cut -d '.' -f2)

	if [ "$mysql_fork" = "mysql" ] && [ "$mysql_ver_sub" -ge 8 ]; then
		query="CREATE USER \`$dbuser\`@\`%\`
			IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null

		query="CREATE USER \`$dbuser\`@localhost
			IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@\`%\`"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@localhost"
		mysql_query "$query" > /dev/null
	else
		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@\`%\`
			IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@localhost
			IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null
	fi
}

delete_mysql_database_temp_user() {
	mysql_connect "$HOST" "$PORT"
	query="REVOKE ALL ON \`$database\`.* FROM \`$dbuser\`@\`%\`"
	mysql_query "$query" > /dev/null
	query="REVOKE ALL ON \`$database\`.* FROM \`$dbuser\`@localhost"
	mysql_query "$query" > /dev/null
	query="DROP USER '$dbuser'@'%'"
	mysql_query "$query" > /dev/null
	query="DROP USER '$dbuser'@'localhost'"
	mysql_query "$query" > /dev/null
}

add_redis_database_temp_user() {
	redis_connect "$HOST" "$PORT"
	redis_get_prefix
	redis_apply_acl_user "$dbuser" "$dbpass" "$redis_prefix" "on"
	redis_query ACL SETUSER "$dbuser" +SCAN > /dev/null
}

delete_redis_database_temp_user() {
	redis_connect "$HOST" "$PORT"
	redis_query ACL DELUSER "$dbuser" > /dev/null
	redis_save_acl
}

# Check if database host do not exist in config
is_dbhost_new() {
	if [ -e "$HESTIA/conf/$type.conf" ]; then
		check_host=$(database_count_host_matches "$type" "$host" "$port")
		if [ "$check_host" -gt 0 ]; then
			echo "Error: db host exist"
			log_event "$E_EXISTS" "$ARGUMENTS"
			exit $E_EXISTS
		fi
	fi
}

# Get database values
get_database_values() {
	local db_row

	unset PORT
	db_row=$(grep "DB='$database'" "$USER_DATA/db.conf")
	parse_object_kv_list "$db_row"
	if [ -z "$PORT" ]; then
		PORT=$(database_get_default_port "$TYPE")
	fi
}

# Change MySQL database password
change_mysql_password() {
	mysql_connect "$HOST" "$PORT"

	mysql_ver_sub=$(echo $mysql_ver | cut -d '.' -f1)
	mysql_ver_sub_sub=$(echo $mysql_ver | cut -d '.' -f2)

	if [ "$mysql_fork" = "mysql" ]; then
		# mysql
		if [ "$mysql_ver_sub" -ge 8 ]; then
			# mysql >= 8
			query="SET PASSWORD FOR \`$DBUSER\`@\`%\` = '$dbpass'"
			mysql_query "$query" > /dev/null
			query="SET PASSWORD FOR \`$DBUSER\`@localhost = '$dbpass'"
			mysql_query "$query" > /dev/null
		else
			# mysql < 8
			query="GRANT ALL ON \`$database\`.* TO \`$DBUSER\`@\`%\`
                  IDENTIFIED BY '$dbpass'"
			mysql_query "$query" > /dev/null

			query="GRANT ALL ON \`$database\`.* TO \`$DBUSER\`@localhost
                  IDENTIFIED BY '$dbpass'"
			mysql_query "$query" > /dev/null
		fi
	else
		# mariadb
		query="GRANT ALL ON \`$database\`.* TO \`$DBUSER\`@\`%\`
              IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$database\`.* TO \`$DBUSER\`@localhost
              IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null
	fi

	if [ "$mysql_fork" = "mysql" ]; then
		# mysql
		if [ "$mysql_ver_sub" -ge 8 ] || { [ "$mysql_ver_sub" -eq 5 ] && [ "$mysql_ver_sub_sub" -ge 7 ]; }; then
			if [ "$mysql_ver_sub" -ge 8 ]; then
				# mysql >= 8

				# This query will be proceeding with the usage of Print identified with as hex feature
				md5=$(mysql_query "SET print_identified_with_as_hex=ON; SHOW CREATE USER \`$DBUSER\`" 2> /dev/null)

				# echo $md5
				if [[ "$md5" =~ 0x([^ ]+) ]]; then
					md5=$(echo "$md5" | grep password | grep -E -o '0x([^ ]+)')
				else
					md5=$(echo "$md5" | grep password | cut -f4 -d \')
				fi
				# echo $md5
			else
				# mysql < 8
				md5=$(mysql_query "SHOW CREATE USER \`$DBUSER\`" 2> /dev/null)
				md5=$(echo "$md5" | grep password | cut -f8 -d \')
			fi
		else
			# mysql < 5.7
			md5=$(mysql_query "SHOW GRANTS FOR \`$DBUSER\`" 2> /dev/null)
			md5=$(echo "$md5" | grep PASSW | tr ' ' '\n' | tail -n1 | cut -f 2 -d \')
		fi
	else
		# mariadb
		md5=$(mysql_query "SHOW GRANTS FOR \`$DBUSER\`" 2> /dev/null)
		md5=$(echo "$md5" | grep PASSW | tr ' ' '\n' | tail -n1 | cut -f 2 -d \')
	fi
}

# Change PostgreSQL database password
change_pgsql_password() {
	psql_connect "$HOST" "$PORT"
	query="ALTER ROLE $DBUSER WITH LOGIN PASSWORD '$dbpass'"
	psql_query "$query" > /dev/null

	query="SELECT rolpassword FROM pg_authid WHERE rolname='$DBUSER'"
	md5=$(psql_query "$query" | grep md5 | cut -f 2 -d \ )
}

# Change Redis ACL database password
change_redis_password() {
	redis_connect "$HOST" "$PORT"
	redis_get_prefix
	redis_apply_acl_user "$DBUSER" "$dbpass" "$redis_prefix" "on"
	redis_save_acl
	redis_get_acl_hash
}

# Delete MySQL database
delete_mysql_database() {
	mysql_connect "$HOST" "$PORT"

	query="DROP DATABASE \`$database\`"
	mysql_query "$query"

	query="REVOKE ALL ON \`$database\`.* FROM \`$DBUSER\`@\`%\`"
	mysql_query "$query" > /dev/null

	query="REVOKE ALL ON \`$database\`.* FROM \`$DBUSER\`@localhost"
	mysql_query "$query" > /dev/null

	if [ "$(grep "DBUSER='$DBUSER'" $USER_DATA/db.conf | wc -l)" -lt 2 ]; then
		query="DROP USER '$DBUSER'@'%'"
		mysql_query "$query" > /dev/null

		query="DROP USER '$DBUSER'@'localhost'"
		mysql_query "$query" > /dev/null
	fi
}

# Delete PostgreSQL database
delete_pgsql_database() {
	psql_connect "$HOST" "$PORT"

	query="REVOKE ALL PRIVILEGES ON DATABASE $database FROM $DBUSER"
	psql_query "$query" > /dev/null

	query="DROP DATABASE $database"
	psql_query "$query" > /dev/null

	if [ "$(grep "DBUSER='$DBUSER'" $USER_DATA/db.conf | wc -l)" -lt 2 ]; then
		query="REVOKE CONNECT ON DATABASE template1 FROM $DBUSER"
		psql_query "$query" > /dev/null
		query="DROP ROLE $DBUSER"
		psql_query "$query" > /dev/null
	fi
}

# Delete Redis ACL database
delete_redis_database() {
	redis_connect "$HOST" "$PORT"
	redis_get_prefix
	while IFS= read -r key; do
		[ -z "$key" ] && continue
		redis_query UNLINK "$key" > /dev/null
	done < <(redis_query --scan --pattern "$redis_prefix*")
	redis_query ACL DELUSER "$DBUSER" > /dev/null
	redis_save_acl
}

# Dump MySQL database
dump_mysql_database() {
	mysql_connect "$HOST" "$PORT"

	mysql_dump $dump $database

	query="SHOW GRANTS FOR '$DBUSER'@'localhost'"
	mysql_query "$query" | grep -v "Grants for" > $grants

	query="SHOW GRANTS FOR '$DBUSER'@'%'"
	mysql_query "$query" | grep -v "Grants for" > $grants
}

# Dump PostgreSQL database
dump_pgsql_database() {
	psql_connect "$HOST" "$PORT"

	psql_dump $dump $database

	query="SELECT rolpassword FROM pg_authid WHERE rolname='$DBUSER'"
	md5=$(psql_query "$query" | head -n1 | cut -f 2 -d \ )
	pw_str="UPDATE pg_authid SET rolpassword='$md5' WHERE rolname='$DBUSER'"
	gr_str="GRANT ALL PRIVILEGES ON DATABASE $database to '$DBUSER'"
	echo -e "$pw_str\n$gr_str" >> $grants
}

# Dump Redis prefixed keys as RESTORE commands
dump_redis_database() {
	redis_connect "$HOST" "$PORT"
	redis_get_prefix
	: > "$dump"
	while IFS= read -r key; do
		[ -z "$key" ] && continue
		ttl=$(redis_query PTTL "$key")
		if [ "$ttl" -lt 0 ]; then
			ttl=0
		fi
		payload=$(redis_query --raw DUMP "$key" | perl -0pe 's/\n\z//' | base64 -w 0)
		echo "RESTORE_BASE64 '$key' '$ttl' '$payload'" >> "$dump"
	done < <(redis_query --scan --pattern "$redis_prefix*")
	redis_get_acl_hash
	echo "$md5" > "$grants"
}

# Check if database server is in use
is_dbhost_free() {
	database_get_host_values "$type" "$host" "$port"
	if [ 0 -ne "$U_DB_BASES" ]; then
		echo "Error: host $HOST is used"
		log_event "$E_INUSE" "$ARGUMENTS"
		exit $E_INUSE
	fi
}

# Suspend MySQL database
suspend_mysql_database() {
	mysql_connect "$HOST" "$PORT"
	query="REVOKE ALL ON \`$database\`.* FROM \`$DBUSER\`@\`%\`"
	mysql_query "$query" > /dev/null
	query="REVOKE ALL ON \`$database\`.* FROM \`$DBUSER\`@localhost"
	mysql_query "$query" > /dev/null
}

# Suspend PostgreSQL database
suspend_pgsql_database() {
	psql_connect "$HOST" "$PORT"
	query="REVOKE ALL PRIVILEGES ON $database FROM $DBUSER"
	psql_query "$query" > /dev/null
}

# Suspend Redis ACL database
suspend_redis_database() {
	redis_connect "$HOST" "$PORT"
	redis_query ACL SETUSER "$DBUSER" off > /dev/null
	redis_save_acl
}

# Unsuspend MySQL database
unsuspend_mysql_database() {
	mysql_connect "$HOST" "$PORT"
	query="GRANT ALL ON \`$database\`.* TO \`$DBUSER\`@\`%\`"
	mysql_query "$query" > /dev/null
	query="GRANT ALL ON \`$database\`.* TO \`$DBUSER\`@localhost"
	mysql_query "$query" > /dev/null
}

# Unsuspend PostgreSQL database
unsuspend_pgsql_database() {
	psql_connect "$HOST" "$PORT"
	query="GRANT ALL PRIVILEGES ON DATABASE $database TO $DBUSER"
	psql_query "$query" > /dev/null
}

# Unsuspend Redis ACL database
unsuspend_redis_database() {
	redis_connect "$HOST" "$PORT"
	redis_query ACL SETUSER "$DBUSER" on > /dev/null
	redis_save_acl
}

# Get MySQL disk usage
get_mysql_disk_usage() {
	mysql_connect "$HOST" "$PORT"
	query="SELECT SUM( data_length + index_length ) / 1024 / 1024 'Size'
        FROM information_schema.TABLES WHERE table_schema='$database'"
	usage=$(mysql_query "$query" | tail -n1)
	if [ "$usage" == '' ] || [ "$usage" == 'NULL' ] || [ "${usage:0:1}" -eq '0' ]; then
		usage=1
	fi
	export LC_ALL=C
	usage=$(printf "%0.f\n" $usage)
}

# Get PostgreSQL disk usage
get_pgsql_disk_usage() {
	psql_connect "$HOST" "$PORT"

	query="SELECT pg_database_size('$database');"
	usage=$(psql_query "$query")
	usage=$(echo "$usage" | grep -v "-" | grep -v 'row' | sed "/^$/d")
	usage=$(echo "$usage" | grep -v "pg_database_size" | awk '{print $1}')
	if [ -z "$usage" ]; then
		usage=0
	fi
	usage=$(($usage / 1048576))
	if [ "$usage" -eq '0' ]; then
		usage=1
	fi
}

# Get Redis prefixed key memory usage
get_redis_disk_usage() {
	redis_connect "$HOST" "$PORT"
	redis_get_prefix
	usage=0
	while IFS= read -r key; do
		[ -z "$key" ] && continue
		key_usage=$(redis_query MEMORY USAGE "$key" 2> /dev/null || echo 0)
		usage=$((usage + key_usage))
	done < <(redis_query --scan --pattern "$redis_prefix*")
	usage=$((usage / 1048576))
	if [ "$usage" -eq '0' ]; then
		usage=1
	fi
}

# Delete MySQL user
delete_mysql_user() {
	mysql_connect "$HOST" "$PORT"

	query="REVOKE ALL ON \`$database\`.* FROM \`$old_dbuser\`@\`%\`"
	mysql_query "$query" > /dev/null

	query="REVOKE ALL ON \`$database\`.* FROM \`$old_dbuser\`@localhost"
	mysql_query "$query" > /dev/null

	query="DROP USER '$old_dbuser'@'%'"
	mysql_query "$query" > /dev/null

	query="DROP USER '$old_dbuser'@'localhost'"
	mysql_query "$query" > /dev/null
}

# Delete PostgreSQL user
delete_pgsql_user() {
	psql_connect "$HOST" "$PORT"

	query="REVOKE ALL PRIVILEGES ON DATABASE $database FROM $old_dbuser"
	psql_query "$query" > /dev/null

	query="REVOKE CONNECT ON DATABASE template1 FROM $old_dbuser"
	psql_query "$query" > /dev/null

	query="DROP ROLE $old_dbuser"
	psql_query "$query" > /dev/null
}
