<?php

    if ((($_SESSION['userContext'] === 'user') && ($panel[$user]['SUSPENDED'] === 'yes') && ($_SESSION['POLICY_USER_VIEW_SUSPENDED'] === 'yes')) ||
       (($_SESSION['userContext'] === 'admin') && ($_SESSION['look'] === 'admin') && ($_SESSION['POLICY_SYSTEM_PROTECTED_ADMIN'] === 'yes'))) {
        $read_only = 'true';
    } else {
        $read_only = '';
    }

    if ($read_only === 'true') {
        $display_mode = 'disabled';
    } else {
        $display_mode = '';
    }
