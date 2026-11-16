#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.8

#######################################################################################
#######                      Place additional commands below.                   #######
#######################################################################################
####### upgrade_config_set_value only accepts true or false.                    #######
#######                                                                         #######
####### Pass through information to the end user in case of a issue or problem  #######
#######                                                                         #######
####### Use add_upgrade_message "My message here" to include a message          #######
####### in the upgrade notification email. Example:                             #######
#######                                                                         #######
####### add_upgrade_message "My message here"                                   #######
#######                                                                         #######
####### You can use \n within the string to create new lines.                   #######
#######################################################################################

upgrade_config_set_value 'UPGRADE_UPDATE_WEB_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_DNS_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_FILEMANAGER_CONFIG' 'false'
upgrade_config_set_value 'UPGRADE_UPDATE_MAIL_TEMPLATES' 'false'
upgrade_config_set_value 'UPGRADE_REBUILD_USERS' 'false'

# Apply the per-user package RATE_LIMIT to sendmail / PHP mail() in Exim (#5324).
# Replaces the stock acl_not_smtp block of the installed template with the one
# shipped in the new template. A customised block is never touched. Exim is not
# restarted here, the upgrade restarts the mail services at the end.
# usage: patch_exim_not_smtp_ratelimit [installed_template] [shipped_template]
patch_exim_not_smtp_ratelimit() {
	local conf="${1:-/etc/exim4/exim4.conf.template}"
	local shipped="${2:-$HESTIA_INSTALL_DIR/exim/exim4.conf.template}"
	local stock cur new bak n tmp vdir

	[[ -f "$conf" && -f "$shipped" ]] || return 0

	# _acl_block prints the acl_not_smtp block (up to the next top level label),
	# _acl_block_norm the same with whitespace collapsed and blank lines dropped.
	_acl_block() {
		awk '/^acl_not_smtp:/ { f = 1; print; next } f && /^[A-Za-z_][A-Za-z0-9_]*:/ { exit } f' "$1"
	}
	_acl_block_norm() {
		_acl_block "$1" | sed 's/[[:space:]]\+/ /g; s/^ //; s/ $//; /^$/d'
	}

	stock=$(
		cat << 'EOF'
acl_not_smtp:
deny message = Website of user $authenticated_id is sending too many emails - rate overlimit = $sender_rate / $sender_rate_period
ratelimit = 200 / 1h / $authenticated_id
warn ratelimit = 100 / 1h / strict / $authenticated_id
log_message = Sender rate [limitlog]: log / account / $authenticated_id / $sender_rate / $sender_rate_period
accept
EOF
	)
	cur=$(_acl_block_norm "$conf")
	new=$(_acl_block_norm "$shipped")

	if [[ -z "$new" ]]; then
		echo "[ ! ] Exim: no acl_not_smtp block in the shipped template, not patched"
		return 0
	fi
	if [[ "$cur" == "$new" ]]; then
		return 0
	fi
	if [[ "$cur" != "$stock" ]]; then
		echo "[ ! ] Exim: custom acl_not_smtp block in $conf, not patched (per-user RATE_LIMIT is not applied to PHP mail())"
		add_upgrade_message "Exim: the acl_not_smtp block in $conf is customised and was not updated, so the package RATE_LIMIT is not applied to sendmail and PHP mail(). Replace it with the block from /usr/local/hestia/install/deb/exim/exim4.conf.template, then run update-exim4.conf and restart Exim."
		return 0
	fi

	echo "[ + ] Applying per-user mail rate limit to Exim acl_not_smtp"

	# Unique backup name, never overwrite an existing backup
	bak="$conf.$(date +%Y%m%d-%H%M%S).before_5324.bak"
	n=0
	while [[ -e "$bak" ]]; do
		n=$((n + 1))
		bak="$conf.$(date +%Y%m%d-%H%M%S).before_5324.$n.bak"
	done
	if ! cp -p -- "$conf" "$bak"; then
		echo "[ ! ] Exim: could not create a backup, nothing was modified"
		return 0
	fi

	# Build the candidate: installed template with the new block swapped in.
	# Every step must succeed and the result must carry the new block.
	tmp=$(mktemp "$conf.XXXXXX") || return 0
	vdir=$(mktemp -d) || {
		rm -f "$tmp"
		return 0
	}
	if ! {
		_acl_block "$shipped" > "$vdir/block" && [[ -s "$vdir/block" ]] \
			&& cp -p -- "$conf" "$tmp" \
			&& awk '
				NR == FNR { nb[++n] = $0; next }
				/^acl_not_smtp:/ { for (i = 1; i <= n; i++) print nb[i]; skip = 1; next }
				skip && /^[A-Za-z_][A-Za-z0-9_]*:/ { skip = 0 }
				!skip { print }
			' "$vdir/block" "$conf" > "$tmp" \
			&& [[ "$(_acl_block_norm "$tmp")" == "$new" ]]
	}; then
		echo "[ ! ] Exim: could not build the patched configuration, nothing was changed"
		rm -f "$tmp"
		rm -rf "$vdir"
		return 0
	fi

	# Validate the candidate before it goes anywhere near the live config
	cp -- "$tmp" "$vdir/exim4.conf.template"
	cp -- "$(dirname "$conf")/update-exim4.conf.conf" "$vdir/" 2> /dev/null
	cp -- "$(dirname "$conf")/exim4.conf.localmacros" "$vdir/" 2> /dev/null
	if ! { update-exim4.conf --confdir "$vdir" --output "$vdir/exim4.conf" \
		&& exim4 -C "$vdir/exim4.conf" -bV; } > "$vdir/validate.log" 2>&1; then
		echo "[ ! ] Exim: the patched configuration did not validate, nothing was changed"
		echo "      Details: $vdir/validate.log, rejected candidate: $tmp, backup: $bak"
		return 0
	fi

	if mv -f -- "$tmp" "$conf" && update-exim4.conf > "$vdir/apply.log" 2>&1 \
		&& exim4 -bV >> "$vdir/apply.log" 2>&1; then
		rm -rf "$vdir"
	else
		echo "[ ! ] Exim: applying the patched configuration failed, restoring the backup"
		cp -p -- "$bak" "$conf" && update-exim4.conf > /dev/null 2>&1
		echo "      Details: $vdir/apply.log, backup kept: $bak"
		return 0
	fi
}

if [[ "$MAIL_SYSTEM" == exim* ]]; then
	# Fill /etc/exim4/user-limits with each user's package RATE_LIMIT. The helper
	# warns on failure; its stderr goes to stdout so that the upgrade log has it.
	for user in $("$BIN/v-list-users" list); do
		update_exim_user_limit "$user" 2>&1
	done

	patch_exim_not_smtp_ratelimit
fi

unset -f patch_exim_not_smtp_ratelimit _acl_block _acl_block_norm
