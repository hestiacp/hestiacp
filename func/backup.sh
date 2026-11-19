#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - Backup Function Library                            #
#                                                                           #
#===========================================================================#

# Execute a remote backup command with configurable retry attempts and delay.
# Honors BACKUP_REMOTE_RETRIES (default: 3) and BACKUP_REMOTE_RETRY_DELAY
# (default: 10 seconds, linear backoff) from hestia.conf or *.backup.conf.
remote_retry_exec() {
	local label="$1"
	shift
	local max_attempts="${BACKUP_REMOTE_RETRIES:-3}"
	local base_delay="${BACKUP_REMOTE_RETRY_DELAY:-10}"
	max_attempts="${max_attempts//[[:space:]]/}"
	base_delay="${base_delay//[[:space:]]/}"
	[[ "$max_attempts" =~ ^[1-9][0-9]*$ ]] || max_attempts=3
	[[ "$base_delay" =~ ^[0-9]+$ ]] || base_delay=10

	local attempt=1
	local rc=0
	local wait_sec=0
	while true; do
		"$@"
		rc=$?
		if [ "$rc" -eq 0 ]; then
			return 0
		fi
		if [ "$attempt" -ge "$max_attempts" ]; then
			return "$rc"
		fi
		wait_sec=$((base_delay * attempt))
		if [ -n "${BACKUP:-}" ] && [ -n "${user:-}" ] && [ -d "$BACKUP" ]; then
			echo -e "$(date "+%F %T") Warning: $label failed (exit $rc), retrying ($((attempt + 1))/$max_attempts) in ${wait_sec}s..." \
				| tee -a "$BACKUP/$user.log"
		else
			echo -e "$(date "+%F %T") Warning: $label failed (exit $rc), retrying ($((attempt + 1))/$max_attempts) in ${wait_sec}s..."
		fi
		sleep "$wait_sec"
		attempt=$((attempt + 1))
	done
}

# Local storage
# Defining local storage function
local_backup() {

	rm -f $BACKUP/$user.$backup_new_date.tar

	# Checking retention
	backup_list=$(ls -lrt $BACKUP/ | awk '{print $9}' | grep -E "^${user}\.[0-9]{4}-.+\.tar$" | sort)
	backups_count=$(echo "$backup_list" | wc -l)
	if [ "$BACKUPS" -le "$backups_count" ]; then
		backups_rm_number=$((backups_count - BACKUPS + 1))

		# Removing old backup
		for backup in $(echo "$backup_list" | head -n $backups_rm_number); do
			backup_date=$(echo $backup | sed -e "s/$user.//" -e "s/.tar$//")
			echo -e "$(date "+%F %T") Rotated: $backup_date" \
				| tee -a $BACKUP/$user.log
			rm -f $BACKUP/$backup
		done
	fi

	# Checking disk space
	disk_usage=$(df $BACKUP | tail -n1 | tr ' ' '\n' | grep % | cut -f 1 -d %)
	if [ "$disk_usage" -ge "$BACKUP_DISK_LIMIT" ]; then
		rm -rf $tmpdir
		rm -f $BACKUP/$user.log
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "Not enough disk space" | $SENDMAIL -s "$subj" "$email" "yes"
		check_result "$E_DISK" "Not enough dsk space"
	fi

	# Creating final tarball
	cd $tmpdir
	tar -cf $BACKUP/$user.$backup_new_date.tar .
	chmod 640 $BACKUP/$user.$backup_new_date.tar
	chown "hestiaweb":"$user" $BACKUP/$user.$backup_new_date.tar
	localbackup='yes'
	echo -e "$(date "+%F %T") Local: $BACKUP/$user.$backup_new_date.tar" \
		| tee -a $BACKUP/$user.log
}

# FTP Functions
# Defining ftp command function
ftpc() {
	/usr/bin/ftp -np $HOST $PORT << EOF
    quote USER $USERNAME
    quote PASS $PASSWORD
    binary
    $1
    $2
    $3
    quit
EOF
}

