<?php

session_start();

define('NO_AUTH_REQUIRED',true);

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (isset($_GET['logout'])) {
    session_destroy();
}

$TAB = 'LOGIN';

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
} else {
    if (isset($_POST['user']) && isset($_POST['password'])) {
        $cmd="/usr/bin/sudo /usr/local/vesta/bin/";
        $v_user = escapeshellarg($_POST['user']);
        $v_password = escapeshellarg($_POST['password']);
        $command="$cmd"."v-check-user-password ".$v_user." ".$v_password." '".$_SERVER["REMOTE_ADDR"]."'";
        exec ($command, $output, $return_var);
        if ( $return_var > 0 ) {
            $ERROR = "<a class=\"error\">"._('ERROR: Invalid username or password')."</a>";
        } else {
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
    }
    require_once '../templates/header.html';
    require_once '../templates/login.html';
}
?>
