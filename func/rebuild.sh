# User account rebuild
rebuild_user_conf() {

    # Get user variables
    source $USER_DATA/user.conf

    # Creating user data files
    chmod 770 $USER_DATA
    chmod 660 $USER_DATA/user.conf
    touch $USER_DATA/backup.conf
    chmod 660 $USER_DATA/backup.conf
    touch $USER_DATA/history.log
    chmod 660 $USER_DATA/history.log
    touch $USER_DATA/stats.log
    chmod 660 $USER_DATA/stats.log
    
    # Update FNAME LNAME to NAME
    if [ -z "$NAME" ]; then 
        NAME="$FNAME $LNAME"
        if [ -z $FNAME ]; then NAME=""; fi
        
        sed -i "s/FNAME='$FNAME'/NAME='$NAME'/g" $USER_DATA/user.conf
        sed -i "/LNAME='$LNAME'/d" $USER_DATA/user.conf  
    fi
    if [ -z "${TWOFA+x}" ]; then 
        sed -i "/RKEY/a TWOFA=''" $USER_DATA/user.conf 
    fi
    if [ -z "${QRCODE+x}" ]; then
        sed -i "/TWOFA/a QRCODE=''" $USER_DATA/user.conf 
    fi
    if [ -z "${PHPCLI+x}" ]; then 
        sed -i "/QRCODE/a PHPCLI=''" $USER_DATA/user.conf 
    fi
    if [ -z "${ROLE+x}" ]; then 
        sed -i "/PHPCLI/a ROLE='user'" $USER_DATA/user.conf 
    fi
    # Run template trigger
    if [ -x "$HESTIA/data/packages/$PACKAGE.sh" ]; then
        $HESTIA/data/packages/$PACKAGE.sh "$user" "$CONTACT" "$NAME"
    fi

    # Rebuild user
    shell=$(grep -w "$SHELL" /etc/shells |head -n1)
    /usr/sbin/useradd "$user" -s "$shell" -c "$CONTACT" \
        -m -d "$HOMEDIR/$user" > /dev/null 2>&1

    # Add a general group for normal users created by Hestia
    if [ -z "$(grep "^hestia-users:" /etc/group)" ]; then
        groupadd --system "hestia-users"
    fi

    # Add membership to hestia-users group to non-admin users
    if [ "$user" = "admin" ]; then
        setfacl -m "g:admin:r-x" "$HOMEDIR/$user"
    else
        usermod -a -G "hestia-users" "$user"
        setfacl -m "u:$user:r-x" "$HOMEDIR/$user"
    fi
    setfacl -m "g:hestia-users:---" "$HOMEDIR/$user"

    # Update user shell
    /usr/bin/chsh -s "$shell" "$user" &>/dev/null

    # Update password
    chmod u+w /etc/shadow
    sed -i "s|^$user:[^:]*:|$user:$MD5:|" /etc/shadow
    chmod u-w /etc/shadow

    # Building directory tree
    if [ -e "$HOMEDIR/$user/conf" ]; then
        chattr -i $HOMEDIR/$user/conf > /dev/null 2>&1
    fi

    # Create default writeable folders
    mkdir -p \
        $HOMEDIR/$user/conf \
        $HOMEDIR/$user/.config \
        $HOMEDIR/$user/.cache \
        $HOMEDIR/$user/.local \
        $HOMEDIR/$user/.composer \
        $HOMEDIR/$user/.ssh

    chmod a+x $HOMEDIR/$user
    chmod a+x $HOMEDIR/$user/conf
    chown $user:$user \
        $HOMEDIR/$user \
        $HOMEDIR/$user/.config \
        $HOMEDIR/$user/.cache \
        $HOMEDIR/$user/.local \
        $HOMEDIR/$user/.composer \
        $HOMEDIR/$user/.ssh
    chown root:root $HOMEDIR/$user/conf

    $BIN/v-add-user-sftp-jail "$user"

    # Update disk pipe
    sed -i "/ $user$/d" $HESTIA/data/queue/disk.pipe
    echo "$BIN/v-update-user-disk $user" >> $HESTIA/data/queue/disk.pipe

    # WEB
    if [ ! -z "$WEB_SYSTEM" ] && [ "$WEB_SYSTEM" != 'no' ]; then
        mkdir -p $USER_DATA/ssl
        chmod 770 $USER_DATA/ssl
        touch $USER_DATA/web.conf
        chmod 660 $USER_DATA/web.conf
        if [ "$(grep -w $user $HESTIA/data/queue/traffic.pipe)" ]; then
            echo "$BIN/v-update-web-domains-traff $user" \
                >> $HESTIA/data/queue/traffic.pipe
        fi
        echo "$BIN/v-update-web-domains-disk $user" \
            >> $HESTIA/data/queue/disk.pipe

        if [[ -L "$HOMEDIR/$user/web" ]]; then
            rm $HOMEDIR/$user/web
        fi
        mkdir -p $HOMEDIR/$user/conf/web/$domain
        mkdir -p $HOMEDIR/$user/web
        mkdir -p $HOMEDIR/$user/tmp
        chmod 751 $HOMEDIR/$user/conf/web
        chmod 751 $HOMEDIR/$user/web
        chmod 771 $HOMEDIR/$user/tmp
        chown $user:$user $HOMEDIR/$user/web
        if [ -z "$create_user" ]; then
            $BIN/v-rebuild-web-domains $user $restart
        fi
    fi

    # DNS
    if [ ! -z "$DNS_SYSTEM" ] && [ "$DNS_SYSTEM" != 'no' ]; then
        mkdir -p $USER_DATA/dns
        chmod 770 $USER_DATA/dns
        touch $USER_DATA/dns.conf
        chmod 660 $USER_DATA/dns.conf

        mkdir -p $HOMEDIR/$user/conf/dns
        chmod 751 $HOMEDIR/$user/conf/dns
        if [ -z "$create_user" ]; then
            $BIN/v-rebuild-dns-domains $user $restart
        fi
    fi

    if [ ! -z "$MAIL_SYSTEM" ] && [ "$MAIL_SYSTEM" != 'no' ]; then
        mkdir -p $USER_DATA/mail
        chmod 770 $USER_DATA/mail
        touch $USER_DATA/mail.conf
        chmod 660 $USER_DATA/mail.conf
        echo "$BIN/v-update-mail-domains-disk $user" \
            >> $HESTIA/data/queue/disk.pipe

        if [[ -L "$HOMEDIR/$user/mail" ]]; then
            rm $HOMEDIR/$user/mail
        fi
        mkdir -p $HOMEDIR/$user/conf/mail/$domain
        mkdir -p $HOMEDIR/$user/mail
        chmod 751 $HOMEDIR/$user/mail
        chmod 751 $HOMEDIR/$user/conf/mail
        if [ -z "$create_user" ]; then
            $BIN/v-rebuild-mail-domains $user
        fi
    fi


    if [ ! -z "$DB_SYSTEM" ] && [ "$DB_SYSTEM" != 'no' ]; then
        touch $USER_DATA/db.conf
        chmod 660 $USER_DATA/db.conf
        echo "$BIN/v-update-databases-disk $user" >> $HESTIA/data/queue/disk.pipe

        if [ -z "$create_user" ]; then
            $BIN/v-rebuild-databases $user
        fi
    fi

    if [ ! -z "$CRON_SYSTEM" ] && [ "$CRON_SYSTEM" != 'no' ]; then
        touch $USER_DATA/cron.conf
        chmod 660 $USER_DATA/cron.conf

        if [ -z "$create_user" ]; then
            $BIN/v-rebuild-cron-jobs $user $restart
        fi
    fi

    # Set immutable flag
    chattr +i $HOMEDIR/$user/conf > /dev/null 2>&1
}

