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
    check_result $? "Install composer failed"
fi

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
COMPOSER_HOME="$HOMEDIR/$user/.config/composer" user_exec /usr/bin/php $COMPOSER_BIN --quiet --no-dev install
check_result $? "Install filemanager dependency"

chown root: -R "${FM_INSTALL_DIR}"
chown $user: "${FM_INSTALL_DIR}/private"
chown $user: "${FM_INSTALL_DIR}/private/logs"
chown $user: "${FM_INSTALL_DIR}/repository"
