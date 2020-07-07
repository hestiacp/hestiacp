#!/bin/sh

hestia_module_php_list() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    module_installed=$(hestia_module_isinstalled php)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "PHP module is not installed. See 'hestia module info php'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant php)
    if [ "$module_variant" != 'php-fpm' ] && [ ! "$param_force" ]; then
        echo "The installed PHP module is not FPM. See 'hestia module info php'."
        exit 1
    fi

    echo "Present PHP (FPM) versions"
    for php_version in $PHP_SUPPORTED_VERSIONS; do
        php_withoutdot=${php_version//.}
        php_version_present=$(osal_kv_read_bool $HESTIA_CONF_MODULES/php.conf "php${php_withoutdot}_present")
        echo PHP ${php_version}: $(osal_bool_yes $php_version_present 'present' 'not present')
    done

    exit 0
}