# WEB domain rebuild
rebuild_web_domain_conf() {

    # Ensure that global domain folders are available
    if [ ! -d /etc/$WEB_SYSTEM/conf.d/domains ]; then
        mkdir -p /etc/$WEB_SYSTEM/conf.d/domains
    fi
    if [ ! -d /etc/$PROXY_SYSTEM/conf.d/domains ]; then
        mkdir -p /etc/$PROXY_SYSTEM/conf.d/domains
    fi

    get_domain_values 'web'
    is_ip_valid $IP
    prepare_web_domain_values

    # Remove old web configuration files
    if [ -f /etc/$WEB_SYSTEM/conf.d/$domain.conf ]; then
        rm -f /etc/$WEB_SYSTEM/conf.d/$domain*.conf
    fi
    if [ -f /etc/$PROXY_SYSTEM/conf.d/$domain.conf ]; then
        rm -f /etc/$PROXY_SYSTEM/conf.d/$domain*.conf
    fi

    # Temporary allow write permissions to owner
    [ -d "$HOMEDIR/$user/web/$domain" ] && chmod 751 "$HOMEDIR/$user/web/$domain"

    # Rebuilding domain directories
    if [ -d "$HOMEDIR/$user/web/$domain/document_errors" ]; then
        $BIN/v-delete-fs-directory "$user" "$HOMEDIR/$user/web/$domain/document_errors"
    fi

    $BIN/v-add-fs-directory "$user" "$HOMEDIR/$user/web/$domain"
    $BIN/v-add-fs-directory "$user" "$HOMEDIR/$user/web/$domain/public_html"
    $BIN/v-add-fs-directory "$user" "$HOMEDIR/$user/web/$domain/public_shtml"
    $BIN/v-add-fs-directory "$user" "$HOMEDIR/$user/web/$domain/document_errors"
    $BIN/v-add-fs-directory "$user" "$HOMEDIR/$user/web/$domain/cgi-bin"
    $BIN/v-add-fs-directory "$user" "$HOMEDIR/$user/web/$domain/private"
    $BIN/v-add-fs-directory "$user" "$HOMEDIR/$user/web/$domain/stats"
    $BIN/v-add-fs-directory "$user" "$HOMEDIR/$user/web/$domain/logs"

    # Creating domain logs
    if [ ! -e "/var/log/$WEB_SYSTEM/domains" ]; then
        mkdir -p /var/log/$WEB_SYSTEM/domains
        chmod 771 /var/log/$WEB_SYSTEM/domains
    fi
    touch /var/log/$WEB_SYSTEM/domains/$domain.bytes \
          /var/log/$WEB_SYSTEM/domains/$domain.log \
          /var/log/$WEB_SYSTEM/domains/$domain.error.log

    # Creating symlinks
    cd $HOMEDIR/$user/web/$domain/logs/
    ln -f -s /var/log/$WEB_SYSTEM/domains/$domain.log .
    ln -f -s /var/log/$WEB_SYSTEM/domains/$domain.error.log .
    cd /

    # Propagating html skeleton
    if [ -d "$WEBTPL/skel/document_errors/" ]; then
        user_exec cp -r "$WEBTPL/skel/document_errors/" "$HOMEDIR/$user/web/$domain/"
    fi

    # Set ownership
    chown $user:$user \
        $HOMEDIR/$user/web/$domain \
        $HOMEDIR/$user/web/$domain/private \
        $HOMEDIR/$user/web/$domain/cgi-bin \
        $HOMEDIR/$user/web/$domain/public_html \
        $HOMEDIR/$user/web/$domain/public_shtml
    chown -R $user:$user $HOMEDIR/$user/web/$domain/document_errors
    chown root:$user /var/log/$WEB_SYSTEM/domains/$domain.*

    # Adding vhost configuration
    conf="$HOMEDIR/$user/conf/web/$domain/$WEB_SYSTEM.conf"
    add_web_config "$WEB_SYSTEM" "$TPL.tpl"

    # Adding SSL vhost configuration
    if [ "$SSL" = 'yes' ]; then
        ssl_file_dir="$HOMEDIR/$user/conf/web/$domain/ssl"
        conf="$HOMEDIR/$user/conf/web/$domain/$WEB_SYSTEM.ssl.conf"
        if [ ! -d "$ssl_file_dir" ]; then
            mkdir -p $ssl_file_dir
        fi
        add_web_config "$WEB_SYSTEM" "$TPL.stpl"
        cp -f $USER_DATA/ssl/$domain.crt \
            $HOMEDIR/$user/conf/web/$domain/ssl/$domain.crt
        cp -f $USER_DATA/ssl/$domain.key \
            $HOMEDIR/$user/conf/web/$domain/ssl/$domain.key
        cp -f $USER_DATA/ssl/$domain.pem \
            $HOMEDIR/$user/conf/web/$domain/ssl/$domain.pem
        if [ -e "$USER_DATA/ssl/$domain.ca" ]; then
            cp -f $USER_DATA/ssl/$domain.ca \
                $HOMEDIR/$user/conf/web/$domain/ssl/$domain.ca
        fi
    fi

    # Refresh HTTPS redirection if previously enabled
    if [ "$SSL_FORCE" = 'yes' ]; then
        $BIN/v-delete-web-domain-ssl-force $user $domain no
        $BIN/v-add-web-domain-ssl-force $user $domain yes
    fi

    if [ "$SSL_HSTS" = 'yes' ]; then
        $BIN/v-delete-web-domain-ssl-hsts $user $domain no
        $BIN/v-add-web-domain-ssl-hsts $user $domain yes
    fi

    # Adding proxy configuration
    if [ ! -z "$PROXY_SYSTEM" ] && [ ! -z "$PROXY" ]; then
        conf="$HOMEDIR/$user/conf/web/$domain/$PROXY_SYSTEM.conf"
        add_web_config "$PROXY_SYSTEM" "$PROXY.tpl"
        if [ "$SSL" = 'yes' ]; then
            conf="$HOMEDIR/$user/conf/web/$domain/$PROXY_SYSTEM.ssl.conf"
            add_web_config "$PROXY_SYSTEM" "$PROXY.stpl"
        fi
    fi

    # Adding web stats parser
    if [ ! -z "$STATS" ]; then
        domain_idn=$domain
        format_domain_idn
        cat $WEBTPL/$STATS/$STATS.tpl |\
            sed -e "s|%ip%|$local_ip|g" \
                -e "s|%web_system%|$WEB_SYSTEM|g" \
                -e "s|%domain_idn%|$domain_idn|g" \
                -e "s|%domain%|$domain|g" \
                -e "s|%user%|$user|g" \
                -e "s|%home%|$HOMEDIR|g" \
                -e "s|%alias%|${aliases//,/ }|g" \
                -e "s|%alias_idn%|${aliases_idn//,/ }|g" \
                > $HOMEDIR/$user/conf/web/$domain/$STATS.conf
        if [ "$STATS" == 'awstats' ]; then
            if [ -e "/etc/awstats/$STATS.$domain_idn.conf" ]; then
                rm -f "/etc/awstats/$STATS.$domain_idn.conf"
            fi
            ln -f -s $HOMEDIR/$user/conf/web/$domain/$STATS.conf \
                /etc/awstats/$STATS.$domain_idn.conf
        fi

        webstats="$BIN/v-update-web-domain-stat $user $domain"
        check_webstats=$(grep "$webstats" $HESTIA/data/queue/webstats.pipe)
        if [ -z "$check_webstats" ]; then
            echo "$webstats" >> $HESTIA/data/queue/webstats.pipe
        fi

        if [ ! -z "$STATS_USER" ]; then
            stats_dir="$HOMEDIR/$user/web/$domain/stats"
            if [ "$WEB_SYSTEM" = 'nginx' ]; then
                echo "auth_basic \"Web Statistics\";"               |user_exec tee    $stats_dir/auth.conf > /dev/null
                echo "auth_basic_user_file $stats_dir/.htpasswd;"   |user_exec tee -a $stats_dir/auth.conf > /dev/null
            else
                echo "AuthUserFile $stats_dir/.htpasswd"    |user_exec tee    $stats_dir/.htaccess > /dev/null
                echo "AuthName \"Web Statistics\""          |user_exec tee -a $stats_dir/.htaccess > /dev/null
                echo "AuthType Basic"                       |user_exec tee -a $stats_dir/.htaccess > /dev/null
                echo "Require valid-user"                   |user_exec tee -a $stats_dir/.htaccess > /dev/null
            fi
            echo "$STATS_USER:$STATS_CRYPT" |user_exec tee $stats_dir/.htpasswd > /dev/null
        fi
    fi

    # Adding ftp users
    if [ -z "$FTP_SHELL" ]; then
        shell=$(which nologin)
        if [ -e "/usr/bin/rssh" ]; then
            shell='/usr/bin/rssh'
        fi
    else
        shell=$FTP_SHELL
    fi
    for ftp_user in ${FTP_USER//:/ }; do
        if [ -z "$(grep ^$ftp_user: /etc/passwd)" ]; then
            position=$(echo $FTP_USER |tr ':' '\n' |grep -n '' |\
                grep ":$ftp_user$" |cut -f 1 -d:)
            ftp_path=$(echo $FTP_PATH |tr ':' '\n' |grep -n '' |\
                grep "^$position:" |cut -f 2 -d :)
            ftp_md5=$(echo $FTP_MD5 | tr ':' '\n' |grep -n '' |\
                grep "^$position:" |cut -f 2 -d :)

            # rebuild S/FTP users
            $BIN/v-delete-web-domain-ftp "$user" "$domain" "$ftp_user"
            $BIN/v-add-web-domain-ftp "$user" "$domain" "${ftp_user#*_}" "!xplaceholder$FTP_MD5" "$ftp_path"

            # Updating ftp user password
            chmod u+w /etc/shadow
            sed -i "s|^$ftp_user:[^:]*:|$ftp_user:$ftp_md5:|" /etc/shadow
            chmod u-w /etc/shadow
        fi
    done

    # Adding http auth protection
    htaccess="$HOMEDIR/$user/conf/web/$domain/htaccess"
    htpasswd="$HOMEDIR/$user/conf/web/$domain/htpasswd"
    docroot="$HOMEDIR/$user/web/$domain/public_html"
    for auth_user in ${AUTH_USER//:/ }; do
        # Parsing auth user variables
        position=$(echo $AUTH_USER |tr ':' '\n' |grep -n '' |\
            grep ":$auth_user$" |cut -f 1 -d:)
        auth_hash=$(echo $AUTH_HASH |tr ':' '\n' |grep -n '' |\
            grep "^$position:" |cut -f 2 -d :)

        # Adding http auth user
        touch $htpasswd
        sed -i "/^$auth_user:/d" $htpasswd
        echo "$auth_user:$auth_hash" >> $htpasswd

        # Checking web server include
        if [ ! -e "$htaccess" ]; then
            if [ "$WEB_SYSTEM" != 'nginx' ]; then
                echo "<Directory $docroot>" > $htaccess
                echo "    AuthUserFile $htpasswd" >> $htaccess
                echo "    AuthName \"$domain access\"" >> $htaccess
                echo "    AuthType Basic" >> $htaccess
                echo "    Require valid-user" >> $htaccess
                echo "</Directory>" >> $htaccess
            else
                echo "auth_basic  \"$domain password access\";" > $htaccess
                echo "auth_basic_user_file    $htpasswd;" >> $htaccess
            fi
            chmod 640 $htpasswd $htaccess >/dev/null 2>&1
        fi
    done

    # Set folder permissions
    chmod 551   $HOMEDIR/$user/web/$domain \
                $HOMEDIR/$user/web/$domain/stats \
                $HOMEDIR/$user/web/$domain/logs
    chmod 751   $HOMEDIR/$user/web/$domain/private \
                $HOMEDIR/$user/web/$domain/cgi-bin \
                $HOMEDIR/$user/web/$domain/public_html \
                $HOMEDIR/$user/web/$domain/public_shtml \
                $HOMEDIR/$user/web/$domain/document_errors
    chmod 640 /var/log/$WEB_SYSTEM/domains/$domain.*

    chown $user:www-data $HOMEDIR/$user/web/$domain/public_html \
                $HOMEDIR/$user/web/$domain/public_shtml
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

    # Set permissions
    if [ "$DNS_SYSTEM" = 'named' ]; then
        dns_group='named'
    else
        dns_group='bind'
    fi

    # Set file permissions
    chmod 640 $HOMEDIR/$user/conf/dns/$domain.db
    chown root:$dns_group $HOMEDIR/$user/conf/dns/$domain.db

    # Get dns config path
    if [ -e '/etc/named.conf' ]; then
        dns_conf='/etc/named.conf'
    fi

    if [ -e '/etc/bind/named.conf' ]; then
        dns_conf='/etc/bind/named.conf'
    fi

    # Bind config check
    if [ "$SUSPENDED" = 'yes' ]; then
        rm_string=$(grep -n /etc/namedb/$domain.db $dns_conf | cut -d : -f 1)
        if [ ! -z "$rm_string" ]; then
            sed -i "$rm_string d" $dns_conf
        fi
        suspended_dns=$((suspended_dns + 1))
    else
        if [ -z "$(grep /$domain.db $dns_conf)" ]; then
            named="zone \"$domain_idn\" {type master; file"
            named="$named \"$HOMEDIR/$user/conf/dns/$domain.db\";};"
            echo "$named" >> $dns_conf
        fi
    fi
    user_domains=$((user_domains + 1))
    records=$(wc -l $USER_DATA/dns/$domain.conf | cut -f 1 -d ' ')
    user_records=$((user_records + records))
    update_object_value 'dns' 'DOMAIN' "$domain" '$RECORDS' "$records"
}

# MAIL domain rebuild
rebuild_mail_domain_conf() {
    get_domain_values 'mail'

    if [[ "$domain" = *[![:ascii:]]* ]]; then
        domain_idn=$(idn -t --quiet -a $domain)
    else
        domain_idn=$domain
    fi

    # Inherit web domain local ip address
    unset -v nat ip local_ip domain_ip
    local domain_ip=$(get_object_value 'web' 'DOMAIN' "$domain" '$IP')
    if [ ! -z "$domain_ip" ]; then
        local local_ip=$(get_real_ip "$domain_ip")
        is_ip_valid "$local_ip" "$user"
    else
        get_user_ip
    fi

    if [ "$SUSPENDED" = 'yes' ]; then
        SUSPENDED_MAIL=$((SUSPENDED_MAIL +1))
    fi

    if [ ! -d "$USER_DATA/mail" ]; then
        rm -f $USER_DATA/mail
        mkdir $USER_DATA/mail
    fi

    # Rebuilding exim config structure
    if [[ "$MAIL_SYSTEM" =~ exim ]]; then
        rm -f /etc/$MAIL_SYSTEM/domains/$domain_idn
        mkdir -p $HOMEDIR/$user/conf/mail/$domain
        ln -s $HOMEDIR/$user/conf/mail/$domain \
            /etc/$MAIL_SYSTEM/domains/$domain_idn
        rm -f $HOMEDIR/$user/conf/mail/$domain/aliases
        rm -f $HOMEDIR/$user/conf/mail/$domain/antispam
        rm -f $HOMEDIR/$user/conf/mail/$domain/antivirus
        rm -f $HOMEDIR/$user/conf/mail/$domain/protection
        rm -f $HOMEDIR/$user/conf/mail/$domain/passwd
        rm -f $HOMEDIR/$user/conf/mail/$domain/fwd_only
        rm -f $HOMEDIR/$user/conf/mail/$domain/ip
        touch $HOMEDIR/$user/conf/mail/$domain/aliases
        touch $HOMEDIR/$user/conf/mail/$domain/passwd
        touch $HOMEDIR/$user/conf/mail/$domain/fwd_only

        # Setting outgoing ip address
        if [ ! -z "$local_ip" ]; then
            echo "$local_ip" > $HOMEDIR/$user/conf/mail/$domain/ip
        fi

        
        # Setting HELO for mail domain
        if [ ! -z "$local_ip" ]; then
            IP_RDNS=$(is_ip_rdns_valid "$local_ip")
            sed -i "/^${domain}:/d" /etc/exim4/mailhelo.conf >/dev/null 2>&1
            if [ ! -z "$IP_RDNS" ]; then
                echo ${domain}:${IP_RDNS} >> /etc/exim4/mailhelo.conf
            fi
        fi

        # Adding antispam protection
        if [ "$ANTISPAM" = 'yes' ]; then
            touch $HOMEDIR/$user/conf/mail/$domain/antispam
        fi

        # Adding antivirus protection
        if [ "$ANTIVIRUS" = 'yes' ]; then
            touch $HOMEDIR/$user/conf/mail/$domain/antivirus
        fi

        # Adding dkim
        if [ "$DKIM" = 'yes' ]; then
            cp $USER_DATA/mail/$domain.pem \
                $HOMEDIR/$user/conf/mail/$domain/dkim.pem
        fi

        # Removing configuration files if domain is suspended
        if [ "$SUSPENDED" = 'yes' ]; then
            rm -f /etc/exim/domains/$domain_idn
            rm -f /etc/dovecot/conf.d/domains/$domain_idn.conf
        fi

        # Adding mail directory
        if [ ! -e $HOMEDIR/$user/mail/$domain_idn ]; then
            mkdir "$HOMEDIR/$user/mail/$domain_idn"
        fi

        # Adding catchall email
        dom_aliases=$HOMEDIR/$user/conf/mail/$domain/aliases
        if [ ! -z "$CATCHALL" ]; then
            echo "*@$domain_idn:$CATCHALL" >> $dom_aliases
        fi
    fi

    # Rebuild domain accounts
    accs=0
    dom_disk=0
    if [ -e "$USER_DATA/mail/$domain.conf" ]; then
        accounts=$(search_objects "mail/$domain" 'SUSPENDED' "no" 'ACCOUNT')
    else
        accounts=''
    fi
    for account in $accounts; do
        (( ++accs))
        object=$(grep "ACCOUNT='$account'" $USER_DATA/mail/$domain.conf)
        FWD_ONLY='no'
        parse_object_kv_list "$object"
        if [ "$SUSPENDED" = 'yes' ]; then
            MD5='SUSPENDED'
        fi

        if [[ "$MAIL_SYSTEM" =~ exim ]]; then
            if [ "$QUOTA" = 'unlimited' ]; then
                QUOTA=0
            fi
            str="$account:$MD5:$user:mail::$HOMEDIR/$user::userdb_quota_rule=*:storage=${QUOTA}M"
            echo $str >> $HOMEDIR/$user/conf/mail/$domain/passwd
            for malias in ${ALIAS//,/ }; do
                echo "$malias@$domain_idn:$account@$domain_idn" >> $dom_aliases
            done
            if [ ! -z "$FWD" ]; then
                echo "$account@$domain_idn:$FWD" >> $dom_aliases
            fi
            if [ "$FWD_ONLY" = 'yes' ]; then
                echo "$account" >> $HOMEDIR/$user/conf/mail/$domain/fwd_only
            fi
        fi
    done

    # Set permissions and ownership
    if [[ "$MAIL_SYSTEM" =~ exim ]]; then
        chmod 660 $USER_DATA/mail/$domain.*
        chmod 771 $HOMEDIR/$user/conf/mail/$domain
        chmod 660 $HOMEDIR/$user/conf/mail/$domain/*
        chmod 771 /etc/$MAIL_SYSTEM/domains/$domain_idn
        chmod 770 $HOMEDIR/$user/mail/$domain_idn
        chown -R $MAIL_USER:mail $HOMEDIR/$user/conf/mail/$domain
        if [ "$IMAP_SYSTEM" = "dovecot" ]; then
            chown -R dovecot:mail $HOMEDIR/$user/conf/mail/$domain/passwd
        fi
        chown $user:mail $HOMEDIR/$user/mail/$domain_idn
    fi

    # Add missing SSL configuration flags to existing domains
    # for per-domain SSL migration
    sslcheck=$(grep "DOMAIN='$domain'" $USER_DATA/mail.conf | grep SSL)
    if [ -z "$sslcheck" ]; then
        sed -i "s|$domain'|$domain' SSL='no' LETSENCRYPT='no'|g" $USER_DATA/mail.conf
    fi

    # Remove and recreate SSL configuration
    if [ -f "$HOMEDIR/$user/conf/mail/$domain/ssl/$domain.crt" ]; then
        del_mail_ssl_config
        add_mail_ssl_config
        update_object_value 'mail' 'DOMAIN' "$domain" '$SSL' "yes"
    else
        update_object_value 'mail' 'DOMAIN' "$domain" '$SSL' "no"
    fi

    dom_disk=0
    for account in $(search_objects "mail/$domain" 'SUSPENDED' "no" 'ACCOUNT'); do
        home_dir=$HOMEDIR/$user/mail/$domain/$account
        if [ -e "$home_dir" ]; then
            udisk=$(nice -n 19 du -shm $home_dir | cut -f 1 )
        else
            udisk=0
        fi
        update_object_value "mail/$domain" 'ACCOUNT' "$account"  '$U_DISK' "$udisk"
        dom_disk=$((dom_disk + udisk))
    done

    update_object_value 'mail' 'DOMAIN' "$domain" '$ACCOUNTS' "$accs"
    update_object_value 'mail' 'DOMAIN' "$domain" '$U_DISK' "$dom_disk"

    # Update usage counters
    U_MAIL_ACCOUNTS=$((U_MAIL_ACCOUNTS + accs))
    U_MAIL_DOMAINS=$((U_MAIL_DOMAINS + 1))
    recalc_user_disk_usage
}

# Rebuild MySQL
rebuild_mysql_database() {
    mysql_connect $HOST
    mysql_query "CREATE DATABASE \`$DB\` CHARACTER SET $CHARSET" >/dev/null
    if [ "$mysql_fork" = "mysql" ]; then
        # mysql
        if [ "$(echo $mysql_ver |cut -d '.' -f2)" -ge 7 ]; then
            # mysql >= 5.7
            mysql_query "CREATE USER IF NOT EXISTS \`$DBUSER\`" > /dev/null
            mysql_query "CREATE USER IF NOT EXISTS \`$DBUSER\`@localhost" > /dev/null
            query="UPDATE mysql.user SET authentication_string='$MD5'"
            query="$query WHERE User='$DBUSER'"
        else
            # mysql < 5.7
            query="UPDATE mysql.user SET Password='$MD5' WHERE User='$DBUSER'"
        fi
    else
        # mariadb
        if [ "$(echo $mysql_ver |cut -d '.' -f1)" -eq 5 ]; then
            # mariadb = 5
            mysql_query "CREATE USER \`$DBUSER\`" > /dev/null
            mysql_query "CREATE USER \`$DBUSER\`@localhost" > /dev/null
        else
            # mariadb = 10
            mysql_query "CREATE USER IF NOT EXISTS \`$DBUSER\` IDENTIFIED BY PASSWORD '$MD5'" > /dev/null
            mysql_query "CREATE USER IF NOT EXISTS \`$DBUSER\`@localhost IDENTIFIED BY PASSWORD '$MD5'" > /dev/null
        fi
        # mariadb any version
        query="UPDATE mysql.user SET Password='$MD5' WHERE User='$DBUSER'"
    fi
    mysql_query "GRANT ALL ON \`$DB\`.* TO \`$DBUSER\`@\`%\`" >/dev/null
    mysql_query "GRANT ALL ON \`$DB\`.* TO \`$DBUSER\`@localhost" >/dev/null
    mysql_query "$query" >/dev/null
    mysql_query "FLUSH PRIVILEGES" >/dev/null
}

# Rebuild PostgreSQL
rebuild_pgsql_database() {

    host_str=$(grep "HOST='$HOST'" $HESTIA/conf/pgsql.conf)
    parse_object_kv_list "$host_str"
    export PGPASSWORD="$PASSWORD"
    if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ] || [ -z $TPL ]; then
        echo "Error: postgresql config parsing failed"
        if [ ! -z "$SENDMAIL" ]; then
            echo "Can't parse PostgreSQL config" | $SENDMAIL -s "$subj" $email
        fi
        log_event "$E_PARSING" "$ARGUMENTS"
        exit $E_PARSING
    fi

    query='SELECT VERSION()'
    psql -h $HOST -U $USER -c "$query" > /dev/null 2>&1
    if [ '0' -ne "$?" ];  then
        echo "Error: Connection failed"
        if [ ! -z "$SENDMAIL" ]; then
            echo "Database connection to PostgreSQL host $HOST failed" |\
                $SENDMAIL -s "$subj" $email
        fi
        log_event "$E_CONNECT" "$ARGUMENTS"
        exit $E_CONNECT
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

    query="GRANT CONNECT ON DATABASE template1 to $DBUSER"
    psql -h $HOST -U $USER -c "$query" > /dev/null 2>&1
}


# Import MySQL dump
import_mysql_database() {

    host_str=$(grep "HOST='$HOST'" $HESTIA/conf/mysql.conf)
    parse_object_kv_list "$host_str"
    if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ]; then
        echo "Error: mysql config parsing failed"
        log_event "$E_PARSING" "$ARGUMENTS"
        exit $E_PARSING
    fi

    mysql -h $HOST -u $USER -p$PASSWORD $DB < $1 > /dev/null 2>&1
}


# Import PostgreSQL dump
import_pgsql_database() {

    host_str=$(grep "HOST='$HOST'" $HESTIA/conf/pgsql.conf)
    parse_object_kv_list "$host_str"
    export PGPASSWORD="$PASSWORD"
    if [ -z $HOST ] || [ -z $USER ] || [ -z $PASSWORD ] || [ -z $TPL ]; then
        echo "Error: postgresql config parsing failed"
        log_event "$E_PARSING" "$ARGUMENTS"
        exit $E_PARSING
    fi

    psql -h $HOST -U $USER $DB < $1 > /dev/null 2>&1
}