# Execute FTP commands in verbose mode so 4xx/5xx server replies and dropped
# connections return non-zero exit codes instead of failing silently.
ftpc_checked() {
	local ftp_out rc
	ftp_out=$(
		/usr/bin/ftp -nvp "$HOST" "$PORT" 2>&1 << EOF
    quote USER $USERNAME
    quote PASS $PASSWORD
    binary
    ${1:-}
    ${2:-}
    ${3:-}
    quit
EOF
	)
	rc=$?
	if [ "$rc" -ne 0 ]; then
		return "$E_CONNECT"
	fi
	if printf '%s\n' "$ftp_out" | grep -vE '^[0-9]+ bytes (sent|received) in ' | grep -qiE '^([45][0-9]{2}[ -]|ftp:|not connected|lost connection|passive mode refused|login failed|transfer incomplete)'; then
		if printf '%s\n' "$ftp_out" | grep -qiE '^(530[ -]|ftp:.*connect|not connected)'; then
			return "$E_CONNECT"
		fi
		return "$E_FTP"
	fi
	if ! printf '%s\n' "$ftp_out" | grep -qE '^230[ -]'; then
		return "$E_CONNECT"
	fi
	case "${1:-} ${2:-} ${3:-}" in
		*"put "* | *"get "*)
			if ! printf '%s\n' "$ftp_out" | grep -qE '^226[ -]'; then
				return "$E_FTP"
			fi
			;;
	esac
	return 0
}

# Defining ftp storage function
ftp_backup() {
	local BACKUP_REMOTE_RETRIES="${BACKUP_REMOTE_RETRIES:-}"
	local BACKUP_REMOTE_RETRY_DELAY="${BACKUP_REMOTE_RETRY_DELAY:-}"
	local rc=0

	# Checking config
	if [ ! -e "$HESTIA/conf/ftp.backup.conf" ]; then
		error="ftp.backup.conf doesn't exist"
		echo "$error" | $SENDMAIL -s "$subj" $email "yes"
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$E_NOTEXIST"
		return "$E_NOTEXIST"
	fi

	# Parse config
	source_conf "$HESTIA/conf/ftp.backup.conf"

	# Set default port
	if [ -z "$(grep 'PORT=' $HESTIA/conf/ftp.backup.conf)" ]; then
		PORT='21'
	fi

	# Checking variables
	if [ -z "$HOST" ] || [ -z "$USERNAME" ] || [ -z "$PASSWORD" ]; then
		error="Can't parse ftp backup configuration"
		echo "$error" | $SENDMAIL -s "$subj" $email "yes"
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$E_PARSING"
		return "$E_PARSING"
	fi

	# Debug info
	echo -e "$(date "+%F %T") Remote: ftp://$HOST$BPATH/$user.$backup_new_date.tar"

	# Checking ftp connection
	if ! remote_retry_exec "FTP connection check ($HOST)" ftpc_checked; then
		error="Error: can't login to ftp ftp://$USERNAME@$HOST"
		echo "$error" | $SENDMAIL -s "$subj" $email $notify
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$E_CONNECT"
		return "$E_CONNECT"
	fi

	# Check ftp permissions
	if [ -z $BPATH ]; then
		ftmpdir="vst.bK76A9SUkt"
	else
		ftpc "mkdir $BPATH" > /dev/null 2>&1
		ftmpdir="$BPATH/vst.bK76A9SUkt"
	fi
	ftpc "mkdir $ftmpdir" "rm $ftmpdir"
	ftp_result=$(ftpc "mkdir $ftmpdir" "rm $ftmpdir" | grep -v Trying)
	if [ -n "$ftp_result" ]; then
		error="Can't create ftp backup folder ftp://$HOST$BPATH"
		echo "$error" | $SENDMAIL -s "$subj" $email $notify
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$E_FTP"
		return "$E_FTP"
	fi

	# Checking retention (Only include .tar files)
	if [ -z $BPATH ]; then
		backup_list=$(ftpc "ls" | awk '{print $9}' | grep -E "^${user}\.[0-9]{4}-.+\.tar$" | sort)
	else
		backup_list=$(ftpc "cd $BPATH" "ls" | awk '{print $9}' | grep -E "^${user}\.[0-9]{4}-.+\.tar$" | sort)
	fi
	backups_count=$(echo "$backup_list" | wc -l)
	if [ "$backups_count" -ge "$BACKUPS" ]; then
		backups_rm_number=$((backups_count - BACKUPS + 1))
		for backup in $(echo "$backup_list" | head -n $backups_rm_number); do
			backup_date=$(echo $backup | sed -e "s/$user.//" -e "s/.tar$//")
			echo -e "$(date "+%F %T") Rotated ftp backup: $backup_date" \
				| tee -a $BACKUP/$user.log
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
			remote_retry_exec "FTP upload ($user.$backup_new_date.tar)" ftpc_checked "put $user.$backup_new_date.tar"
			rc=$?
		else
			remote_retry_exec "FTP upload ($user.$backup_new_date.tar)" ftpc_checked "cd $BPATH" "put $user.$backup_new_date.tar"
			rc=$?
		fi
	else
		cd $tmpdir
		tar -cf $BACKUP/$user.$backup_new_date.tar .
		cd $BACKUP/
		if [ -z $BPATH ]; then
			remote_retry_exec "FTP upload ($user.$backup_new_date.tar)" ftpc_checked "put $user.$backup_new_date.tar"
			rc=$?
		else
			remote_retry_exec "FTP upload ($user.$backup_new_date.tar)" ftpc_checked "cd $BPATH" "put $user.$backup_new_date.tar"
			rc=$?
		fi
		rm -f $user.$backup_new_date.tar
	fi
	if [ "$rc" -ne 0 ]; then
		error="Can't upload backup archive $user.$backup_new_date.tar to ftp://$HOST$BPATH"
		echo "$error" | $SENDMAIL -s "$subj" $email $notify
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$rc"
		return "$rc"
	fi
}

