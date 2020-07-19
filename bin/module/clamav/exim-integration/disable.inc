#!/bin/bash

hestia_module_clamav_exim-integration_disable() {
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
    /usr/bin/sed -i "/^CLAMD\s*=\s*/ s/^/#/" "${OSAL_PATH_EXIM_CONF}"
    
    osal_service_restart $OSAL_SERVICE_EXIM
    check_result $? "Exim restart failed"
}
