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
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = _('Error: vesta did not return any output.');
            $_SESSION['error_msg'] = $error;
    }
    unset($output);

    // DNS
    if ($return_var == 0) {
        exec (VESTA_CMD."v-list-dns-domain ".$v_username." ".$v_domain." json", $output, $lreturn_var);
        if ($lreturn_var == 0 ) {
            exec (VESTA_CMD."v-delete-dns-domain ".$v_username." ".$v_domain, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = _('Error: vesta did not return any output.');
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
        }
    }

    // Mail
    if ($return_var == 0) {
        exec (VESTA_CMD."v-list-mail-domain ".$v_username." ".$v_domain." json", $output, $lreturn_var);
        if ($lreturn_var == 0 ) {
            exec (VESTA_CMD."v-delete-mail-domain ".$v_username." ".$v_domain, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
            }
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
