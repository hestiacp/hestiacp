#!/bin/sh

hestia_module_clamav_setup-exim() {
    source $HESTIA/bin/module/func.inc

    module_installed=$(hestia_module_isinstalled mta)
    if [ ! "$module_installed" ] && [ ! "$param_force" ]; then
        echo "MTA module is not installed. See 'hestia module info mta'."
        exit 1
    fi

    module_variant=$(hestia_module_getvariant mta)
    if [ "$module_variant" != 'exim' ] && [ ! "$param_force" ]; then
        echo "The installed MTA module is not Exim. See 'hestia module info mta'."
        exit 1
    fi

    echo "Disabling Exim-ClamAV integration..."

    /usr/bin/sed -i "/CLAMD/ s/^/#/" $OSAL_PATH_EXIM_CONF/exim4.conf.template

    # FIXME: how do we rebuild exim.conf? 
}
