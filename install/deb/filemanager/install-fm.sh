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

if [ -z "$HOMEDIR" ] || [ -z "$HESTIA_INSTALL_DIR" ]; then
    echo "Error: Hestia environment vars not present"
    exit 2
fi

FM_INSTALL_DIR="$HESTIA/web/fm"

FM_V="7.4.1"
FM_FILE="filegator_v${FM_V}.zip"
FM_URL="https://github.com/filegator/filegator/releases/download/v${FM_V}/${FM_FILE}"


COMPOSER_BIN="$HOMEDIR/$user/.composer/composer"
if [ ! -f "$COMPOSER_BIN" ]; then
    $BIN/v-add-user-composer "$user"
    if [ $? -ne 0 ]; then
        $BIN/v-add-user-notification admin 'Composer installation failed!' '<b>The File Manager will not work without Composer.</b><br><br>Please try running the installer from a shell session:<br>bash $HESTIA/install/deb/filemanager/install-fm.sh<br><br>If this issue continues, please open an issue report on <a href="https://github.com/hestiacp/hestiacp/issues" target="_new"><i class="fab fa-github"></i> GitHub</a>.'
        fm_error='yes'
    fi
fi

if [ "$fm_error" != "yes" ]; then
    rm --recursive --force "$FM_INSTALL_DIR"
    mkdir -p "$FM_INSTALL_DIR"
    cd "$FM_INSTALL_DIR"

    [ ! -f "${FM_INSTALL_DIR}/${FM_FILE}" ] && 
        wget "$FM_URL" --quiet -O "${FM_INSTALL_DIR}/${FM_FILE}"

    unzip -qq "${FM_INSTALL_DIR}/${FM_FILE}"
    mv --force ${FM_INSTALL_DIR}/filegator/* "${FM_INSTALL_DIR}"
    rm --recursive --force ${FM_INSTALL_DIR}/filegator
    [[ -f "${FM_INSTALL_DIR}/${FM_FILE}" ]] && rm "${FM_INSTALL_DIR}/${FM_FILE}"

    cp --recursive --force ${HESTIA_INSTALL_DIR}/filemanager/filegator/* "${FM_INSTALL_DIR}"

    chown $user: -R "${FM_INSTALL_DIR}"

    # Check if php7.3 is available and run the installer
    if [ -f "/usr/bin/php7.3" ]; then
        COMPOSER_HOME="$HOMEDIR/$user/.config/composer" user_exec /usr/bin/php7.3 $COMPOSER_BIN --quiet --no-dev install
        if [ $? -ne 0 ]; then
            $BIN/v-add-user-notification admin 'File Manager installation failed!' 'Please try running the installer from a shell session:<br>bash $HESTIA/install/deb/filemanager/install-fm.sh<br><br>If this issue continues, please open an issue report on <a href="https://github.com/hestiacp/hestiacp/issues" target="_new"><i class="fab fa-github"></i> GitHub</a>.'
            fm_error="yes"
        fi
    else
        $BIN/v-add-user-notification admin 'File Manager installation failed!' '<b>Unable to proceed with installation of File Manager.</b><br><br>Package <b>php7.3-cli</b> is missing from your system. Please check your PHP installation and environment settings.'
        fm_error="yes"
    fi

    if [ "$fm_error" != "yes" ]; then
        chown root: -R "${FM_INSTALL_DIR}"
        chown $user: "${FM_INSTALL_DIR}/private"
        chown $user: "${FM_INSTALL_DIR}/private/logs"
        chown $user: "${FM_INSTALL_DIR}/repository"
    fi
fi
