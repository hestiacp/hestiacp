#!/bin/bash

hestia_module_php_add() {
    source $HESTIA/bin/module/func.inc
    source $HESTIA/bin/module/php/func.inc

    if ! hestia_module_isinstalled 'php' && [ ! "$param_force" ]; then
        echo "PHP module is not installed. See 'hestia module info php'."
        return 1
    fi

    if [ ! "$param_template" ]; then
        param_template='default'
    fi

    if [ ! "$param_docroot" ]; then
        param_docroot='public_html'
    fi

    local pool_file=$(osal_php_fpm_pool_d $PHP_DEFAULT_VERSION)/${param_domain}.conf

    cat $HESTIA_INSTALL_DIR/php-fpm/${param_template}.tpl |\
        sed -e "s|%user%|$param_user|g"\
            -e "s|%apache_group%|$WEB_RGROUPS|g"\
            -e "s|%domain%|$param_domain|g"\
            -e "s|%domain%|$param_domain|g"\
            -e "s|%docroot%|$param_docroot|g" > $pool_file
}
