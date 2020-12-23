#!/bin/bash

hestia_package_mail-server_integrate() {
    source $HESTIA/bin/module/func.inc

    # Rebuild mail
    for user in $($HESTIA/bin/v-list-sys-users plain); do
        echo "Rebuilding mail domains for user $user"
        $BIN/v-rebuild-mail-domains $user 'no' >/dev/null 2>&1
    done

    # Setup ClamAv integration
    if [[ $(hestia_module_variant_installed 'antivirus') == 'clamav' ]]; then
        hestia module clamav exim-integration enable
    else
        hestia module clamav exim-integration disable
    fi

    # Setup SpamAssassin integration
    if [[ $(hestia_module_variant_installed 'antispam') == 'spamassassin' ]]; then
        hestia module spamassassin exim-integration enable
    else
        hestia module spamassassin exim-integration disable
    fi
}
