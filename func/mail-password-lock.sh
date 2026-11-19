#!/usr/bin/env bash

# The stable gate is outside user/domain directories so deletion, ownership
# transfers, and restores cannot replace the inode while a migration uses it.
# Keep the descriptor open until this CLI and its nested commands have exited.
# All cooperating administrative commands take SHARED locks, not exclusive
# locks: a child command must never wait for an exclusive lock held by a parent.
lock_mail_password_writes() {
	local path="$HESTIA/data/.mail-password-upgrade.lock"
	local fd old_umask
	if [[ -L "$path" ]] || { [[ -e "$path" ]] \
		&& { [[ ! -f "$path" ]] || [[ "$(stat -c %h -- "$path")" != 1 ]]; }; }; then
		check_result "$E_PARSING" "Unsafe mail password upgrade lock"
	fi
	old_umask=$(umask)
	umask 077
	if ! exec {fd}>> "$path"; then
		umask "$old_umask"
		check_result "$E_PARSING" "Cannot open mail password upgrade lock"
	fi
	umask "$old_umask"
	if [[ -L "$path" ]] || [[ ! -f "$path" ]] \
		|| [[ "$(stat -Lc '%d:%i:%h' -- "/proc/self/fd/$fd")" != "$(stat -c '%d:%i:1' -- "$path")" ]]; then
		check_result "$E_PARSING" "Unsafe mail password upgrade lock"
	fi
	chmod 600 "/proc/self/fd/$fd"
	check_result "$?" "Cannot secure mail password upgrade lock"
	flock --shared --timeout 30 "$fd"
	check_result "$?" "Mail password upgrade busy"
}
