# Local storage
# Defining local storage function
local_backup(){

    rm -f $BACKUP/$user.$backup_new_date.tar

    # Checking retention
    backup_list=$(ls -lrt $BACKUP/ |awk '{print $9}' |grep "^$user\." | grep ".tar")
    backups_count=$(echo "$backup_list" |wc -l)
    if [ "$BACKUPS" -le "$backups_count" ]; then
        backups_rm_number=$((backups_count - BACKUPS + 1))

        # Removing old backup
        for backup in $(echo "$backup_list" |head -n $backups_rm_number); do
            backup_date=$(echo $backup |sed -e "s/$user.//" -e "s/.tar$//")
            echo -e "$(date "+%F %T") Rotated: $backup_date" |\
                tee -a $BACKUP/$user.log
            rm -f $BACKUP/$backup
        done
    fi

    # Checking disk space
    disk_usage=$(df $BACKUP |tail -n1 |tr ' ' '\n' |grep % |cut -f 1 -d %)
    if [ "$disk_usage" -ge "$BACKUP_DISK_LIMIT" ]; then
        rm -rf $tmpdir
        rm -f $BACKUP/$user.log
        sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
        echo "Not enough disk space" |$SENDMAIL -s "$subj" $email $notify
        check_result "$E_DISK" "Not enough dsk space"
    fi

    # Creating final tarball
    cd $tmpdir
    tar -cf $BACKUP/$user.$backup_new_date.tar .
    chmod 640 $BACKUP/$user.$backup_new_date.tar
    chown admin:$user $BACKUP/$user.$backup_new_date.tar
    localbackup='yes'
    echo -e "$(date "+%F %T") Local: $BACKUP/$user.$backup_new_date.tar" |\
        tee -a $BACKUP/$user.log
}

# FTP Functions 
# Defining ftp command function
ftpc() {
    /usr/bin/ftp -np $HOST $PORT <<EOF
    quote USER $USERNAME
    quote PASS $PASSWORD
    binary
    $1
    $2
    $3
    quit
EOF
}

# Defining ftp storage function
ftp_backup() {
    # Checking config
    if [ ! -e "$HESTIA/conf/ftp.backup.conf" ]; then
        error="ftp.backup.conf doesn't exist"
        rm -rf $tmpdir
        rm -f $BACKUP/$user.log
        echo "$error" |$SENDMAIL -s "$subj" $email $notify
        sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
        check_result "$E_NOTEXIST" "$error"
    fi

    # Parse config
    source $HESTIA/conf/ftp.backup.conf

    # Set default port
    if [ -z "$(grep 'PORT=' $HESTIA/conf/ftp.backup.conf)" ]; then
        PORT='21'
    fi

    # Checking variables
    if [ -z "$HOST" ] || [ -z "$USERNAME" ] || [ -z "$PASSWORD" ]; then
        error="Can't parse ftp backup configuration"
        rm -rf $tmpdir
        rm -f $BACKUP/$user.log
        echo "$error" |$SENDMAIL -s "$subj" $email $notify
        sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
        check_result "$E_PARSING" "$error"
    fi

    # Debug info
    echo -e "$(date "+%F %T") Remote: ftp://$HOST$BPATH/$user.$backup_new_date.tar"

    # Checking ftp connection
    fconn=$(ftpc)
    ferror=$(echo $fconn |grep -i -e failed -e error -e "Can't" -e "not conn")
    if [ ! -z "$ferror" ]; then
        error="Error: can't login to ftp ftp://$USERNAME@$HOST"
        rm -rf $tmpdir
        rm -f $BACKUP/$user.log
        echo "$error" |$SENDMAIL -s "$subj" $email $notify
        sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
        check_result "$E_CONNECT" "$error"
    fi

    # Check ftp permissions
    if [ -z $BPATH ]; then
            ftmpdir="vst.bK76A9SUkt"
        else
            ftpc "mkdir $BPATH" > /dev/null 2>&1
            ftmpdir="$BPATH/vst.bK76A9SUkt"
    fi
    ftpc "mkdir $ftmpdir" "rm $ftmpdir"
    ftp_result=$(ftpc "mkdir $ftmpdir" "rm $ftmpdir" |grep -v Trying)
    if [ ! -z "$ftp_result" ] ; then
        error="Can't create ftp backup folder ftp://$HOST$BPATH"
        rm -rf $tmpdir
        rm -f $BACKUP/$user.log
        echo "$error" |$SENDMAIL -s "$subj" $email $notify
        sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
        check_result "$E_FTP" "$error"
    fi

    # Checking retention
    if [ -z $BPATH ]; then
        backup_list=$(ftpc "ls" |awk '{print $9}' |grep "^$user\.")
    else
        backup_list=$(ftpc "cd $BPATH" "ls" |awk '{print $9}' |grep "^$user\.")
    fi
    backups_count=$(echo "$backup_list" |wc -l)
    if [ "$backups_count" -ge "$BACKUPS" ]; then
        backups_rm_number=$((backups_count - BACKUPS + 1))
        for backup in $(echo "$backup_list" |head -n $backups_rm_number); do 
            backup_date=$(echo $backup |sed -e "s/$user.//" -e "s/.tar$//")
            echo -e "$(date "+%F %T") Rotated ftp backup: $backup_date" |\
                tee -a $BACKUP/$user.log
            if [ -z $BPATH ]; then
                ftpc "delete $backup"
            else
                ftpc "cd $BPATH" "delete $backup"
            fi
        done
    fi

    # Uploading backup archive
    if [ "$localbackup" = 'yes' ]; then
        cd $BACKUP
        if [ -z $BPATH ]; then
            ftpc "put $user.$backup_new_date.tar"
         else
            ftpc "cd $BPATH" "put $user.$backup_new_date.tar"
        fi
    else
        cd $tmpdir
        tar -cf $BACKUP/$user.$backup_new_date.tar .
        cd $BACKUP/
        if [ -z $BPATH ]; then
            ftpc "put $user.$backup_new_date.tar"
        else
            ftpc "cd $BPATH" "put $user.$backup_new_date.tar"
        fi
        rm -f $user.$backup_new_date.tar
    fi
}

