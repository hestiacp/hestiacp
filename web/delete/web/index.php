<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Delete as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=$_GET['user'];
}

if (!empty($_GET['domain'])) {
    $v_username = escapeshellarg($user);
    $v_domain = escapeshellarg($_GET['domain']);
    exec (VESTA_CMD."v-delete-web-domain ".$v_username." ".$v_domain, $output, $return_var);
    check_return_code($return_var,$output);
    unset($output);

    // DNS
    if (empty($_SESSION['error_msg'])) {
        exec (VESTA_CMD."v-list-dns-domain ".$v_username." ".$v_domain." json", $output, $lreturn_var);
        if ($lreturn_var == 0 ) {
            exec (VESTA_CMD."v-delete-dns-domain ".$v_username." ".$v_domain, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }
    }

    // Mail
    if (empty($_SESSION['error_msg'])) {
        exec (VESTA_CMD."v-list-mail-domain ".$v_username." ".$v_domain." json", $output, $lreturn_var);
        if ($lreturn_var == 0 ) {
            exec (VESTA_CMD."v-delete-mail-domain ".$v_username." ".$v_domain, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }
    }
}

$back = $_SESSION['back'];
if (!empty($back)) {
    header("Location: ".$back);
    exit;
}

header("Location: /list/web/");
exit;
