<?php
// Init
//error_reporting(NULL);
ob_start();
session_start();
$TAB = 'WEB';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
if ($_SESSION['user'] == 'admin') {

    // Cancel
    if (!empty($_POST['back'])) {
        header("Location: /list/web/");
    }

    // Ok
    if (!empty($_GET['domain'])) {
        $v_username = escapeshellarg($user);
        $v_domain = escapeshellarg($_GET['domain']);
        exec (VESTA_CMD."v_unsuspend_web_domain ".$v_username." ".$v_domain, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
        } else {
            $_SESSION['ok_msg'] = "OK: web domain <b>".$_GET['domain']."</b> has been unsuspended.";
                unset($v_lname);
        }
        unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_unsuspend_web.html');
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/unsuspend_web.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);

    } else {
        header("Location: /list/web/");
    }

}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
