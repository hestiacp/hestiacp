#!/bin/bash
# lib/hestia.sh - HestiaCP Patches
#
# Patches:
#   backup.sh → b2_backup/b2_download/b2_delete use organized YYYY/MM paths
#   v-delete-user-backup → follows symlinks
#
# All patches are idempotent (safe to re-run).

# ── Patch b2 functions in backup.sh ────────────────────────────────
patch_b2_functions() {
	local script="$HESTIA/func/backup.sh"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-custom-b2-v4-retention" "$script" 2> /dev/null && return 0

	[ ! -f "${script}.original" ] && cp "$script" "${script}.original" 2> /dev/null

	if grep -q "hestia-custom-b2-v3" "$script" 2> /dev/null; then
		patch_b2_retention
		return $?
	fi

	# --- PATCH b2_backup: organized upload path ---
	# Find the upload echo line and replace the function body
	local upload_line=$(grep -n 'Upload to B2:' "$script" | head -1 | cut -d: -f1)
	if [ -n "$upload_line" ]; then
		# Insert our path calculation BEFORE the echo line
		sed -i "${upload_line}i\\
\\t# hestia-custom-b2-v3\\
\\t_b2_year=\$(date +%Y)\\
\\t_b2_month=\$(date +%m_%B | tr '[:lower:]' '[:upper:]')\\
\\t_b2_remote_path=\"\${_b2_year}/\${_b2_month}/\${user}/\${user}.\${backup_new_date}.tar\"" "$script"

		# Replace the flat upload paths with our organized path
		sed -i "s|b2 upload-file \$BUCKET \$user\.\$backup_new_date\.tar \$user/\$user\.\$backup_new_date\.tar|b2 upload-file \$BUCKET \$user.\$backup_new_date.tar \$_b2_remote_path|g" "$script"

		# Replace the echo to show organized path
		sed -i "s|Upload to B2: \$user/\$user\.\$backup_new_date\.tar|Upload to B2: \$_b2_remote_path|" "$script"

		_log "[$(date)] : Patched b2_backup() → organized upload paths"
	fi

	# --- PATCH b2_download: search organized paths ---
	local download_line=$(grep -n 'b2 download-file-by-name $BUCKET $user/$1 $1' "$script" | head -1 | cut -d: -f1)
	if [ -n "$download_line" ]; then
		sed -i "${download_line}i\\
\\t# hestia-custom-b2-v3-download: search organized paths\\
\\t_b2_remote=\$(b2 ls \$BUCKET --recursive 2>/dev/null | grep \"\$1\" | head -1)\\
\\tif [ -n \"\$_b2_remote\" ]; then\\
\\t\\tb2 download-file-by-name \$BUCKET \$_b2_remote \$1 > /dev/null 2>\&1\\
\\t\\tif [ \"\$?\" -eq 0 ]; then return 0; fi\\
\\tfi" "$script"
		_log "[$(date)] : Patched b2_download() → searches organized paths"
	fi

	# --- PATCH b2_delete: find in organized paths ---
	local delete_line=$(grep -n 'b2 delete-file-version $1/$2' "$script" | head -1 | cut -d: -f1)
	if [ -n "$delete_line" ]; then
		sed -i "${delete_line}s|b2 delete-file-version \$1/\$2|# hestia-custom-b2-v3-delete: find in organized paths\n\t_del_ids=\$(b2 ls \"\$BUCKET\" --recursive --long 2>/dev/null \| grep \"\$2\" \| awk '{print \$1}')\n\tfor _did in \$_del_ids; do b2 delete-file-version \"\$_did\" >/dev/null 2>\&1; done\n\t# fallback: b2 delete-file-version \$1/\$2|" "$script"
		_log "[$(date)] : Patched b2_delete() → finds in organized paths"
	fi

	patch_b2_retention
	return 0
}

patch_b2_retention() {
	local script="$HESTIA/func/backup.sh"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-custom-b2-v4-retention" "$script" 2> /dev/null && return 0

	local start_line
	local end_line
	local temp
	start_line=$(grep -n 'backup_list=$(b2 ls --long $BUCKET $user' "$script" | head -1 | cut -d: -f1)
	[ -z "$start_line" ] && return 1

	end_line=$(awk -v start="$start_line" 'NR > start && /^$/ { print NR; exit }' "$script")
	[ -z "$end_line" ] && return 1

	temp=$(mktemp /tmp/patch-b2-retention.XXXXXX)
	sed -n "1,$((start_line - 1))p" "$script" > "$temp"
	cat >> "$temp" << 'PATCH_EOF'
	# hestia-custom-b2-v4-retention: rotate organized cloud backups per user
	backup_list=$(b2 ls "$BUCKET" --recursive --long 2>/dev/null | awk -v user="$user" '
		$NF ~ ("(^|/)" user "/" user "\\.[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{2}-[0-9]{2}-[0-9]{2}\\.tar$") {
			archive = $NF
			sub(/^.*\//, "", archive)
			print archive "|" $1 "|" $NF
		}
	' | sort -t'|' -k1,1)
	backups_count=$(echo "$backup_list" | sed '/^$/d' | wc -l)
	if [ "$backups_count" -gt "$BACKUPS" ]; then
		backups_rm_number=$((backups_count - BACKUPS))
		echo "$backup_list" | head -n "$backups_rm_number" | while IFS='|' read -r backup_archive backup_id backup_file_name; do
			[ -z "$backup_id" ] && continue
			if b2 delete-file-version "$backup_id" > /dev/null 2>&1; then
				echo -e "$(date "+%F %T") Rotated b2 backup: $backup_file_name"
			else
				echo -e "$(date "+%F %T") Failed to rotate b2 backup: $backup_file_name"
			fi
		done
	fi
PATCH_EOF
	sed -n "$((end_line + 1)),\$p" "$script" >> "$temp"

	if grep -q "hestia-custom-b2-v4-retention" "$temp" 2> /dev/null; then
		mv "$temp" "$script" && chmod +x "$script"
		_log "[$(date)] : Patched b2_backup() -> organized retention"
		return 0
	fi

	rm -f "$temp" 2> /dev/null
	return 1
}

# ── Patch v-backup-user (Local Organization Hook) ───────────────────
patch_v_backup_user() {
	local script="$HESTIA/bin/v-backup-user"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-custom-hook-v1" "$script" 2> /dev/null && return 0

	[ ! -f "${script}.original" ] && cp "$script" "${script}.original" 2> /dev/null

	local temp=$(mktemp /tmp/patch-backup-user.XXXXXX)
	local line_num=$(grep -n 'log_event "$OK" "$ARGUMENTS"' "$script" | cut -d: -f1 | head -1)

	if [ -n "$line_num" ]; then
		sed -n "1,$((line_num - 1))p" "$script" > "$temp"
		cat >> "$temp" << 'PATCH_EOF'
# hestia-custom-hook-v1: automatic local organization
if [ -f "/usr/local/hestia/bin/v-backup-user-hook" ]; then
    bash /usr/local/hestia/bin/v-backup-user-hook "$user" "$user.$backup_new_date.tar" || true
fi

PATCH_EOF
		sed -n "$line_num,\$p" "$script" >> "$temp"

		if grep -q "hestia-custom-hook-v1" "$temp" 2> /dev/null; then
			mv "$temp" "$script" && chmod +x "$script"
			_log "[$(date)] : Patched v-backup-user (Local Organization Hook)"
			return 0
		fi
	fi
	rm -f "$temp" 2> /dev/null
	return 1
}

# ── Patch v-delete-user-backup ──────────────────────────────────────
patch_delete_command() {
	local script="$HESTIA/bin/v-delete-user-backup"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-backup-symlink-handler" "$script" 2> /dev/null && return 0

	[ ! -f "${script}.original" ] && cp "$script" "${script}.original" 2> /dev/null

	local temp=$(mktemp /tmp/patch-delete.XXXXXX)
	local line_num=$(grep -n 'rm -f "$backup_folder/$2"' "$script" | cut -d: -f1 | head -1)

	if [ -n "$line_num" ]; then
		sed -n "1,$((line_num - 1))p" "$script" > "$temp"
		cat >> "$temp" << 'PATCH_EOF'
		# hestia-backup-symlink-handler
		backup_file_path="$backup_folder/$2"
		if [ -L "$backup_file_path" ]; then
			real_file=$(readlink -f "$backup_file_path" 2>/dev/null)
			rm -f "$backup_file_path"
			[ -n "$real_file" ] && [ -f "$real_file" ] && rm -f "$real_file" "${real_file%.tar}.log" 2>/dev/null
		else
			rm -f "$backup_file_path"
		fi
PATCH_EOF
		sed -n "$((line_num + 1)),\$p" "$script" >> "$temp"

		if grep -q "hestia-backup-symlink-handler" "$temp" 2> /dev/null; then
			mv "$temp" "$script" && chmod +x "$script"
			_log "[$(date)] : Patched v-delete-user-backup (symlink-aware)"
			return 0
		fi
	fi
	rm -f "$temp" 2> /dev/null
	return 1
}

# ── Phase 0: Patch v-backup-user (Database Auto-Repair) ─────────────
patch_v_backup_user_db_repair() {
	local script="$HESTIA/bin/v-backup-user"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-custom-db-repair" "$script" 2> /dev/null && return 0

	[ ! -f "${script}.original" ] && cp "$script" "${script}.original" 2> /dev/null

	local temp=$(mktemp /tmp/patch-db-repair.XXXXXX)
	local line_num=$(grep -n 'case $TYPE in' "$script" | cut -d: -f1 | head -1)

	if [ -n "$line_num" ]; then
		sed -n "1,$((line_num - 1))p" "$script" > "$temp"
		cat >> "$temp" << 'PATCH_EOF'
			# hestia-custom-db-repair
			if [ "$TYPE" = "mysql" ]; then
				echo "$database (Auto-Check & Repair)"
				if command -v mariadb-check >/dev/null 2>&1; then
					mariadb-check --check --auto-repair "$database" >> $BACKUP/$user.log 2>&1
				elif command -v mysqlcheck >/dev/null 2>&1; then
					mysqlcheck --check --auto-repair "$database" >> $BACKUP/$user.log 2>&1
				else
					mysqlrepair --check --auto-repair "$database" >> $BACKUP/$user.log 2>&1
				fi
			fi
PATCH_EOF
		sed -n "$line_num,\$p" "$script" >> "$temp"

		if grep -q "hestia-custom-db-repair" "$temp" 2> /dev/null; then
			mv "$temp" "$script" && chmod +x "$script"
			_log "[$(date)] : Patched v-backup-user (DB Auto-Repair)"
			return 0
		fi
	fi
	rm -f "$temp" 2> /dev/null
	return 1
}

# ── Phase 1: Patch v-backup-users (Interactive Console) ─────────────
patch_v_backup_users_interactive() {
	local script="$HESTIA/bin/v-backup-users"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-custom-interactive" "$script" 2> /dev/null && return 0

	local temp=$(mktemp /tmp/patch-interactive.XXXXXX)
	local line_num=$(grep -n 'nice -n 19 ionice -c2 -n7 $BIN/v-backup-user $user >> $log' "$script" | cut -d: -f1 | head -1)

	if [ -n "$line_num" ]; then
		sed -n "1,$((line_num - 1))p" "$script" > "$temp"
		cat >> "$temp" << 'PATCH_EOF'
		# hestia-custom-interactive
		if [ -t 1 ]; then
			nice -n 19 ionice -c2 -n7 $BIN/v-backup-user $user 2>&1 | tee -a $log
		else
			nice -n 19 ionice -c2 -n7 $BIN/v-backup-user $user >> $log 2>&1
		fi
PATCH_EOF
		sed -n "$((line_num + 1)),\$p" "$script" >> "$temp"

		if grep -q "hestia-custom-interactive" "$temp" 2> /dev/null; then
			mv "$temp" "$script" && chmod +x "$script"
			_log "[$(date)] : Patched v-backup-users (Interactive Console Mode)"
			return 0
		fi
	fi
	rm -f "$temp" 2> /dev/null
	return 1
}

# ── Phase 2: Patch v-backup-user (Individual HTML Email) ────────────
patch_v_backup_user_notify() {
	local script="$HESTIA/bin/v-backup-user"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-custom-notify-hook" "$script" 2> /dev/null && return 0

	local temp=$(mktemp /tmp/patch-notify.XXXXXX)
	local line_num=$(grep -n '\$SENDMAIL -s "$subj" "$email" "$notify"' "$script" | cut -d: -f1 | head -1)

	if [ -n "$line_num" ]; then
		sed -n "1,$((line_num - 1))p" "$script" > "$temp"
		cat >> "$temp" << 'PATCH_EOF'
	# hestia-custom-notify-hook
	if [ -f "/usr/local/hestia/bin/v-backup-user-notify-hook" ]; then
		bash /usr/local/hestia/bin/v-backup-user-notify-hook "$user" "$subj" "$email" "$notify" "$BACKUP/$user.log"
	else
		cat $BACKUP/$user.log | $SENDMAIL -s "$subj" "$email" "$notify"
	fi
PATCH_EOF
		sed -n "$((line_num + 1)),\$p" "$script" >> "$temp"

		if grep -q "hestia-custom-notify-hook" "$temp" 2> /dev/null; then
			mv "$temp" "$script" && chmod +x "$script"
			_log "[$(date)] : Patched v-backup-user (Individual HTML Email)"
			return 0
		fi
	fi
	rm -f "$temp" 2> /dev/null
	return 1
}

# ── Phase 3: Patch v-backup-users (Global Master HTML Email) ────────
patch_v_backup_users_notify() {
	local script="$HESTIA/bin/v-backup-users"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-custom-notify-global-hook" "$script" 2> /dev/null && return 0

	local temp=$(mktemp /tmp/patch-global-notify.XXXXXX)
	local line_num=$(grep -n '^exit' "$script" | tail -1 | cut -d: -f1)

	if [ -n "$line_num" ]; then
		sed -n "1,$((line_num - 1))p" "$script" > "$temp"
		cat >> "$temp" << 'PATCH_EOF'
# hestia-custom-notify-global-hook
if [ -f "/usr/local/hestia/bin/v-backup-users-notify-hook" ]; then
    bash /usr/local/hestia/bin/v-backup-users-notify-hook
fi

exit
PATCH_EOF
		sed -n "$((line_num + 1)),\$p" "$script" >> "$temp"

		if grep -q "hestia-custom-notify-global-hook" "$temp" 2> /dev/null; then
			mv "$temp" "$script" && chmod +x "$script"
			_log "[$(date)] : Patched v-backup-users (Global HTML Email)"
			return 0
		fi
	fi
	rm -f "$temp" 2> /dev/null
	return 1
}

# ── Phase 4: Patch Queued Removals (Silence 'rm' cron errors) ───────
# Phase 3.1: Patch v-backup-users (Optional Retention Hook)
patch_v_backup_users_retention() {
	local script="$HESTIA/bin/v-backup-users"
	[ ! -f "$script" ] && return 1
	grep -q "hestia-custom-retention-hook" "$script" 2> /dev/null && return 0

	[ ! -f "${script}.original" ] && cp "$script" "${script}.original" 2> /dev/null

	local temp=$(mktemp /tmp/patch-retention-hook.XXXXXX)
	local line_num=$(grep -n '^exit' "$script" | tail -1 | cut -d: -f1)

	if [ -n "$line_num" ]; then
		sed -n "1,$((line_num - 1))p" "$script" > "$temp"
		cat >> "$temp" << 'PATCH_EOF'
# hestia-custom-retention-hook
if [ -f "/usr/local/hestia/bin/v-backup-users-retention-hook" ]; then
    bash /usr/local/hestia/bin/v-backup-users-retention-hook || true
fi

PATCH_EOF
		sed -n "$line_num,\$p" "$script" >> "$temp"

		if grep -q "hestia-custom-retention-hook" "$temp" 2> /dev/null; then
			mv "$temp" "$script" && chmod +x "$script"
			_log "[$(date)] : Patched v-backup-users (Optional Retention Hook)"
			return 0
		fi
	fi
	rm -f "$temp" 2> /dev/null
	return 1
}

# Phase 4: Patch Queued Removals (Silence 'rm' cron errors)
patch_cron_rm_silence() {
	local scripts=(
		"$HESTIA/bin/v-download-backup"
		"$HESTIA/bin/v-dump-database"
		"$HESTIA/bin/v-dump-site"
	)
	local patched=0
	for script in "${scripts[@]}"; do
		if [ -f "$script" ]; then
			if ! grep -q 'echo "rm -f $BACKUP' "$script" 2> /dev/null; then
				[ ! -f "${script}.original" ] && cp "$script" "${script}.original" 2> /dev/null
				sed -i 's|echo "rm \$BACKUP|echo "rm -f \$BACKUP|g' "$script"
				patched=$((patched + 1))
			fi
		fi
	done
	if [ "$patched" -gt 0 ]; then
		_log "[$(date)] : Patched $patched scripts to silence queued 'rm' tasks (added -f)"
		return 0
	fi
	return 1
}

# ── Entry point ──────────────────────────────────────────────────────
apply_hestia_patches() {
	_log "[$(date)] : Applying Hestia patches..."
	patch_delete_command
	patch_b2_functions
	patch_v_backup_user_db_repair
	patch_v_backup_user
	patch_v_backup_user_notify
	patch_v_backup_users_interactive
	patch_v_backup_users_notify
	patch_v_backup_users_retention
	patch_cron_rm_silence
}
