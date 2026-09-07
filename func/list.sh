#!/bin/bash

#===========================================================================#
#                                                                           #
# Hestia Control Panel - List Function Library                              #
#                                                                           #
#===========================================================================#

# Read each configuration once, without spawning a list command per user or a
# parser per object. Keep all output private until parsing/formatting succeeds.
list_all_user_objects() (
	local kind="$1" format="$2" result status file
	local -a files=()

	case $kind in
		web | mail | dns | db) ;;
		*) return 1 ;;
	esac
	case $format in
		plain | csv | shell | json) ;;
		*) return 1 ;;
	esac
	if [[ $HESTIA != /* || ! -d $HESTIA/data/users || ! -r $HESTIA/data/users || ! -x $HESTIA/data/users ]]; then
		printf '%s\n' 'Error: cannot read the Hestia user directory' >&2
		return 1
	fi

	shopt -s nullglob
	# Like v-list-users, require user.conf before treating a directory as a user.
	# Ignore unrelated files and directories left behind without that marker.
	for file in "$HESTIA"/data/users/*/user.conf; do
		[[ -f $file ]] || continue
		file=${file%/*}
		# Do not follow a user directory outside the Hestia user store.
		if [[ -L $file || ! -d $file || ! -r $file || ! -x $file ]]; then
			printf 'Error: invalid user directory: %s\n' "$file" >&2
			return 1
		fi
		file="$file/$kind.conf"
		# A missing service configuration is a valid empty inventory.
		[[ -e $file || -L $file ]] || continue
		if [[ ! -f $file || ! -r $file || -L $file ]]; then
			printf 'Error: cannot read configuration: %s\n' "$file" >&2
			return 1
		fi
		files+=("$file")
	done

	result=$(
		set -o pipefail
		HESTIA_LIST_HOMEDIR="$HOMEDIR" LC_ALL=C awk -v kind="$kind" -v format="$format" '
			function fail() {
				# Never include a configuration value (possibly a secret) in errors.
				printf "Error: invalid configuration: %s:%d\n", FILENAME, FNR > "/dev/stderr"
				exit 1
			}
			# Match the single-quoted/unquoted key-value format used by Hestia.
			# Values are data: never source/eval configuration or interpolate code.
			function parse(line,    key, value, c, end) {
				# Intentionally leave missing fields empty instead of inheriting them
				# from the previous row, as the legacy parser/eval can do.
				for (key in object) delete object[key]
				while (length(line)) {
					sub(/^[ \t\r]+/, "", line)
					if (!length(line)) break
					if (!match(line, /^[A-Za-z][A-Za-z0-9_]*=/)) fail()
					key = substr(line, 1, RLENGTH - 1)
					line = substr(line, RLENGTH + 1)
					value = ""
					while (length(line) && line !~ /^[ \t\r]/) {
						c = substr(line, 1, 1)
						line = substr(line, 2)
						if (c == quote) {
							end = index(line, quote)
							if (!end) fail()
							value = value substr(line, 1, end - 1)
							line = substr(line, end + 1)
						} else if (c == "\\") {
							if (!length(line)) fail()
							value = value substr(line, 1, 1)
							line = substr(line, 2)
						} else {
							value = value c
						}
					}
					object[key] = value
				}
			}
			function json(value,    out, i, c) {
				out = "\""
				for (i = 1; i <= length(value); i++) {
					c = substr(value, i, 1)
					if (c == "\\" || c == "\"") out = out "\\" c
					else if (c in control) out = out control[c]
					else out = out c
				}
				return out "\""
			}
			BEGIN {
				quote = sprintf("%c", 39)
				for (i = 0; i < 32; i++) control[sprintf("%c", i)] = sprintf("\\u%04x", i)
				if (kind == "web") {
					identity = "DOMAIN"
					fields = "DOMAIN IP IP6 DOCROOT U_DISK U_BANDWIDTH TPL ALIAS STATS STATS_USER SSL SSL_HOME LETSENCRYPT FTP_USER FTP_PATH AUTH_USER BACKEND PROXY PROXY_EXT SUSPENDED TIME DATE"
					json_fields = "IP IP6 DOCUMENT_ROOT U_DISK U_BANDWIDTH TPL ALIAS STATS STATS_USER SSL SSL_HOME LETSENCRYPT FTP_USER FTP_PATH AUTH_USER BACKEND PROXY PROXY_EXT SUSPENDED TIME DATE"
					shell_fields = "DOMAIN IP TPL SSL U_DISK U_BANDWIDTH SUSPENDED DATE"
					heading = "DOMAIN IP TPL SSL DISK BW SPND DATE"
					rule = "------ -- --- --- ---- -- ---- -----"
				} else if (kind == "dns") {
					identity = "DOMAIN"
					fields = "DOMAIN IP TPL TTL EXP SOA SERIAL SRC DNSSEC RECORDS SUSPENDED TIME DATE"
					if (format == "plain") fields = "DOMAIN IP TPL TTL EXP SOA DNSSEC SERIAL SRC RECORDS SUSPENDED TIME DATE"
					json_fields = "IP TPL TTL EXP SOA SERIAL DNSSEC SRC RECORDS SUSPENDED TIME DATE"
					shell_fields = "DOMAIN IP TPL TTL DNSSEC RECORDS SUSPENDED DATE"
					heading = "DOMAIN IP TPL TTL DNSSEC REC SPND DATE"
					rule = "------ -- --- --- ------ --- ---- ----"
				} else if (kind == "mail") {
					identity = "DOMAIN"
					fields = "DOMAIN ANTIVIRUS ANTISPAM DKIM SSL CATCHALL ACCOUNTS U_DISK SUSPENDED TIME DATE WEBMAIL_ALIAS WEBMAIL"
					json_fields = "ANTIVIRUS ANTISPAM REJECT RATE_LIMIT DKIM CATCHALL ACCOUNTS U_DISK SSL SUSPENDED TIME DATE WEBMAIL_ALIAS WEBMAIL"
					shell_fields = "DOMAIN ANTIVIRUS ANTISPAM DKIM SSL ACCOUNTS U_DISK SUSPENDED DATE"
					heading = "DOMAIN ANTIVIRUS ANTISPAM DKIM SSL ACC DISK SPND DATE"
					rule = "------ --------- -------- ---- --- --- ---- --- ----"
				} else {
					identity = "DB"
					fields = "DATABASE DBUSER HOST TYPE CHARSET U_DISK SUSPENDED TIME DATE"
					json_fields = fields
					shell_fields = "DATABASE DBUSER HOST TYPE U_DISK SUSPENDED DATE"
					heading = "DATABASE USER HOST TYPE DISK SPND DATE"
					rule = "-------- ---- ---- ---- ---- ---- ----"
				}
				if (format == "json") {
					fields = json_fields
					printf "{"
				} else if (format == "shell") {
					fields = shell_fields
					print "USER " heading
					print "---- " rule
				} else if (format == "csv") {
					heading = fields
					gsub(/ /, ",", heading)
					print "USER," heading
				}
				count = split(fields, names, " ")
				separator = (format == "plain" ? "\t" : (format == "csv" ? "," : " "))
			}
			/^[ \t\r]*$/ { next }
			{
				parse($0)
				if (object[identity] == "") fail()
				owner = FILENAME
				sub(/\/[^\/]*$/, "", owner)
				sub(/^.*\//, "", owner)
				# Explicit output schemas exclude passwords and other private fields.
				object["DATABASE"] = object["DB"]
				object["DOCROOT"] = object["CUSTOM_DOCROOT"]
				if (object["DOCROOT"] == "") object["DOCROOT"] = ENVIRON["HESTIA_LIST_HOMEDIR"] "/" owner "/web/" object["DOMAIN"] "/public_html/"
				object["DOCUMENT_ROOT"] = object["DOCROOT"]
				if (format == "json") {
					# Do not silently overwrite an object if a corrupt store has duplicates.
					if (object[identity] in seen) fail()
					seen[object[identity]] = 1
					printf "%s%s:{\"USER\":%s", (rows++ ? "," : ""), json(object[identity]), json(owner)
					for (i = 1; i <= count; i++) printf ",%s:%s", json(names[i]), json(object[names[i]])
					printf "}"
				} else {
					printf "%s", owner
					for (i = 1; i <= count; i++) {
						key = names[i]
						value = object[key]
						# Preserve the existing mail table contract, including its legacy
						# literal CATCHALL suffix and CSV disk-prefix apostrophe.
						if (kind == "mail" && format == "plain") {
							if (key == "CATCHALL") continue
							if (key == "SSL") value = value "$CATCHALL"
						}
						if (kind == "mail" && format == "csv" && key == "U_DISK") value = quote value
						if (kind == "web" && format == "csv" && key ~ /^(ALIAS|STATS_USER|FTP_USER|FTP_PATH|AUTH_USER|PROXY_EXT)$/) value = "\"" value "\""
						printf "%s%s", separator, value
					}
					# Intentionally omit the blank row emitted by legacy mail CSV.
					printf "\n"
				}
			}
			END { if (format == "json") print "}" }
		' /dev/null "${files[@]}" | case $format in
			json) jq --indent 4 . ;;
			shell) column -t ;;
			*) cat ;;
		esac
	)
	status=$?
	[ "$status" -eq 0 ] || return "$status"
	if [ -n "$result" ]; then
		printf '%s\n' "$result"
	fi
)
