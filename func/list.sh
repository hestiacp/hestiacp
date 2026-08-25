#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - List Function Library                              #
#                                                                           #
#===========================================================================#

# Combine a user-scoped list command for all Hestia users.
# JSON object keys must be unique across users.
list_all_user_objects_json() {
	local list_command="$1"
	local users user objects result status

	users=$("$BIN/v-list-users" list)
	status=$?
	[ "$status" -eq 0 ] || return "$status"

	result=$(
		set -o pipefail
		{
			while IFS= read -r user; do
				[ -z "$user" ] && continue

				objects=$("$BIN/$list_command" "$user" json)
				status=$?
				[ "$status" -eq 0 ] || exit "$status"

				printf '{"USER":"%s","OBJECTS":%s}\n' "$user" "$objects"
			done <<< "$users"
		} | jq --indent 4 -s 'map(.USER as $user | .OBJECTS | with_entries(.value = ({ USER: $user } + .value))) | add // {}'
	)
	status=$?
	[ "$status" -eq 0 ] || return "$status"

	printf '%s\n' "$result"
}

# Add the user to every row while keeping the original command output.
list_all_user_objects_table() {
	local list_command="$1"
	local format="$2"
	local separator header_lines
	local first_user='yes'
	local users user_output result status
	local line line_number prefix user

	# Existing Hestia list commands emit no header in plain output, one header
	# line in CSV output, and a heading plus separator line in shell output.
	case $format in
		plain)
			separator=$'\t'
			header_lines=0
			;;
		csv)
			separator=','
			header_lines=1
			;;
		shell)
			separator=' '
			header_lines=2
			;;
	esac

	users=$("$BIN/v-list-users" list)
	status=$?
	[ "$status" -eq 0 ] || return "$status"

	result=$(
		while IFS= read -r user; do
			[ -z "$user" ] && continue

			user_output=$("$BIN/$list_command" "$user" "$format")
			status=$?
			[ "$status" -eq 0 ] || exit "$status"

			line_number=0
			while IFS= read -r line; do
				[ -z "$line" ] && continue
				((line_number++))
				if [ "$line_number" -le "$header_lines" ]; then
					if [ "$first_user" = 'yes' ]; then
						prefix='USER'
						if [ "$line_number" -gt 1 ]; then
							prefix='----'
						fi
						printf '%s%s%s\n' "$prefix" "$separator" "$line"
					fi
					continue
				fi

				printf '%s%s%s\n' "$user" "$separator" "$line"
			done <<< "$user_output"
			first_user='no'
		done <<< "$users"
	)
	status=$?
	[ "$status" -eq 0 ] || return "$status"

	[ -n "$result" ] && printf '%s\n' "$result"
}

list_all_user_objects() {
	local list_command="$1"
	local format="$2"
	local result status

	case $format in
		json) list_all_user_objects_json "$list_command" ;;
		plain | csv) list_all_user_objects_table "$list_command" "$format" ;;
		shell)
			result=$(
				set -o pipefail
				list_all_user_objects_table "$list_command" "$format" | column -t
			)
			status=$?
			[ "$status" -eq 0 ] || return "$status"

			[ -n "$result" ] && printf '%s\n' "$result"
			;;
	esac
}