# FTP backup download function
ftp_download() {
    source $HESTIA/conf/ftp.backup.conf
    if [ -z "$PORT" ]; then
        PORT='21'
    fi
    if [ -z $BPATH ]; then
        ftpc "get $1"
    else
        ftpc "cd $BPATH" "get $1"
    fi
}

#FTP Delete function
ftp_delete() {
    source $HESTIA/conf/ftp.backup.conf
    if [ -z "$PORT" ]; then
        PORT='21'
    fi
    if [ -z $BPATH ]; then
        ftpc "delete $1"
    else
        ftpc "cd $BPATH" "delete $1"
    fi    
}


# SFTP Functions
# sftp command function
sftpc() {
    expect -f "-" <<EOF "$@"
        set timeout 60
        set count 0
        spawn /usr/bin/sftp -o StrictHostKeyChecking=no \
            -o Port=$PORT $USERNAME@$HOST
        expect {
            "password:" {
                send "$PASSWORD\r"
                exp_continue
            }

            -re "Couldn't|(.*)disconnect|(.*)stalled|(.*)not found" {
                set count \$argc
                set output "Disconnected."
                set rc $E_FTP
                exp_continue
            }

            -re ".*denied.*(publickey|password)." {
                set output "Permission denied, wrong publickey or password."
                set rc $E_CONNECT
            }

            -re "\[0-9]*%" {
                exp_continue
            }

            "sftp>" {
                if {\$count < \$argc} {
                    set arg [lindex \$argv \$count]
                    send "\$arg\r"
                    incr count
                } else {
                    send "exit\r"
                    set output "Disconnected."
                    if {[info exists rc] != 1} {
                        set rc $OK
                    }
                }
                exp_continue
            }

            timeout {
                set output "Connection timeout."
                set rc $E_CONNECT
            }
        }

        if {[info exists output] == 1} {
            puts "\$output"
        }

    exit \$rc
EOF
}

# SFTP backup download function
sftp_download() {
    source $HESTIA/conf/sftp.backup.conf
    if [ -z "$PORT" ]; then
        PORT='22'
    fi
    cd $BACKUP
    if [ -z $BPATH ]; then
        sftpc "get $1" > /dev/null 2>&1
    else
        sftpc "cd $BPATH" "get $1" > /dev/null 2>&1
    fi
}

sftp_delete() {
    echo "$1"
    source $HESTIA/conf/sftp.backup.conf
    if [ -z "$PORT" ]; then
        PORT='22'
    fi
    echo $BPATH
    if [ -z $BPATH ]; then
        sftpc "rm $1" > /dev/null 2>&1
    else
        sftpc "cd $BPATH" "rm $1" > /dev/null 2>&1
    fi
    
}

