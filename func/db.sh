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

# Escape a value for safe use inside a PostgreSQL single-quoted SQL string
# literal. PostgreSQL uses standard-conforming strings by default, so a
# backslash is NOT an escape character and must not be doubled here.
sql_escape() {
	printf '%s' "$1" | sed "s/'/''/g"
}

# Escape a value for safe use inside a MariaDB/MySQL single-quoted SQL string
# literal. MariaDB/MySQL treat backslash as an escape character by default
# (unless NO_BACKSLASH_QUOTES is set), so both backslashes and single quotes
# must be escaped. Backslashes are doubled first so the backslashes introduced
# when escaping single quotes are not doubled again.
mysql_sql_escape() {
	printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e "s/'/''/g"
}

# Escape the wildcard syntax used in database-level grants by MariaDB and
# by MySQL with partial_revokes disabled.
mysql_escape_db_grant_name() {
	printf '%s' "$1" | sed -e 's/\\/\\\\/g' -e 's/_/\\_/g' -e 's/%/\\%/g'
}

# Return the exact database name for a GRANT on the connected server.
mysql_grant_db_name() {
	local partial_revokes
	if [ "$mysql_fork" = 'mysql' ] && [ "${mysql_ver%%.*}" -ge 8 ]; then
		partial_revokes=$(mysql_query_raw "SHOW GLOBAL VARIABLES WHERE Variable_name = 'partial_revokes'") || return 1
		case "$partial_revokes" in
			$'partial_revokes\tON')
				# MySQL matches the name literally, including any backslashes.
				printf '%s' "$1"
				return
				;;
			$'partial_revokes\tOFF' | '') ;;
			*) return 1 ;;
		esac
	fi
	mysql_escape_db_grant_name "$1"
}

mysql_revoke_named_db_grants() {
	mysql_query "REVOKE ALL ON \`$1\`.* FROM \`$2\`@\`%\`" > /dev/null
	mysql_query "REVOKE ALL ON \`$1\`.* FROM \`$2\`@localhost" > /dev/null
}

# Remove the grant format that does not apply to the connected server. The
# unescaped form is the correct grant when partial_revokes is enabled.
# Usage: mysql_revoke_obsolete_db_grants <database> <dbuser> <current_grant_name>
mysql_revoke_obsolete_db_grants() {
	local obsolete_grant="$1"
	if [ "$3" = "$1" ]; then
		obsolete_grant=$(mysql_escape_db_grant_name "$1")
	fi
	if [ "$obsolete_grant" != "$3" ]; then
		mysql_revoke_named_db_grants "$obsolete_grant" "$2"
	fi
}

# Revoke all database-level grants of a user on a database, both the escaped
# form and the legacy unescaped form.
# Usage: mysql_revoke_db_grants <database> <dbuser>
mysql_revoke_db_grants() {
	local escaped_grant
	escaped_grant=$(mysql_escape_db_grant_name "$1")
	mysql_revoke_named_db_grants "$escaped_grant" "$2"
	if [ "$escaped_grant" != "$1" ]; then
		mysql_revoke_named_db_grants "$1" "$2"
	fi
}

