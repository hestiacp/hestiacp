<?php
session_start();

define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');

if (!empty($_SESSION['look'])) {
    unset($_SESSION['look']);
    unset($_SESSION['LANDING_POINT_SOURCE']);
    unset($_SESSION['LANDING_POINT_VAR_DATA']);
    # Remove current path for filemanager
    unset($_SESSION['_sf2_attributes']);
    unset($_SESSION['_sf2_meta']);
    header("Location: /");
} else {
    if($_SESSION['token'] && $_SESSION['user']){
        $v_user = escapeshellarg($_SESSION['user']);
        $v_session_id = escapeshellarg($_SESSION['token']);
        exec(HESTIA_CMD."v-log-user-logout ".$v_user." ".$v_session_id, $output, $return_var);
    }
    
    session_destroy();
    header("Location: /login/");
}
exit;
?>
