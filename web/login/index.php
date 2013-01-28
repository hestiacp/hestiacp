<?php
session_start();

define('NO_AUTH_REQUIRED',true);
$TAB = 'LOGIN';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
}

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


// Login as someone else
if (isset($_SESSION['user'])) {
    if ($_SESSION['user'] ==  'admin' && !empty($_GET['loginas'])) {
        if ($_GET['loginas'] == 'admin') {
            unset($_SESSION['look']);
        } else {
            $_SESSION['look'] = $_GET['loginas'];
            $_SESSION['look_alert'] = $_GET['loginas'];
        }
    }
    header("Location: /");
    exit;
}

// Auth
if (isset($_POST['user']) && isset($_POST['password'])) {
    $v_user = escapeshellarg($_POST['user']);
    $v_password = escapeshellarg($_POST['password']);
    exec(VESTA_CMD ."v-check-user-password ".$v_user." ".$v_password." '".$_SERVER["REMOTE_ADDR"]."'",  $output, $return_var);
    if ( $return_var > 0 ) {
        $ERROR = "<a class=\"error\">"._('Invalid username or password')."</a>";
    } else {
        unset($output);
        exec (VESTA_CMD . "v-list-user ".$v_user." json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        $_SESSION['language'] = $data[$_POST['user']]['LANGUAGE'];
        if (empty($_SESSION['language'])) $_SESSION['language'] = 'en';
        $_SESSION['user'] = $_POST['user'];
        if ($_POST['user'] == 'root') $_SESSION['user'] = 'admin';
        if (!empty($_SESSION['request_uri'])) {
            header("Location: ".$_SESSION['request_uri']);
            unset($_SESSION['request_uri']);
            exit;
        } else {
            header("Location: /");
            exit;
        }
    }
} else {
    // Set system language
    exec (VESTA_CMD . "v-list-sys-config json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    if (!empty( $data['config']['LANGUAGE'])) {
        $_SESSION['language'] = $data['config']['LANGUAGE'];
    } else {
        $_SESSION['language'] = 'en';
    }

    require_once($_SERVER['DOCUMENT_ROOT'].'/inc/i18n/'.$_SESSION['language'].'.php');
    require_once('../templates/header.html');
    require_once('../templates/login.html');
}
?>
