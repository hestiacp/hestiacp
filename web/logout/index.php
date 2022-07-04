<?php
use function Divinity76\quoteshellarg\quoteshellarg;
// Main include
include($_SERVER['DOCUMENT_ROOT'] . '/inc/main.php');
// Check token
verify_csrf($_GET);

if (!empty($_SESSION['look'])) {
    $v_user = quoteshellarg($_SESSION['look']);
    $v_impersonator = quoteshellarg($_SESSION['user']);
    exec(HESTIA_CMD . "v-log-action system 'Warning' 'Security' 'User impersonation session ended (User: $v_user, Administrator: $v_impersonator)'", $output, $return_var);
    unset($_SESSION['look']);
    # Remove current path for filemanager
    unset($_SESSION['_sf2_attributes']);
    unset($_SESSION['_sf2_meta']);
    header('Location: /');
} else {
    if ($_SESSION['token'] && $_SESSION['user']) {
        unset($_SESSION['userTheme']);
        $v_user = quoteshellarg($_SESSION['user']);
        $v_session_id = quoteshellarg($_SESSION['token']);
        exec(HESTIA_CMD . 'v-log-user-logout ' . $v_user . ' ' . $v_session_id, $output, $return_var);
    }

    unset($_SESSION);
    session_unset();
    session_destroy();
    header('Location: /login/');
}
exit;
