# WEB domain rebuild
rebuild_web_domain_conf() {

    # Get domain values
    domain_idn=$(idn -t --quiet -a "$domain")
    get_domain_values 'web'
    ip=$(get_real_ip $IP)

    # Preparing domain values for the template substitution
    upd_web_domain_values

    # Rebuilding directories
    mkdir -p $HOMEDIR/$user/web/$domain \
        $HOMEDIR/$user/web/$domain/public_html \
        $HOMEDIR/$user/web/$domain/public_shtml \
        $HOMEDIR/$user/web/$domain/document_errors \
        $HOMEDIR/$user/web/$domain/cgi-bin \
        $HOMEDIR/$user/web/$domain/private \
        $HOMEDIR/$user/web/$domain/stats \
        $HOMEDIR/$user/web/$domain/logs

    # Create domain logs
    touch /var/log/httpd/domains/$domain.bytes \
          /var/log/httpd/domains/$domain.log \
          /var/log/httpd/domains/$domain.error.log

    # Create symlinks
    cd $HOMEDIR/$user/web/$domain/logs/
    ln -f -s /var/log/httpd/domains/$domain.log .
    ln -f -s /var/log/httpd/domains/$domain.error.log .
    cd - > /dev/null

    # Propagate html skeleton
    if [ ! -e "$WEBTPL/skel/document_errors/" ]; then
        cp -r $WEBTPL/skel/document_errors/ $HOMEDIR/$user/web/$domain/
    fi

    # Set folder permissions
    chmod 551 $HOMEDIR/$user/web/$domain
    chmod 751 $HOMEDIR/$user/web/$domain/private
    chmod 751 $HOMEDIR/$user/web/$domain/cgi-bin
    chmod 751 $HOMEDIR/$user/web/$domain/public_html
    chmod 751 $HOMEDIR/$user/web/$domain/public_shtml
    chmod 751 $HOMEDIR/$user/web/$domain/document_errors
    chmod 551 $HOMEDIR/$user/web/$domain/stats
    chmod 551 $HOMEDIR/$user/web/$domain/logs
    chmod 640 /var/log/httpd/domains/$domain.*

    # Set ownership
    chown $user:$user $HOMEDIR/$user/web/$domain
    chown $user:$user $HOMEDIR/$user/web/$domain/private
    chown $user:$user $HOMEDIR/$user/web/$domain/cgi-bin
    chown $user:$user $HOMEDIR/$user/web/$domain/public_html
    chown $user:$user $HOMEDIR/$user/web/$domain/public_shtml
    chown -R $user:$user $HOMEDIR/$user/web/$domain/document_errors
    chown root:$user /var/log/httpd/domains/$domain.*


    # Adding tmp_httpd.conf
    tpl_file="$WEBTPL/apache_$TPL.tpl"
    conf="$HOMEDIR/$user/conf/web/tmp_httpd.conf"
    add_web_config
    chown root:apache $conf
    chmod 640 $conf

    # Running template trigger
    if [ -x $WEBTPL/apache_$TPL.sh ]; then
        $WEBTPL/apache_$TPL.sh $user $domain $ip $HOMEDIR $docroot
    fi

    # Checking aliases
    if [ ! -z "$ALIAS" ]; then
        aliases=$(echo "$ALIAS"|tr ',' '\n'| wc -l)
        user_aliases=$((user_aliases + aliases))
    fi

    # Checking stats
    if [ ! -z "$STATS" ]; then
        cat $WEBTPL/$STATS.tpl |\
            sed -e "s/%ip%/$ip/g" \
                -e "s/%web_port%/$WEB_PORT/g" \
                -e "s/%web_ssl_port%/$WEB_SSL_PORT/g" \
                -e "s/%proxy_port%/$PROXY_PORT/g" \
                -e "s/%proxy_ssl_port%/$PROXY_SSL_PORT/g" \
                -e "s/%domain_idn%/$domain_idn/g" \
                -e "s/%domain%/$domain/g" \
                -e "s/%user%/$user/g" \
                -e "s/%home%/${HOMEDIR////\/}/g" \
                -e "s/%alias%/${aliases//,/ }/g" \
                -e "s/%alias_idn%/${aliases_idn//,/ }/g" \
                > $HOMEDIR/$user/conf/web/$STATS.$domain.conf

        if [ "$STATS" == 'awstats' ]; then
            if [ ! -e "/etc/awstats/$STATS.$domain_idn.conf" ]; then
                ln -s $HOMEDIR/$user/conf/web/$STATS.$domain.conf \
                    /etc/awstats/$STATS.$domain_idn.conf
            fi
        fi

        webstats="$BIN/v-update-web-domain-stat $user $domain"
        check_webstats=$(grep "$webstats" $VESTA/data/queue/webstats.pipe)
        if [ -z "$check_webstats" ]; then
            echo "$webstats" >> $VESTA/data/queue/webstats.pipe
        fi

        if [ ! -z "$STATS_USER" ]; then
            stats_dir="$HOMEDIR/$user/web/$domain/stats"

            # Adding htaccess file
            echo "AuthUserFile $stats_dir/.htpasswd" > $stats_dir/.htaccess
            echo "AuthName \"Web Statistics\"" >> $stats_dir/.htaccess
            echo "AuthType Basic" >> $stats_dir/.htaccess
            echo "Require valid-user" >> $stats_dir/.htaccess

            # Generating htaccess user and password
            echo "$STATS_USER:$STATS_CRYPT" > $stats_dir/.htpasswd
        fi
    fi

    # Checking ssl
    if [ "$SSL" = 'yes' ]; then
        # Adding domain to the shttpd.conf
        conf="$HOMEDIR/$user/conf/web/tmp_shttpd.conf"
        tpl_file="$WEBTPL/apache_$TPL.stpl"
        add_web_config
        chown root:apache $conf
        chmod 640 $conf

        cp -f $USER_DATA/ssl/$domain.crt \
            $HOMEDIR/$user/conf/web/ssl.$domain.crt
        cp -f $USER_DATA/ssl/$domain.key \
            $HOMEDIR/$user/conf/web/ssl.$domain.key
        cp -f $USER_DATA/ssl/$domain.pem \
            $HOMEDIR/$user/conf/web/ssl.$domain.pem
        if [ -e "$USER_DATA/ssl/$domain.ca" ]; then
            cp -f $USER_DATA/ssl/$domain.ca \
                $HOMEDIR/$user/conf/web/ssl.$domain.ca
        fi

        # Running template trigger
        if [ -x $WEBTPL/apache_$TPL.sh ]; then
            $WEBTPL/apache_$TPL.sh $user $domain $ip $HOMEDIR $sdocroot
        fi

        user_ssl=$((user_ssl + 1))
        ssl_change='yes'
    fi

    # Checking nginx
    if [ ! -z "$NGINX" ]; then
        tpl_file="$WEBTPL/nginx_$NGINX.tpl"
        conf="$HOMEDIR/$user/conf/web/tmp_nginx.conf"
        add_web_config
        chown root:nginx $conf
        chmod 640 $conf

        if [ "$SSL" = 'yes' ]; then
            tpl_file="$WEBTPL/nginx_$NGINX.stpl"
            conf="$HOMEDIR/$user/conf/web/tmp_snginx.conf"
            add_web_config
            chown root:nginx $conf
            chmod 640 $conf
        fi
        ngix_change='yes'
    fi
    if [ "$SUSPENDED" = 'yes' ]; then
        suspended_web=$((suspended_web + 1))
    fi
    user_domains=$((user_domains + 1))

    # Checking ftp
    if [ ! -z "$FTP_USER" ]; then
        if [ -z "$(grep ^$FTP_USER: /etc/passwd)" ]; then
            /usr/sbin/adduser -o -u $(id -u $user) -g $user -s /sbin/nologin \
                -M -d "$HOMEDIR/$user/web/$domain" $FTP_USER > /dev/null 2>&1

            shadow='/etc/shadow'
            shdw=$(grep "^$FTP_USER:" $shadow)
            shdw3=$(echo "$shdw" | cut -f3 -d :)
            shdw4=$(echo "$shdw" | cut -f4 -d :)
            shdw5=$(echo "$shdw" | cut -f5 -d :)
            shdw6=$(echo "$shdw" | cut -f6 -d :)
            shdw7=$(echo "$shdw" | cut -f7 -d :)
            shdw8=$(echo "$shdw" | cut -f8 -d :)
            shdw9=$(echo "$shdw" | cut -f9 -d :)
            chmod u+w $shadow
            sed -i "/^$FTP_USER:*/d" $shadow
            shdw_str="$FTP_USER:$FTP_MD5:$shdw3:$shdw4:$shdw5:$shdw6"
            shdw_str="$shdw_str:$shdw7:$shdw8:$shdw9"
            echo "$shdw_str" >> $shadow
            chmod u-w $shadow
        fi
    fi
}

