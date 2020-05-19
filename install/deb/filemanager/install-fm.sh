#!/bin/bash

if [ -z "$HESTIA" ]; then
    HESTIA="/usr/local/hestia"
fi

# Hestia php-fpm pool user
user='admin'

source $HESTIA/func/main.sh

FM_INSTALL_DIR="$HESTIA/web/fm"

FM_V="7.4.1"
FM_FILE="filegator_v${FM_V}.zip"
FM_URL="https://github.com/filegator/filegator/releases/download/v${FM_V}/${FM_FILE}"


COMPOSER_DIR="$HOMEDIR/$user/.composer"
COMPOSER_BIN="$COMPOSER_DIR/composer"

if [ ! -f "$COMPOSER_BIN" ]; then
    mkdir -p "$COMPOSER_DIR"
    chown $user: "$COMPOSER_DIR"

    COMPOSER_SETUP_FILE=$(mktemp)
    check_result $? "Create temp file"

    signature="$(curl https://composer.github.io/installer.sig)"
    check_result $? "Download signature"

    user_exec wget --tries=3 --timeout=15 --read-timeout=15 --waitretry=3 --no-dns-cache https://getcomposer.org/installer --quiet -O "$COMPOSER_SETUP_FILE"
    check_result $? "Download composer installer"

    [[ "$signature" = $(sha384sum $COMPOSER_SETUP_FILE | cut -f 1 -d " ") ]] || check_result $E_INVALID "Composer signature does not match"

    COMPOSER_HOME="$HOMEDIR/$user/.config/composer" user_exec /usr/bin/php "$COMPOSER_SETUP_FILE"  --install-dir="$COMPOSER_DIR" --filename=composer
    check_result $? "Composer instal failed"

    [ -f "$COMPOSER_SETUP_FILE" ] && rm -f "$COMPOSER_SETUP_FILE"
fi

mkdir -p "$FM_INSTALL_DIR"
cd "$FM_INSTALL_DIR"

[ ! -f "${FM_INSTALL_DIR}/${FM_FILE}" ] && 
    wget "$FM_URL" --quiet -O "${FM_INSTALL_DIR}/${FM_FILE}"

unzip -qq "${FM_INSTALL_DIR}/${FM_FILE}"
mv --force ${FM_INSTALL_DIR}/filegator/* "${FM_INSTALL_DIR}"
rm --recursive --force ${FM_INSTALL_DIR}/filegator
chown root: "${FM_INSTALL_DIR}"

cp --recursive --force ${HESTIA_INSTALL_DIR}/filemanager/filegator/* "${FM_INSTALL_DIR}"

COMPOSER_HOME="$HOMEDIR/$user/.config/composer" $COMPOSER_BIN require league/flysystem-sftp

chown $user: "${FM_INSTALL_DIR}/private"
chown $user: "${FM_INSTALL_DIR}/private/logs"
chown $user: "${FM_INSTALL_DIR}/repository"
