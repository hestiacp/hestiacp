#!/bin/bash

# Hestia Control Panel upgrade script for target version 1.10.11

# Refresh the installed phpMyAdmin SSO handler using its existing settings.
if [[ -f /usr/share/phpmyadmin/hestia-sso.php ]]; then
	if ! (
		endpoint=/usr/share/phpmyadmin/hestia-sso.php
		template="$HESTIA_INSTALL_DIR/phpmyadmin/hestia-sso.php"
		staging=$(mktemp -d "${endpoint}.XXXXXX") || exit 1
		staged="$staging/hestia-sso.php"
		trap 'rm -f -- "$staged"; rmdir -- "$staging"' EXIT

		awk '
			FILENAME == ARGV[1] {
				if ($0 ~ /^define\("(PHPMYADMIN_KEY|API_HOST_NAME|API_HESTIA_PORT|API_KEY)", "[[:alnum:]_.:=-]+"\);$/) {
					split($0, fields, "\"")
					if (fields[2] in installed) invalid = 1
					installed[fields[2]] = $0
				}
				next
			}
			/^define\("(PHPMYADMIN_KEY|API_HOST_NAME|API_HESTIA_PORT|API_KEY)", "%[A-Z_]+%"\);$/ {
				split($0, fields, "\"")
				if (!(fields[2] in installed) || ++replaced[fields[2]] != 1) invalid = 1
				print installed[fields[2]]
				count++
				next
			}
			{ print }
			END { if (invalid || count != 4) exit 1 }
		' "$endpoint" "$template" > "$staged" || exit 1
		"$HESTIA/php/bin/php" -l "$staged" > /dev/null 2>&1 || exit 1

		if ! cmp -s "$staged" "$endpoint"; then
			chown root:hestiamail "$staged" && chmod 0640 "$staged" && mv -fT -- "$staged" "$endpoint"
		fi
	); then
		echo "[ ! ] Could not refresh the phpMyAdmin SSO handler."
		add_upgrade_message "The phpMyAdmin Single Sign-On handler could not be updated. Check the installed handler configuration before using Single Sign-On."
	fi
fi
