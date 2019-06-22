#!/bin/bash
HESTIA="/usr/local/hestia"
HESTIA_BACKUP="/root/hst_upgrade/$(date +%d%m%Y%H%M)"
spinner="/-\|"

function version_ge(){ test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1" -o ! -z "$1" -a "$1" = "$2"; }

# Add amd64 to repositories to prevent notifications - https://goo.gl/hmsSV7
if ! grep -q 'arch=amd64' /etc/apt/sources.list.d/nginx.list; then
    sed -i s/"deb "/"deb [arch=amd64] "/g /etc/apt/sources.list.d/nginx.list
fi
if ! grep -q 'arch=amd64' /etc/apt/sources.list.d/mariadb.list; then
    sed -i s/"deb "/"deb [arch=amd64] "/g /etc/apt/sources.list.d/mariadb.list
fi

# Fix named rule for AppArmor - https://goo.gl/SPqHdq
if [ "$DNS_SYSTEM" = 'bind9' ] && [ ! -f /etc/apparmor.d/local/usr.sbin.named ]; then
        echo "/home/** rwm," >> /etc/apparmor.d/local/usr.sbin.named 2> /dev/null
fi

# Remove obsolete ports.conf if exists.
if [ -f /usr/local/hestia/data/firewall/ports.conf ]; then
    rm -f /usr/local/hestia/data/firewall/ports.conf
fi

# Reset backend port
if [ ! -z "$BACKEND_PORT" ]; then
    /usr/local/hestia/bin/v-change-sys-port $BACKEND_PORT
fi

# Move clamav to proper location - https://goo.gl/zNuM11
if [ ! -d /usr/local/hestia/web/edit/server/clamav-daemon ]; then
    mv /usr/local/hestia/web/edit/server/clamd /usr/local/web/edit/server/clamav-daemon
fi