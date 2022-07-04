<?php
use function Divinity76\quoteshellarg\quoteshellarg;

define('NO_AUTH_REQUIRED', true);
$TAB = 'RESET PASSWORD';

if (isset($_SESSION['user'])) {
    header("Location: /list/user");
}

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

//Check values
if (!empty($_POST['user']) && !empty($_POST['twofa'])) {
    // Check token
    verify_csrf($_POST);
    $error = true;
    $v_user = quoteshellarg($_POST['user']);
    $user = $_POST['user'];
    $twofa = $_POST['twofa'];
    exec(HESTIA_CMD . "v-list-user ".$v_user .' json', $output, $return_var);
    if ($return_var == 0) {
        $data = json_decode(implode('', $output), true);
        if ($data[$user]['TWOFA'] == $twofa) {
            $success = true;
            exec(HESTIA_CMD . "v-delete-user-2fa ".$v_user, $output, $return_var);
            session_destroy();
        } else {
            exec(HESTIA_CMD . 'v-log-user-login ' . $v_user . ' ' . $v_ip . ' failed ' . $v_session_id . ' ' . $v_user_agent .' yes "Failed to enter correct 2FA reset key"', $output, $return_var);
            sleep(5);
        }
    } else {
        exec(HESTIA_CMD . 'v-log-user-login ' . $v_user . ' ' . $v_ip . ' failed ' . $v_session_id . ' ' . $v_user_agent .' yes "Failed to enter correct 2FA reset key"', $output, $return_var);
        sleep(5);
    }
}

require_once '../templates/header.html';
require_once '../templates/pages/login/reset2fa.html';