# DNS domain rebuild
rebuild_dns_domain_conf() {

    # Get domain values
    get_domain_values 'dns'
    domain_idn=$(idn -t --quiet -a "$domain")

    # Checking zone file
    if [ ! -e "$USER_DATA/dns/$domain.conf" ]; then
        cat $DNSTPL/$TPL.tpl |\
            sed -e "s/%ip%/$IP/g" \
                -e "s/%domain_idn%/$domain_idn/g" \
                -e "s/%domain%/$domain/g" \
                -e "s/%ns1%/$ns1/g" \
                -e "s/%ns2%/$ns2/g" \
                -e "s/%ns3%/$ns3/g" \
                -e "s/%ns4%/$ns4/g" \
                -e "s/%time%/$TIME/g" \
                -e "s/%date%/$DATE/g" > $USER_DATA/dns/$domain.conf
    fi

    # Sorting records
    sort_dns_records

    # Updating zone
    update_domain_zone

    # Set file permissions
    chmod 640 $HOMEDIR/$user/conf/dns/$domain.db
    chown root:named $HOMEDIR/$user/conf/dns/$domain.db

    # Bind config check
    nconf='/etc/named.conf'
    if [ "$SUSPENDED" = 'yes' ]; then
        rm_string=$(grep -n /etc/namedb/$domain.db $nconf | cut -d : -f 1)
        if [ ! -z "$rm_string" ]; then
            sed -i "$rm_string d" $nconf
        fi
        suspended_dns=$((suspended_dns + 1))
    else
        if [ -z "$(grep /$domain.db $nconf)" ]; then
            named="zone \"$domain_idn\" {type master; file"
            named="$named \"$HOMEDIR/$user/conf/dns/$domain.db\";};"
            echo "$named" >> /etc/named.conf
        fi
    fi
    user_domains=$((user_domains + 1))
    records=$(wc -l $USER_DATA/dns/$domain.conf | cut -f 1 -d ' ')
    user_records=$((user_records + records))
    update_object_value 'dns' 'DOMAIN' "$domain" '$RECORDS' "$records"
}

