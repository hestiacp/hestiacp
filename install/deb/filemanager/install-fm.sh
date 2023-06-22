#!/bin/bash

# Checking root permissions
if [ "x$(id -u)" != 'x0' ]; then
	echo "Error: Script can be run executed only by root"
	exit 10
fi

if [ -z "$HESTIA" ]; then
	HESTIA="/usr/local/hestia"
fi

user='admin'
fm_error='no'
source $HESTIA/func/main.sh
source $HESTIA/install/upgrade/upgrade.conf

if [ -z "$HOMEDIR" ] || [ -z "$HESTIA_INSTALL_DIR" ]; then
	echo "Error: Hestia environment vars not present"
	exit 2
fi

FM_INSTALL_DIR="$HESTIA/web/fm"

FM_FILE="filegator_latest"
FM_URL="https://github.com/filegator/static/raw/master/builds/filegator_latest.zip"

COMPOSER_BIN="$HOMEDIR/$user/.composer/composer"
if [ ! -f "$COMPOSER_BIN" ]; then
	$BIN/v-add-user-composer "$user"
	if [ $? -ne 0 ]; then
		$BIN/v-add-user-notification admin 'Composer installation failed!' '<p class="u-text-bold">The File Manager will not work without Composer.</p><p>Please try running the installer from a shell session:<br><code>bash $HESTIA/install/deb/filemanager/install-fm.sh</code></p><p>If this issue continues, please <a href="https://github.com/hestiacp/hestiacp/issues" target="_blank">open an issue on GitHub</a>.</p>'
		fm_error='yes'
	fi
fi

if [ "$fm_error" != "yes" ]; then
	rm --recursive --force "$FM_INSTALL_DIR"
	mkdir -p "$FM_INSTALL_DIR"
	cd "$FM_INSTALL_DIR"

	[ ! -f "${FM_INSTALL_DIR}/${FM_FILE}" ] && wget "$FM_URL" --quiet -O "${FM_INSTALL_DIR}/${FM_FILE}.zip"

	unzip -qq "${FM_INSTALL_DIR}/${FM_FILE}.zip"
	mv --force ${FM_INSTALL_DIR}/filegator/* "${FM_INSTALL_DIR}"
	rm --recursive --force ${FM_INSTALL_DIR}/${FM_FILE}
	[[ -f "${FM_INSTALL_DIR}/${FM_FILE}" ]] && rm "${FM_INSTALL_DIR}/${FM_FILE}"

	cp --recursive --force ${HESTIA_INSTALL_DIR}/filemanager/filegator/* "${FM_INSTALL_DIR}"

	chown $user: -R "${FM_INSTALL_DIR}"

	# Check if php7.3 is available and run the installer
	if [ -f "/usr/bin/php7.3" ]; then
		COMPOSER_HOME="$HOMEDIR/$user/.config/composer" user_exec /usr/bin/php7.3 $COMPOSER_BIN --quiet --no-dev install
		if [ $? -ne 0 ]; then
			$BIN/v-add-user-notification admin 'File Manager installation failed!' '<p>Please try running the installer from a shell session:<br><code>bash $HESTIA/install/deb/filemanager/install-fm.sh</code></p><p>If this issue continues, please <a href="https://github.com/hestiacp/hestiacp/issues" target="_blank">open an issue on GitHub</a>.</p>'
			fm_error="yes"
		fi
	else
		$BIN/v-add-user-notification admin 'File Manager installation failed!' '<p class="u-text-bold">Unable to proceed with installation of File Manager.</p><p>Package <span class="u-text-bold">php7.3-cli</span> is missing from your system. Please check your PHP installation and environment settings.</p>'
		fm_error="yes"
	fi

	if [ "$fm_error" != "yes" ]; then
		chown root: -R "${FM_INSTALL_DIR}"
		chown $user: "${FM_INSTALL_DIR}/private"
		chown $user: "${FM_INSTALL_DIR}/private/logs"
		chown $user: "${FM_INSTALL_DIR}/repository"
	fi
fi
