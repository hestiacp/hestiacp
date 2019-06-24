#!/bin/bash
function welcome_message() {
    echo
    echo '     _   _           _   _        ____ ____        '
    echo '    | | | | ___  ___| |_(_) __ _ / ___|  _ \       '
    echo '    | |_| |/ _ \/ __| __| |/ _` | |   | |_) |      '
    echo '    |  _  |  __/\__ \ |_| | (_| | |___|  __/       '
    echo '    |_| |_|\___||___/\__|_|\__,_|\____|_|          '
    echo ""
    echo "       Hestia Control Panel Upgrade Script"
    echo "                 Version: $version                 "
    echo "==================================================="
    echo ""
    echo "Existing files will be backed up to the following location:"
    echo "$HESTIA_BACKUP/"
    echo ""
    echo "This process may take a few moments, please wait..."
    echo ""
}

function upgrade_complete() {
    echo ""
    echo "    Upgrade complete! Please report any bugs or issues to"
    echo "    https://github.com/hestiacp/hestiacp/issues"
    echo ""
    echo "    We hope that you enjoy this release of Hestia Control Panel,"
    echo "    enjoy your day!"
    echo ""
    echo "    Sincerely,"
    echo "    The Hestia Control Panel development team"
    echo ""
    echo "    www.hestiacp.com"
    echo "    Made with love & pride by the open-source community around the world."
    echo ""
    echo ""
}