sftp_backup() {
    # Checking config
    if [ ! -e "$HESTIA/conf/sftp.backup.conf" ]; then
        error="Can't open sftp.backup.conf"
        rm -rf $tmpdir
        rm -f $BACKUP/$user.log
        echo "$error" |$SENDMAIL -s "$subj" $email $notify
        sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
        check_result "$E_NOTEXIST" "$error"
    fi

    # Parse config
    source $HESTIA/conf/sftp.backup.conf

    # Set default port
    if [ -z "$(grep 'PORT=' $HESTIA/conf/sftp.backup.conf)" ]; then
        PORT='22'
    fi

    # Checking variables
    if [ -z "$HOST" ] || [ -z "$USERNAME" ] || [ -z "$PASSWORD" ]; then
        error="Can't parse sftp backup configuration"
        rm -rf $tmpdir
        rm -f $BACKUP/$user.log
        echo "$error" |$SENDMAIL -s "$subj" $email $notify
        sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
        check_result "$E_PARSING" "$error"
    fi

    # Debug info
    echo -e "$(date "+%F %T") Remote: sftp://$HOST/$BPATH/$user.$backup_new_date.tar" |\
        tee -a $BACKUP/$user.log

    # Checking network connection and write permissions
    if [ -z $BPATH ]; then
        sftmpdir="vst.bK76A9SUkt"
    else
        sftmpdir="$BPATH/vst.bK76A9SUkt"
    fi
    sftpc "mkdir $BPATH" > /dev/null 2>&1
    sftpc "mkdir $sftmpdir" "rmdir $sftmpdir" > /dev/null 2>&1
    rc=$?
    if [[ "$rc" != 0 ]]; then
        case $rc in
            $E_CONNECT) error="Can't login to sftp host $HOST" ;;
            $E_FTP) error="Can't create temp folder on sftp $HOST" ;;
        esac
        rm -rf $tmpdir
        rm -f $BACKUP/$user.log
        echo "$error" |$SENDMAIL -s "$subj" $email $notify
        sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
        check_result "$rc" "$error"
    fi

    # Checking retention
    if [ -z $BPATH ]; then
        backup_list=$(sftpc "ls -l" |awk '{print $9}'|grep "^$user\.")
    else
        backup_list=$(sftpc "cd $BPATH" "ls -l" |awk '{print $9}'|grep "^$user\.")
    fi
    backups_count=$(echo "$backup_list" |wc -l)
    if [ "$backups_count" -ge "$BACKUPS" ]; then
        backups_rm_number=$((backups_count - BACKUPS + 1))
        for backup in $(echo "$backup_list" |head -n $backups_rm_number); do
            backup_date=$(echo $backup |sed -e "s/$user.//" -e "s/.tar.*$//")
            echo -e "$(date "+%F %T") Rotated sftp backup: $backup_date" |\
                tee -a $BACKUP/$user.log
            if [ -z $BPATH ]; then
                sftpc "rm $backup" > /dev/null 2>&1
            else
                sftpc "cd $BPATH" "rm $backup" > /dev/null 2>&1
            fi
        done
    fi

    # Uploading backup archive
    echo "$(date "+%F %T") Uploading $user.$backup_new_date.tar"|tee -a $BACKUP/$user.log
    if [ "$localbackup" = 'yes' ]; then
        cd $BACKUP
        if [ -z $BPATH ]; then
            sftpc "put $user.$backup_new_date.tar" "chmod 0600 $user.$backup_new_date.tar" > /dev/null 2>&1
        else
            sftpc "cd $BPATH" "put $user.$backup_new_date.tar" "chmod 0600 $user.$backup_new_date.tar" > /dev/null 2>&1
        fi
    else
        cd $tmpdir
        tar -cf $BACKUP/$user.$backup_new_date.tar .
        cd $BACKUP/
        if [ -z $BPATH ]; then
            sftpc "put $user.$backup_new_date.tar" "chmod 0600 $user.$backup_new_date.tar" > /dev/null 2>&1
        else
            sftpc "cd $BPATH" "put $user.$backup_new_date.tar" "chmod 0600 $user.$backup_new_date.tar" > /dev/null 2>&1
        fi
        rm -f $user.$backup_new_date.tar
    fi
}

# Google backup download function
google_backup() {

    # Defining google settings
    source $HESTIA/conf/google.backup.conf
    gsutil="$HESTIA/3rdparty/gsutil/gsutil"
    export BOTO_CONFIG="$HESTIA/conf/.google.backup.boto"

    # Debug info
    echo -e "$(date "+%F %T") Remote: gs://$BUCKET/$BPATH/$user.$backup_new_date.tar"

    # Checking retention
    backup_list=$(${gsutil} ls gs://$BUCKET/$BPATH/$user.* 2>/dev/null)
    backups_count=$(echo "$backup_list" |wc -l)
    if [ "$backups_count" -ge "$BACKUPS" ]; then
        backups_rm_number=$((backups_count - BACKUPS))
        for backup in $(echo "$backup_list" |head -n $backups_rm_number); do 
            echo -e "$(date "+%F %T") Rotated gcp backup: $backup"
            $gsutil rm $backup > /dev/null 2>&1
        done
    fi

    # Uploading backup archive
    echo -e "$(date "+%F %T") Uploading $user.$backup_new_date.tar ..."
    if [ "$localbackup" = 'yes' ]; then
        cd $BACKUP
        ${gsutil} cp $user.$backup_new_date.tar gs://$BUCKET/$BPATH/ > /dev/null 2>&1
    else
        cd $tmpdir
        tar -cf $BACKUP/$user.$backup_new_date.tar .
        cd $BACKUP/
        ${gsutil} cp $user.$backup_new_date.tar gs://$BUCKET/$BPATH/ > /dev/null 2>&1
        rc=$?
        rm -f $user.$backup_new_date.tar
        if [ "$rc" -ne 0 ]; then
            check_result "$E_CONNECT" "gsutil failed to upload $user.$backup_new_date.tar"
        fi
    fi
}

google_download() {
    source $HESTIA/conf/google.backup.conf
    gsutil="$HESTIA/3rdparty/gsutil/gsutil"
    export BOTO_CONFIG="$HESTIA/conf/.google.backup.boto"
    ${gsutil} cp gs://$BUCKET/$BPATH/$1 $BACKUP/ > /dev/null 2>&1
    if [ "$?" -ne 0 ]; then
        check_result "$E_CONNECT" "gsutil failed to download $1"
    fi
} 