<?php
session_start();
define('NO_AUTH_REQUIRED',true);
$TAB = 'RESET PASSWORD';

if (isset($_SESSION['user'])) {
    header("Location: /list/user");
}

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ( (!empty($_POST['user'])) && (!empty($_POST['twofa'])) ) {
    $v_user = escapeshellarg($_POST['user']);
    $v_twofa = str_replace(' ', '', $_POST['twofa']);
    $cmd="/usr/bin/sudo /usr/local/hestia/bin/v-list-user";
    exec ($cmd." ".$v_user." json", $output, $return_var);
    if ( $return_var == 0 ) {
        $data = json_decode(implode('', $output), true);
        $twofa = $data[$_POST['user']]['TWOFA'];
        if (hash_equals($twofa, $v_twofa)) {
            $cmd="/usr/bin/sudo /usr/local/hestia/bin/v-delete-user-2fa";
            exec ($cmd." ".$v_user, $output, $return_var);
            if ( $return_var > 0 ) {
                $ERROR = "<a class=\"error\">".__('An internal error occurred')."</a>";
            } else {
                header("Location: /");
                exit;
            }
        } else {
            $ERROR = "<a class=\"error\">".__('Invalid username or reset code')."</a>";
        }
    } else {
        $ERROR = "<a class=\"error\">".__('Invalid username or reset code')."</a>";
    }
}

// Detect language
if (empty($_SESSION['language'])) $_SESSION['language'] = detect_user_language();

require_once '../templates/header.html';
require_once '../templates/reset2fa.html';

?>
