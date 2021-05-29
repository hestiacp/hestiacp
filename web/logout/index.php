<?php
session_start();

define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');

if (!empty($_SESSION['look'])) {
    $v_user = escapeshellarg($_SESSION['look']);
    $v_impersonator = escapeshellarg($_SESSION['user']);
    exec (HESTIA_CMD . "v-log-action system 'Warning' 'Security' 'User impersonation session ended (User: $v_user, Administrator: $v_impersonator)'", $output, $return_var);
    unset($_SESSION['look']);
    # Remove current path for filemanager
    unset($_SESSION['_sf2_attributes']);
    unset($_SESSION['_sf2_meta']);
    header('Location: /');
} else {
    if ($_SESSION['token'] && $_SESSION['user']){
        unset($_SESSION['userTheme']);
        $v_user = escapeshellarg($_SESSION['user']);
        $v_session_id = escapeshellarg($_SESSION['token']);
        exec(HESTIA_CMD . 'v-log-user-logout ' . $v_user . ' ' . $v_session_id, $output, $return_var);
    }
    session_destroy();
    header('Location: /login/');
}
exit;
