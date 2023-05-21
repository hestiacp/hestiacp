#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - Domain Function Library                            #
#                                                                           #
#===========================================================================#

# Global
database_set_default_ports() {

	# Set default ports for MySQL and PostgreSQL
	mysql_default="3306"
	pgsql_default="5432"

	# Handle missing values for both $PORT and $port
	# however don't override both at once or custom ports will be overridden.

	if [ -z "$PORT" ]; then
		if [ "$type" = 'mysql' ]; then
			PORT="$mysql_default"
		fi
		if [ "$type" = 'pgsql' ]; then
			PORT="$pgsql_default"
		fi
	fi
	if [ -z "$port" ]; then
		if [ "$type" = 'mysql' ]; then
			port="$mysql_default"
		fi
		if [ "$type" = 'pgsql' ]; then
			port="$pgsql_default"
		fi
	fi
}

# MySQL
mysql_connect() {
	unset PORT
	host_str=$(grep "HOST='$1'" $HESTIA/conf/mysql.conf)
	parse_object_kv_list "$host_str"
	if [ -z $PORT ]; then PORT=3306; fi
	if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ]; then
		echo "Error: mysql config parsing failed"
		log_event "$E_PARSING" "$ARGUMENTS"
		exit $E_PARSING
	fi
	mycnf="$HESTIA/conf/.mysql.$HOST"
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
			email=$(grep CONTACT $HESTIA/data/users/admin/user.conf | cut -f 2 -d \')
			subj="MySQL connection error on $(hostname)"
			echo -e "Can't connect to MySQL $HOST\n$(cat $mysql_out)" \
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
				email=$(grep CONTACT $HESTIA/data/users/admin/user.conf | cut -f 2 -d \')
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
	host_str=$(grep "HOST='$1'" $HESTIA/conf/pgsql.conf)
	parse_object_kv_list "$host_str"
	export PGPASSWORD="$PASSWORD"
	if [ -z $PORT ]; then PORT=5432; fi
	if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ] || [ -z $TPL ]; then
		echo "Error: postgresql config parsing failed"
		log_event "$E_PARSING" "$ARGUMENTS"
		exit $E_PARSING
	fi

	psql -h $HOST -U $USER -p $PORT -c "SELECT VERSION()" > /dev/null 2> /tmp/e.psql
	if [ '0' -ne "$?" ]; then
		if [ "$notify" != 'no' ]; then
			email=$(grep CONTACT $HESTIA/data/users/admin/user.conf | cut -f 2 -d \')
			subj="PostgreSQL connection error on $(hostname)"
			echo -e "Can't connect to PostgreSQL $HOST\n$(cat /tmp/e.psql)" \
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
	psql -h $HOST -U $USER -f "$sql_tmp" 2> /dev/null
	rm -f $sql_tmp
}

psql_dump() {
	pg_dump -h $HOST -U $USER -c --inserts -O -x -f $1 $2 2> /tmp/e.psql
	if [ '0' -ne "$?" ]; then
		rm -rf $tmpdir
		if [ "$notify" != 'no' ]; then
			email=$(grep CONTACT $HESTIA/data/users/admin/user.conf | cut -f 2 -d \')
			subj="PostgreSQL error on $(hostname)"
			echo -e "Can't dump database $database\n$(cat /tmp/e.psql)" \
				| $SENDMAIL -s "$subj" $email
		fi
		echo "Error: dump $database failed"
		log_event "$E_DB" "$ARGUMENTS"
		exit "$E_DB"
	fi
}

# Get database host
get_next_dbhost() {
	if [ -z "$host" ] || [ "$host" == 'default' ]; then
		IFS=$'\n'
		host='EMPTY_DB_HOST'
		config="$HESTIA/conf/$type.conf"
		host_str=$(grep "SUSPENDED='no'" $config)
		check_row=$(echo "$host_str" | wc -l)

		if [ 0 -lt "$check_row" ]; then
			if [ 1 -eq "$check_row" ]; then
				for db in $host_str; do
					parse_object_kv_list "$db"
					if [ "$MAX_DB" -gt "$U_DB_BASES" ]; then
						host=$HOST
					fi
				done
			else
				old_weight='100'
				for db in $host_str; do
					parse_object_kv_list "$db"
					let weight="$U_DB_BASES * 100 / $MAX_DB" > /dev/null 2>&1
					if [ "$old_weight" -gt "$weight" ]; then
						host="$HOST"
						old_weight="$weight"
					fi
				done
			fi
		fi
	fi
}

# Database charset validation
is_charset_valid() {
	host_str=$(grep "HOST='$host'" $HESTIA/conf/$type.conf)
	parse_object_kv_list "$host_str"

	if [ -z "$(echo $CHARSETS | grep -wi $charset)" ]; then
		echo "Error: charset $charset not exist"
		log_event "$E_NOTEXIST" "$ARGUMENTS"
		exit $E_NOTEXIST
	fi
}

# Increase database host value
increase_dbhost_values() {
	host_str=$(grep "HOST='$host'" $HESTIA/conf/$type.conf)
	parse_object_kv_list "$host_str"

	old_dbbases="U_DB_BASES='$U_DB_BASES'"
	new_dbbases="U_DB_BASES='$((U_DB_BASES + 1))'"
	if [ -z "$U_SYS_USERS" ]; then
		old_users="U_SYS_USERS=''"
		new_users="U_SYS_USERS='$user'"
	else
		old_users="U_SYS_USERS='$U_SYS_USERS'"
		new_users="U_SYS_USERS='$U_SYS_USERS'"
		if [ -z "$(echo $U_SYS_USERS | sed "s/,/\n/g" | grep -w $user)" ]; then
			old_users="U_SYS_USERS='$U_SYS_USERS'"
			new_users="U_SYS_USERS='$U_SYS_USERS,$user'"
		fi
	fi

	sed -i "s/$old_dbbases/$new_dbbases/g" $HESTIA/conf/$type.conf
	sed -i "s/$old_users/$new_users/g" $HESTIA/conf/$type.conf
}

# Decrease database host value
decrease_dbhost_values() {
	host_str=$(grep "HOST='$HOST'" $HESTIA/conf/$TYPE.conf)
	parse_object_kv_list "$host_str"

	old_dbbases="U_DB_BASES='$U_DB_BASES'"
	new_dbbases="U_DB_BASES='$((U_DB_BASES - 1))'"
	old_users="U_SYS_USERS='$U_SYS_USERS'"
	U_SYS_USERS=$(echo "$U_SYS_USERS" \
		| sed "s/,/\n/g" \
		| sed "s/^$user$//g" \
		| sed "/^$/d" \
		| sed ':a;N;$!ba;s/\n/,/g')
	new_users="U_SYS_USERS='$U_SYS_USERS'"

	sed -i "s/$old_dbbases/$new_dbbases/g" $HESTIA/conf/$TYPE.conf
	sed -i "s/$old_users/$new_users/g" $HESTIA/conf/$TYPE.conf
}

# Create MySQL database
add_mysql_database() {
	mysql_connect $host

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
	psql_connect $host

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

add_mysql_database_temp_user() {
	mysql_connect $host

	mysql_ver_sub=$(echo $mysql_ver | cut -d '.' -f1)
	mysql_ver_sub_sub=$(echo $mysql_ver | cut -d '.' -f2)

	if [ "$mysql_fork" = "mysql" ] && [ "$mysql_ver_sub" -ge 8 ]; then
		query="CREATE USER \`$dbuser\`@localhost
			IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@localhost"
		mysql_query "$query" > /dev/null
	else
		query="GRANT ALL ON \`$database\`.* TO \`$dbuser\`@localhost
    		IDENTIFIED BY '$dbpass'"
		mysql_query "$query" > /dev/null
	fi
}

delete_mysql_database_temp_user() {
	mysql_connect $host
	query="REVOKE ALL ON \`$database\`.* FROM \`$dbuser\`@localhost"
	mysql_query "$query" > /dev/null
	query="DROP USER '$dbuser'@'localhost'"
	mysql_query "$query" > /dev/null
}

# Check if database host do not exist in config
is_dbhost_new() {
	if [ -e "$HESTIA/conf/$type.conf" ]; then
		check_host=$(grep "HOST='$host'" $HESTIA/conf/$type.conf)
		if [ "$check_host" ]; then
			echo "Error: db host exist"
			log_event "$E_EXISTS" "$ARGUMENTS"
			exit $E_EXISTS
		fi
	fi
}

# Get database values
get_database_values() {
	parse_object_kv_list $(grep "DB='$database'" $USER_DATA/db.conf)
}

# Change MySQL database password
change_mysql_password() {
	mysql_connect $HOST

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
	psql_connect $HOST
	query="ALTER ROLE $DBUSER WITH LOGIN PASSWORD '$dbpass'"
	psql_query "$query" > /dev/null

	query="SELECT rolpassword FROM pg_authid WHERE rolname='$DBUSER'"
	md5=$(psql_query "$query" | grep md5 | cut -f 2 -d \ )
}

# Delete MySQL database
delete_mysql_database() {
	mysql_connect $HOST

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
	psql_connect $HOST

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

# Dump MySQL database
dump_mysql_database() {
	mysql_connect $HOST

	mysql_dump $dump $database

	query="SHOW GRANTS FOR '$DBUSER'@'localhost'"
	mysql_query "$query" | grep -v "Grants for" > $grants

	query="SHOW GRANTS FOR '$DBUSER'@'%'"
	mysql_query "$query" | grep -v "Grants for" > $grants
}

# Dump PostgreSQL database
dump_pgsql_database() {
	psql_connect $HOST

	psql_dump $dump $database

	query="SELECT rolpassword FROM pg_authid WHERE rolname='$DBUSER'"
	md5=$(psql_query "$query" | head -n1 | cut -f 2 -d \ )
	pw_str="UPDATE pg_authid SET rolpassword='$md5' WHERE rolname='$DBUSER'"
	gr_str="GRANT ALL PRIVILEGES ON DATABASE $database to '$DBUSER'"
	echo -e "$pw_str\n$gr_str" >> $grants
}

# Check if database server is in use
is_dbhost_free() {
	host_str=$(grep "HOST='$host'" $HESTIA/conf/$type.conf)
	parse_object_kv_list "$host_str"
	if [ 0 -ne "$U_DB_BASES" ]; then
		echo "Error: host $HOST is used"
		log_event "$E_INUSE" "$ARGUMENTS"
		exit $E_INUSE
	fi
}

# Suspend MySQL database
suspend_mysql_database() {
	mysql_connect $HOST
	query="REVOKE ALL ON \`$database\`.* FROM \`$DBUSER\`@\`%\`"
	mysql_query "$query" > /dev/null
	query="REVOKE ALL ON \`$database\`.* FROM \`$DBUSER\`@localhost"
	mysql_query "$query" > /dev/null
}

# Suspend PostgreSQL database
suspend_pgsql_database() {
	psql_connect $HOST
	query="REVOKE ALL PRIVILEGES ON $database FROM $DBUSER"
	psql_query "$query" > /dev/null
}

# Unsuspend MySQL database
unsuspend_mysql_database() {
	mysql_connect $HOST
	query="GRANT ALL ON \`$database\`.* TO \`$DBUSER\`@\`%\`"
	mysql_query "$query" > /dev/null
	query="GRANT ALL ON \`$database\`.* TO \`$DBUSER\`@localhost"
	mysql_query "$query" > /dev/null
}

# Unsuspend PostgreSQL database
unsuspend_pgsql_database() {
	psql_connect $HOST
	query="GRANT ALL PRIVILEGES ON DATABASE $database TO $DBUSER"
	psql_query "$query" > /dev/null
}

# Get MySQL disk usage
get_mysql_disk_usage() {
	mysql_connect $HOST
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
	psql_connect $HOST

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

# Delete MySQL user
delete_mysql_user() {
	mysql_connect $HOST

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
	psql_connect $HOST

	query="REVOKE ALL PRIVILEGES ON DATABASE $database FROM $old_dbuser"
	psql_query "$query" > /dev/null

	query="REVOKE CONNECT ON DATABASE template1 FROM $old_dbuser"
	psql_query "$query" > /dev/null

	query="DROP ROLE $old_dbuser"
	psql_query "$query" > /dev/null
}
