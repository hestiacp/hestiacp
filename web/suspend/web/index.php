<?php
// Init
//error_reporting(NULL);
ob_start();
session_start();
$TAB = 'USER';
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
    if (!empty($_GET['user'])) {
        $v_username = escapeshellarg($_GET['user']);
        exec (VESTA_CMD."v_suspend_web_domain ".$v_username, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
        } else {
            $_SESSION['ok_msg'] = "OK: user <b>".$_GET[user]."</b> has been suspended.";
                unset($v_lname);
        }
        unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_suspend_user.html');
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/suspend_user.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);

    } else {
        header("Location: /list/user/");
    }

}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