# MAIL domain rebuild
rebuild_mail_domain_conf() {

    # Get domain values
    get_domain_values 'mail'

    # Rebuilding config structure
    rm -f /etc/exim/domains/$domain
    mkdir -p $HOMEDIR/$user/conf/mail/$domain
    ln -s $HOMEDIR/$user/conf/mail/$domain /etc/exim/domains/
    rm -f $HOMEDIR/$user/conf/mail/$domain/aliases
    rm -f $HOMEDIR/$user/conf/mail/$domain/protection
    rm -f $HOMEDIR/$user/conf/mail/$domain/passwd
    touch $HOMEDIR/$user/conf/mail/$domain/aliases
    touch $HOMEDIR/$user/conf/mail/$domain/protection
    touch $HOMEDIR/$user/conf/mail/$domain/passwd
    chown -R dovecot:mail $HOMEDIR/$user/conf/mail/$domain
    chown -R dovecot:mail /etc/exim/domains/$domain
    chmod 770 $HOMEDIR/$user/conf/mail/$domain
    chmod 660 $HOMEDIR/$user/conf/mail/$domain/*
    chmod 770 /etc/exim/domains/$domain

    # Adding antispam protection
    if [ "$ANTISPAM" = 'yes' ]; then
        echo 'antispam' >> $HOMEDIR/$user/conf/mail/$domain/protection
    fi

    # Adding antivirus protection
    if [ "$ANTIVIRUS" = 'yes' ]; then
        echo 'antivirus' >> $HOMEDIR/$user/conf/mail/$domain/protection
    fi

    # Adding dkim
    if [ "$DKIM" = 'yes' ]; then
        U_MAIL_DKMI=$((U_MAIL_DKMI + 1))
        pem="$USER_DATA/mail/$domain.pem"
        pub="$USER_DATA/mail/$domain.pub"
        openssl genrsa -out $pem 512 &>/dev/null
        openssl rsa -pubout -in $pem -out $pub &>/dev/null
        chmod 660 $USER_DATA/mail/$domain.*

        cp $pem $HOMEDIR/$user/conf/mail/$domain/dkim.pem
        chown root:mail $HOMEDIR/$user/conf/mail/$domain/dkim.pem
        chmod 660 $HOMEDIR/$user/conf/mail/$domain/dkim.pem

        # Deleting old dkim records
        records=$($BIN/v-list-dns-domain-records $user $domain plain)
        dkim_records=$(echo "$records" |grep -w '_domainkey'|cut -f 1 -d ' ')
        for id in $dkim_records; do
            $BIN/v-delete-dns-domain-record $user $domain $id
        done

        # Adding dkim dns records
        check_dns_domain=$(is_object_valid 'dns' 'DOMAIN' "$domain")
        if [ "$?" -eq 0 ]; then
            p=$(cat $pub|grep -v ' KEY---'|tr -d '\n')
            record='_domainkey'
            policy="\"t=y; o=~;\""
            $BIN/v-add-dns-domain-record $user $domain $record TXT "$policy"

            record='mail._domainkey'
            slct="\"k=rsa\; p=$p\""
            $BIN/v-add-dns-domain-record $user $domain $record TXT "$slct"
        fi
    fi

    # Removing symbolic link
    if [ "$SUSPENDED" = 'yes' ]; then
        SUSPENDED_MAIL=$((SUSPENDED_MAIL +1))
        rm -f /etc/exim/domains/$domain
    fi

    if [ ! -e $HOMEDIR/$user/mail/$domain ]; then
        mkdir $HOMEDIR/$user/mail/$domain
    fi
    chown $user:mail $HOMEDIR/$user/mail/$domain
    chmod 770 $HOMEDIR/$user/mail/$domain

    dom_aliases=$HOMEDIR/$user/conf/mail/$domain/aliases
    if [ ! -z "$CATCHALL" ]; then
        echo "*@$domain:$CATCHALL" >> $dom_aliases
    fi

    # Rebuild domain accounts
    accs=0
    dom_diks=0
    if [ -e "$USER_DATA/mail/$domain.conf" ]; then
        accounts=$(search_objects "mail/$domain" 'SUSPENDED' "no" 'ACCOUNT')
    else
        accounts=''
    fi

    for account in $accounts; do
        (( ++accs))
        dom_diks=$((dom_diks + U_DISK))
        object=$(grep "ACCOUNT='$account'" $USER_DATA/mail/$domain.conf)
        eval "$object"
        if [ "$SUSPENDED" = 'yes' ]; then
            MD5='SUSPENDED'
        fi

        str="$account:$MD5:$user:mail::$HOMEDIR/$user:$QUOTA"
        echo $str >> $HOMEDIR/$user/conf/mail/$domain/passwd

        for malias in ${ALIAS//,/ }; do
            echo "$malias@$domain:$account@$domain" >> $dom_aliases
        done
        if [ ! -z "$FWD" ]; then
            echo "$account@$domain:$FWD" >> $dom_aliases
        fi

    done
    update_object_value 'mail' 'DOMAIN' "$domain" '$ACCOUNTS' "$accs"
    update_object_value 'mail' 'DOMAIN' "$domain" '$U_DISK' "$dom_diks"
    U_MAIL_ACCOUNTS=$((U_MAIL_ACCOUNTS + accs))
    U_DISK_MAIL=$((U_DISK_MAIL + dom_diks))
    U_MAIL_DOMAINS=$((U_MAIL_DOMAINS + 1))
}


# Rebuild MySQL
rebuild_mysql_database() {

    host_str=$(grep "HOST='$HOST'" $VESTA/conf/mysql.conf)
    eval $host_str
    if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ]; then
        echo "Error: mysql config parsing failed"
        if [ ! -z "$send_mail" ]; then
            echo "Can't parse MySQL DB config" | $send_mail -s "$subj" $email
        fi
        log_event "$E_PARSING" "$EVENT"
        exit $E_PARSING
    fi

    query='SELECT VERSION()'
    mysql -h $HOST -u $USER -p$PASSWORD -e "$query" > /dev/null 2>&1
    if [ '0' -ne "$?" ]; then
        echo "Error: Database connection to $HOST failed"
        if [ ! -z "$send_mail" ]; then
            echo "Database connection to MySQL host $HOST failed" |\
                $send_mail -s "$subj" $email
        fi
        log_event  "$E_DB $EVENT"
        exit $E_DB
    fi

    query="CREATE DATABASE \`$DB\` CHARACTER SET $CHARSET"
    mysql -h $HOST -u $USER -p$PASSWORD -e "$query" > /dev/null 2>&1

    query="GRANT ALL ON \`$DB\`.* TO \`$DBUSER\`@\`%\`"
    mysql -h $HOST -u $USER -p$PASSWORD -e "$query" > /dev/null 2>&1

    query="GRANT ALL ON \`$DB\`.* TO \`$DBUSER\`@localhost"
    mysql -h $HOST -u $USER -p$PASSWORD -e "$query" > /dev/null 2>&1

    query="UPDATE mysql.user SET Password='$MD5' WHERE User='$DBUSER';"
    mysql -h $HOST -u $USER -p$PASSWORD -e "$query" > /dev/null 2>&1
}

# Rebuild PostgreSQL
rebuild_pgsql_database() {

    host_str=$(grep "HOST='$HOST'" $VESTA/conf/pgsql.conf)
    eval $host_str
    export PGPASSWORD="$PASSWORD"
    if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ] || [ -z $TPL ]; then
        echo "Error: postgresql config parsing failed"
        if [ ! -z "$send_mail" ]; then
            echo "Can't parse PostgreSQL config" | $send_mail -s "$subj" $email
        fi
        log_event "$E_PARSING" "$EVENT"
        exit $E_PARSING
    fi

    query='SELECT VERSION()'
    psql -h $HOST -U $USER -c "$query" > /dev/null 2>&1
    if [ '0' -ne "$?" ];  then
        echo "Error: Connection failed"
        if [ ! -z "$send_mail" ]; then
            echo "Database connection to PostgreSQL host $HOST failed" |\
                $send_mail -s "$subj" $email
        fi
        log_event "$E_DB" "$EVENT"
        exit $E_DB
    fi

    query="CREATE ROLE $DBUSER"
    psql -h $HOST -U $USER -c "$query" > /dev/null 2>&1

    query="UPDATE pg_authid SET rolpassword='$MD5' WHERE rolname='$DBUSER'"
    psql -h $HOST -U $USER -c "$query" > /dev/null 2>&1

    query="CREATE DATABASE $DB OWNER $DBUSER"
    if [ "$TPL" = 'template0' ]; then
        query="$query ENCODING '$CHARSET' TEMPLATE $TPL"
    else
        query="$query TEMPLATE $TPL"
    fi
    psql -h $HOST -U $USER -c "$query" > /dev/null 2>&1

    query="GRANT ALL PRIVILEGES ON DATABASE $DB TO $DBUSER"
    psql -h $HOST -U $USER -c "$query" > /dev/null 2>&1

    query="GRANT CONNECT ON DATABASE template1 to $dbuser"
    psql -h $HOST -U $USER -c "$query" > /dev/null 2>&1
}


# Import MySQL dump
import_mysql_database() {

    host_str=$(grep "HOST='$HOST'" $VESTA/conf/mysql.conf)
    eval $host_str
    if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ]; then
        echo "Error: mysql config parsing failed"
        log_event "$E_PARSING" "$EVENT"
        exit $E_PARSING
    fi

    mysql -h $HOST -u $USER -p$PASSWORD $DB < $1 > /dev/null 2>&1
}


# Import PostgreSQL dump
import_pgsql_database() {

    host_str=$(grep "HOST='$HOST'" $VESTA/conf/pgsql.conf)
    eval $host_str
    export PGPASSWORD="$PASSWORD"
    if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ] || [ -z $TPL ]; then
        echo "Error: postgresql config parsing failed"
        log_event "$E_PARSING" "$EVENT"
        exit $E_PARSING
    fi

    psql -h $HOST -U $USER $DB < $1 > /dev/null 2>&1
}
