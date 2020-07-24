#!/bin/bash

hestia_package_install_mail-server() {
    local memory=$(grep 'MemTotal' /proc/meminfo |tr ' ' '\n' |grep [0-9])

    if [ "$param_help" ]; then
        usage
        return 0
    fi

    echo "Installing mail server package..."

    hestia module install exim
    hestia module install dovecot
    if [ $memory -ge 1572864 ] || [ "$param_withantivirus" ]; then
        if [ ! "$param_withoutantivirus" ]; then
            hestia module install clamav
        fi
    fi

    if [ $memory -ge 1572864 ] || [ "$param_withantispam" ]; then
        if [ ! "$param_withoutantispam" ]; then
            hestia module install spamassassin
        fi
    fi
}

usage() {
    echo "Hestia mail server package."
    echo "Usage:"
    echo "  hestia package install mail-server \\"
    echo "    [--withantivirus|--withoutantivirus]\\"
    echo "    [--withantispam|--withoutantispam]"
    echo ""
    echo "Antivirus and antispam packages will install on systems"
    echo "with at least 1.5 GB of RAM, unless otherwise specified."
}