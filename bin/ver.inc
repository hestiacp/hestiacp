#!/bin/sh

hestia_ver() {
    echo "Hestia ver $@"
    echo "Demo of single-level commands"
}

# If this files is _not_ being sourced, act immediately
# (otherise, wait for hestia cli to call the main function)
[[ $_ == $0 ]] && hestia_web_domain_list $@