# Replace obsolete grants with exact grants for every MySQL database in the
# users' db.conf files. Suspended databases lose both forms of the grant.
mysql_migrate_legacy_db_grants() {
	local db_conf db_line failed=0
	for db_conf in "$HESTIA"/data/users/*/db.conf; do
		[ -f "$db_conf" ] || continue
		while IFS= read -r db_line; do
			[[ "$db_line" == *"TYPE='mysql'"* ]] || continue
			# Subshell: parse_object_kv_list and mysql_connect overwrite
			# globals (USER, HOST, ...) and mysql_connect may exit
			(
				parse_object_kv_list "$db_line"
				db_name="$DB"
				db_user="$DBUSER"
				db_suspended="$SUSPENDED"
				if [ -z "$db_name" ] || [ -z "$db_user" ] || [ -z "$HOST" ]; then
					echo "Error: incomplete MySQL database configuration in $db_conf" >&2
					exit 1
				fi
				mysql_connect "$HOST"
				if [ "$db_suspended" = 'yes' ]; then
					# A missing grant makes REVOKE fail, including on repeat runs.
					mysql_revoke_db_grants "$db_name" "$db_user" || true
				else
					db_grant=$(mysql_grant_db_name "$db_name")
					check_result $? "Unable to determine grant syntax for database $db_name"
					mysql_revoke_obsolete_db_grants "$db_name" "$db_user" "$db_grant"
					grant_failed=0
					mysql_query "GRANT ALL ON \`$db_grant\`.* TO \`$db_user\`@\`%\`" > /dev/null || grant_failed=1
					mysql_query "GRANT ALL ON \`$db_grant\`.* TO \`$db_user\`@localhost" > /dev/null || grant_failed=1
					if [ "$grant_failed" -ne 0 ]; then
						echo "Error: unable to restore MySQL grants for $db_name on $HOST" >&2
						exit 1
					fi
				fi
			) || failed=1
		done < "$db_conf"
	done
	return "$failed"
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

# Run a query and return raw rows without column headers.
# Intended for reading result sets from the shell, e.g.
# SELECT Host FROM mysql.user WHERE User = ...
mysql_query_raw() {
	sql_tmp=$(mktemp)
	echo "$1" > $sql_tmp
	if [ -f '/usr/bin/mariadb' ]; then
		mariadb --defaults-file=$mycnf --batch --skip-column-names --raw \
			< "$sql_tmp" 2> /dev/null
		return_code=$?
	else
		mysql --defaults-file=$mycnf --batch --skip-column-names --raw \
			< "$sql_tmp" 2> /dev/null
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
	$mysqldmp --defaults-file=$mycnf --single-transaction --routines --events -r $1 $2 2> $err
	if [ '0' -ne "$?" ]; then
		$mysqldmp --defaults-extra-file=$mycnf --single-transaction --routines --events -r $1 $2 2> $err
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
	local db_grant
	mysql_connect $host
	db_grant=$(mysql_grant_db_name "$database")
	check_result $? "Unable to determine grant syntax for database $database"

	mysql_ver_sub=$(echo $mysql_ver | cut -d '.' -f1)
	mysql_ver_sub_sub=$(echo $mysql_ver | cut -d '.' -f2)

	dbpass_esc=$(mysql_sql_escape "$dbpass")

	query="CREATE DATABASE \`$database\` CHARACTER SET $charset"
	mysql_query "$query"
	check_result $? "Unable to create database $database"

	if [ "$mysql_fork" = "mysql" ] && [ "$mysql_ver_sub" -ge 8 ]; then
		query="CREATE USER \`$dbuser\`@\`%\`
            IDENTIFIED BY '$dbpass_esc'"
		mysql_query "$query" > /dev/null

		query="CREATE USER \`$dbuser\`@localhost
            IDENTIFIED BY '$dbpass_esc'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$db_grant\`.* TO \`$dbuser\`@\`%\`"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$db_grant\`.* TO \`$dbuser\`@localhost"
		mysql_query "$query" > /dev/null
	else
		query="GRANT ALL ON \`$db_grant\`.* TO \`$dbuser\`@\`%\`
            IDENTIFIED BY '$dbpass_esc'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$db_grant\`.* TO \`$dbuser\`@localhost
            IDENTIFIED BY '$dbpass_esc'"
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

	dbpass_esc=$(sql_escape "$dbpass")
	query="CREATE ROLE $dbuser WITH LOGIN PASSWORD '$dbpass_esc'"
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
	local db_grant
	mysql_connect $host
	db_grant=$(mysql_grant_db_name "$database") || return 1

	mysql_ver_sub=$(echo $mysql_ver | cut -d '.' -f1)

	dbpass_esc=$(mysql_sql_escape "$dbpass")

	if [ "$mysql_fork" = "mysql" ] && [ "$mysql_ver_sub" -ge 8 ]; then
		query="CREATE USER \`$dbuser\`@\`%\`
			IDENTIFIED BY '$dbpass_esc'"
		mysql_query "$query" > /dev/null || return 1

		query="GRANT ALL ON \`$db_grant\`.* TO \`$dbuser\`@\`%\`"
		if ! mysql_query "$query" > /dev/null; then
			mysql_query "DROP USER '$dbuser'@'%'" > /dev/null
			return 1
		fi
	else
		query="GRANT ALL ON \`$db_grant\`.* TO \`$dbuser\`@\`%\`
    		IDENTIFIED BY '$dbpass_esc'"
		mysql_query "$query" > /dev/null
	fi
}

delete_mysql_database_temp_user() {
	mysql_connect $host
	query="REVOKE ALL ON \`$(mysql_escape_db_grant_name "$database")\`.* FROM \`$dbuser\`@\`%\`"
	mysql_query "$query" > /dev/null
	query="REVOKE ALL ON \`$database\`.* FROM \`$dbuser\`@\`%\`"
	mysql_query "$query" > /dev/null
	query="DROP USER '$dbuser'@'%'"
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
	local db_grant
	mysql_connect $HOST
	db_grant=$(mysql_grant_db_name "$database")
	check_result $? "Unable to determine grant syntax for database $database"
	mysql_revoke_obsolete_db_grants "$database" "$DBUSER" "$db_grant"

	mysql_ver_sub=$(echo $mysql_ver | cut -d '.' -f1)
	mysql_ver_sub_sub=$(echo $mysql_ver | cut -d '.' -f2)

	dbpass_esc=$(mysql_sql_escape "$dbpass")

	if [ "$mysql_fork" = "mysql" ]; then
		# mysql
		if [ "$mysql_ver_sub" -ge 8 ]; then
			# mysql >= 8
			query="SET PASSWORD FOR \`$DBUSER\`@\`%\` = '$dbpass_esc'"
			mysql_query "$query" > /dev/null
			query="SET PASSWORD FOR \`$DBUSER\`@localhost = '$dbpass_esc'"
			mysql_query "$query" > /dev/null
		else
			# mysql < 8
			query="GRANT ALL ON \`$db_grant\`.* TO \`$DBUSER\`@\`%\`
                  IDENTIFIED BY '$dbpass_esc'"
			mysql_query "$query" > /dev/null

			query="GRANT ALL ON \`$db_grant\`.* TO \`$DBUSER\`@localhost
                  IDENTIFIED BY '$dbpass_esc'"
			mysql_query "$query" > /dev/null
		fi
	else
		# mariadb
		query="GRANT ALL ON \`$db_grant\`.* TO \`$DBUSER\`@\`%\`
              IDENTIFIED BY '$dbpass_esc'"
		mysql_query "$query" > /dev/null

		query="GRANT ALL ON \`$db_grant\`.* TO \`$DBUSER\`@localhost
              IDENTIFIED BY '$dbpass_esc'"
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
	dbpass_esc=$(sql_escape "$dbpass")
	query="ALTER ROLE $DBUSER WITH LOGIN PASSWORD '$dbpass_esc'"
	psql_query "$query" > /dev/null

	query="SELECT rolpassword FROM pg_authid WHERE rolname='$DBUSER'"
	md5=$(psql_query "$query" | grep md5 | cut -f 2 -d \ )
}

# Delete MySQL database
delete_mysql_database() {
	mysql_connect $HOST

	query="DROP DATABASE \`$database\`"
	mysql_query "$query"

	mysql_revoke_db_grants "$database" "$DBUSER"

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
	mysql_query "$query" | grep -v "Grants for" >> $grants
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
	mysql_revoke_db_grants "$database" "$DBUSER"
}

# Suspend PostgreSQL database
suspend_pgsql_database() {
	psql_connect $HOST
	query="REVOKE ALL PRIVILEGES ON $database FROM $DBUSER"
	psql_query "$query" > /dev/null
}

# Unsuspend MySQL database
unsuspend_mysql_database() {
	local db_grant
	mysql_connect $HOST
	db_grant=$(mysql_grant_db_name "$database")
	check_result $? "Unable to determine grant syntax for database $database"
	mysql_revoke_obsolete_db_grants "$database" "$DBUSER" "$db_grant"
	query="GRANT ALL ON \`$db_grant\`.* TO \`$DBUSER\`@\`%\`"
	mysql_query "$query" > /dev/null
	query="GRANT ALL ON \`$db_grant\`.* TO \`$DBUSER\`@localhost"
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

	mysql_revoke_db_grants "$database" "$old_dbuser"

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