# FTP backup download function
ftp_download() {
	source_conf "$HESTIA/conf/ftp.backup.conf"
	if [ -z "$PORT" ]; then
		PORT='21'
	fi
	cd $BACKUP
	if [ -z $BPATH ]; then
		ftpc "get $1"
	else
		ftpc "cd $BPATH" "get $1"
	fi
}

#FTP Delete function
ftp_delete() {
	source_conf "$HESTIA/conf/ftp.backup.conf"
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
	if [ "$PRIVATEKEY" != "yes" ]; then
		expect -f "-" "$@" << EOF
            set timeout 60
            set count 0
            spawn /usr/bin/sftp -o StrictHostKeyChecking=no \
                -o Port=$PORT $USERNAME@$HOST
            expect {
                -nocase "password:" {
                    send "$PASSWORD\r"
                    exp_continue
                }

                -re "Password for (.*)@(.*)" {
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
	else

		expect -f "-" "$@" << EOF
            set timeout 60
            set count 0
            spawn /usr/bin/sftp -o StrictHostKeyChecking=no \
                -o Port=$PORT -i $PASSWORD $USERNAME@$HOST
            expect {
                -nocase "password:" {
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

	fi
}

# SFTP backup download function
sftp_download() {
	source_conf "$HESTIA/conf/sftp.backup.conf"
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
	source_conf "$HESTIA/conf/sftp.backup.conf"
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

sftpc_quiet() {
	sftpc "$@" > /dev/null 2>&1
}

sftp_check_dir() {
	if [ -n "$BPATH" ]; then
		sftpc "mkdir $BPATH" > /dev/null 2>&1
	fi
	sftpc "mkdir $1" "rmdir $1" > /dev/null 2>&1
}

sftp_backup() {
	local BACKUP_REMOTE_RETRIES="${BACKUP_REMOTE_RETRIES:-}"
	local BACKUP_REMOTE_RETRY_DELAY="${BACKUP_REMOTE_RETRY_DELAY:-}"
	local rc=0

	# Checking config
	if [ ! -e "$HESTIA/conf/sftp.backup.conf" ]; then
		error="Can't open sftp.backup.conf"
		echo "$error" | $SENDMAIL -s "$subj" $email "yes"
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$E_NOTEXIST"
		return "$E_NOTEXIST"
	fi

	# Parse config
	source_conf "$HESTIA/conf/sftp.backup.conf"

	# Set default port
	if [ -z "$(grep 'PORT=' $HESTIA/conf/sftp.backup.conf)" ]; then
		PORT='22'
	fi

	# Checking variables
	if [ -z "$HOST" ] || [ -z "$USERNAME" ] || [ -z "$PASSWORD" ]; then
		error="Can't parse sftp backup configuration"
		echo "$error" | $SENDMAIL -s "$subj" $email "yes"
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$E_PARSING"
		return "$E_PARSING"
	fi

	# Debug info
	echo -e "$(date "+%F %T") Remote: sftp://$HOST/$BPATH/$user.$backup_new_date.tar" \
		| tee -a $BACKUP/$user.log

	# Checking network connection and write permissions
	if [ -z $BPATH ]; then
		sftmpdir="vst.bK76A9SUkt"
	else
		sftmpdir="$BPATH/vst.bK76A9SUkt"
	fi
	remote_retry_exec "SFTP connection check ($HOST)" sftp_check_dir "$sftmpdir"
	rc=$?
	if [[ "$rc" != 0 ]]; then
		case $rc in
			$E_CONNECT) error="Can't login to sftp host $HOST" ;;
			$E_FTP) error="Can't create temp folder on sftp $HOST" ;;
		esac
		echo "$error" | $SENDMAIL -s "$subj" $email "yes"
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$rc"
		return "$rc"
	fi

	# Checking retention (Only include .tar files)
	if [ -z $BPATH ]; then
		backup_list=$(sftpc "ls -l" | tr -d '\r' | awk '{print $9}' | grep -E "^${user}\.[0-9]{4}-.+\.tar$" | sort)
	else
		backup_list=$(sftpc "cd $BPATH" "ls -l" | tr -d '\r' | awk '{print $9}' | grep -E "^${user}\.[0-9]{4}-.+\.tar$" | sort)
	fi
	if [ -z "$backup_list" ]; then
		backups_count=0
	else
		backups_count=$(echo "$backup_list" | wc -l)
	fi
	if [ "$backups_count" -ge "$BACKUPS" ]; then
		backups_rm_number=$((backups_count - BACKUPS + 1))
		for backup in $(echo "$backup_list" | head -n $backups_rm_number); do
			backup_date=$(echo $backup | sed -e "s/$user.//" -e "s/.tar.*$//")
			echo -e "$(date "+%F %T") Rotated sftp backup: $backup_date" \
				| tee -a $BACKUP/$user.log
			if [ -z $BPATH ]; then
				sftpc "rm $backup" > /dev/null 2>&1
			else
				sftpc "cd $BPATH" "rm $backup" > /dev/null 2>&1
			fi
		done
	fi

	# Uploading backup archive
	echo "$(date "+%F %T") Uploading $user.$backup_new_date.tar" | tee -a $BACKUP/$user.log
	if [ "$localbackup" = 'yes' ]; then
		cd $BACKUP
		if [ -z $BPATH ]; then
			remote_retry_exec "SFTP upload ($user.$backup_new_date.tar)" sftpc_quiet "put $user.$backup_new_date.tar" "chmod 0600 $user.$backup_new_date.tar"
			rc=$?
		else
			remote_retry_exec "SFTP upload ($user.$backup_new_date.tar)" sftpc_quiet "cd $BPATH" "put $user.$backup_new_date.tar" "chmod 0600 $user.$backup_new_date.tar"
			rc=$?
		fi
	else
		cd $tmpdir
		tar -cf $BACKUP/$user.$backup_new_date.tar .
		cd $BACKUP/
		if [ -z $BPATH ]; then
			remote_retry_exec "SFTP upload ($user.$backup_new_date.tar)" sftpc_quiet "put $user.$backup_new_date.tar" "chmod 0600 $user.$backup_new_date.tar"
			rc=$?
		else
			remote_retry_exec "SFTP upload ($user.$backup_new_date.tar)" sftpc_quiet "cd $BPATH" "put $user.$backup_new_date.tar" "chmod 0600 $user.$backup_new_date.tar"
			rc=$?
		fi
		rm -f $user.$backup_new_date.tar
	fi
	if [ "$rc" -ne 0 ]; then
		error="Can't upload backup archive $user.$backup_new_date.tar to sftp://$HOST/$BPATH"
		echo "$error" | $SENDMAIL -s "$subj" $email "yes"
		sed -i "/ $user /d" $HESTIA/data/queue/backup.pipe
		echo "$error"
		errorcode="$rc"
		return "$rc"
	fi
}

b2_upload_quiet() {
	b2 file upload "$@" > /dev/null 2>&1
}

# BackBlaze B2 backup function
b2_backup() {
	local BACKUP_REMOTE_RETRIES="${BACKUP_REMOTE_RETRIES:-}"
	local BACKUP_REMOTE_RETRY_DELAY="${BACKUP_REMOTE_RETRY_DELAY:-}"
	local rc=0

	# Defining backblaze b2 settings
	source_conf "$HESTIA/conf/b2.backup.conf"

	# Recreate backblaze auth file ~/.b2_account_info (for situation when key was changed in b2.backup.conf)
	b2 account clear > /dev/null 2>&1
	b2 account authorize $B2_KEYID $B2_KEY > /dev/null 2>&1

	# Uploading backup archive
	echo -e "$(date "+%F %T") Upload to B2: $user/$user.$backup_new_date.tar"
	if [ "$localbackup" = 'yes' ]; then
		cd $BACKUP
		remote_retry_exec "B2 upload ($user.$backup_new_date.tar)" b2_upload_quiet "$BUCKET" "$user.$backup_new_date.tar" "$user/$user.$backup_new_date.tar"
		rc=$?
		if [ "$rc" -ne 0 ]; then
			errorcode="$E_CONNECT"
			return "$E_CONNECT"
		fi
	else
		cd $tmpdir
		tar -cf $BACKUP/$user.$backup_new_date.tar .
		cd $BACKUP/
		remote_retry_exec "B2 upload ($user.$backup_new_date.tar)" b2_upload_quiet "$BUCKET" "$user.$backup_new_date.tar" "$user/$user.$backup_new_date.tar"
		rc=$?
		rm -f $user.$backup_new_date.tar
		if [ "$rc" -ne 0 ]; then
			check_result "$E_CONNECT" "b2 failed to upload $user.$backup_new_date.tar"
		fi
	fi

	# Checking retention
	backup_list=$(b2 ls --json "b2://$BUCKET/$user" 2> /dev/null | jq -r '.[].fileId' 2> /dev/null)
	backups_count=$(echo "$backup_list" | wc -l)
	if [ "$backups_count" -ge "$BACKUPS" ]; then
		backups_rm_number=$(($backups_count - $BACKUPS))
		for backup in $(echo "$backup_list" | head -n $backups_rm_number); do
			backup_file_name=$(b2 file info "b2id://$backup" 2> /dev/null | jq -r '.fileName' 2> /dev/null)
			echo -e "$(date "+%F %T") Rotated b2 backup: $backup_file_name"
			b2 rm "b2id://$backup" > /dev/null 2>&1
		done
	fi

}

b2_download() {
	# Defining backblaze b2 settings
	source_conf "$HESTIA/conf/b2.backup.conf"

	# Recreate backblaze auth file ~/.b2_account_info (for situation when key was changed in b2.backup.conf)
	b2 account clear > /dev/null 2>&1
	b2 account authorize $B2_KEYID $B2_KEY > /dev/null 2>&1
	cd $BACKUP
	b2 file download "b2://$BUCKET/$user/$1" "$1" > /dev/null 2>&1
	if [ "$?" -ne 0 ]; then
		check_result "$E_CONNECT" "b2 failed to download $user.$1"
	fi
}

b2_delete() {
	# Defining backblaze b2 settings
	source_conf "$HESTIA/conf/b2.backup.conf"

	# Recreate backblaze auth file ~/.b2_account_info (for situation when key was changed in b2.backup.conf)
	b2 account clear > /dev/null 2>&1
	b2 account authorize $B2_KEYID $B2_KEY > /dev/null 2>&1

	b2 rm b2://$BUCKET/$1/$2 > /dev/null 2>&1
}

rclone_backup() {
	local BACKUP_REMOTE_RETRIES="${BACKUP_REMOTE_RETRIES:-}"
	local BACKUP_REMOTE_RETRY_DELAY="${BACKUP_REMOTE_RETRY_DELAY:-}"
	local rc=0

	# Define rclone config
	source_conf "$HESTIA/conf/rclone.backup.conf"
	echo -e "$(date "+%F %T") Upload With Rclone to $HOST: $user.$backup_new_date.tar"
	if [ "$localbackup" != 'yes' ]; then
		cd $tmpdir
		tar -cf $BACKUP/$user.$backup_new_date.tar .
	fi
	cd $BACKUP/

	if [ -z "$BPATH" ]; then
		remote_retry_exec "Rclone upload ($user.$backup_new_date.tar)" rclone copy -v "$user.$backup_new_date.tar" "$HOST:$backup"
		rc=$?
		if [ "$rc" -ne 0 ]; then
			if [ "$localbackup" != 'yes' ]; then
				rm -f "$user.$backup_new_date.tar"
				check_result "$E_CONNECT" "Unable to upload backup"
			fi
			errorcode="$E_CONNECT"
			return "$E_CONNECT"
		fi

		# Only include *.tar files
		backup_list=$(rclone lsf $HOST: | cut -d' ' -f1 | grep -E "^${user}\.[0-9]{4}-.+\.tar$" | sort)
		backups_count=$(echo "$backup_list" | wc -l)
		backups_rm_number=$((backups_count - BACKUPS))
		if [ "$backups_count" -ge "$BACKUPS" ]; then
			for backup in $(echo "$backup_list" | head -n $backups_rm_number); do
				echo "Delete file: $backup"
				rclone deletefile $HOST:/$backup
			done
		fi
	else
		remote_retry_exec "Rclone upload ($user.$backup_new_date.tar)" rclone copy -v "$user.$backup_new_date.tar" "$HOST:$BPATH"
		rc=$?
		if [ "$rc" -ne 0 ]; then
			if [ "$localbackup" != 'yes' ]; then
				rm -f "$user.$backup_new_date.tar"
				check_result "$E_CONNECT" "Unable to upload backup"
			fi
			errorcode="$E_CONNECT"
			return "$E_CONNECT"
		fi

		# Only include *.tar files
		backup_list=$(rclone lsf $HOST:$BPATH | cut -d' ' -f1 | grep -E "^${user}\.[0-9]{4}-.+\.tar$" | sort)
		backups_count=$(echo "$backup_list" | wc -l)
		backups_rm_number=$(($backups_count - $BACKUPS))
		if [ "$backups_count" -ge "$BACKUPS" ]; then
			for backup in $(echo "$backup_list" | head -n $backups_rm_number); do
				echo "Delete file: $backup"
				rclone deletefile $HOST:$BPATH/$backup
			done
		fi
	fi
	if [ "$localbackup" != 'yes' ]; then
		rm -f $user.$backup_new_date.tar
	fi

}

rclone_delete() {
	# Defining rclone settings
	source_conf "$HESTIA/conf/rclone.backup.conf"
	if [ -z "$BPATH" ]; then
		rclone deletefile $HOST:/$1
	else
		rclone deletefile $HOST:$BPATH/$1
	fi
}

rclone_download() {

	# Defining rclone b2 settings
	source_conf "$HESTIA/conf/rclone.backup.conf"
	cd $BACKUP
	if [ -z "$BPATH" ]; then
		rclone copy -v $HOST:/$1 ./
	else
		rclone copy -v $HOST:$BPATH/$1 ./
	fi
}
