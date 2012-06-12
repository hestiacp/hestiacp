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
        exec (VESTA_CMD."v_delete_web_domain ".$v_username." ".$v_domain, $output, $return_var);
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = 'Error: vesta did not return any output.';
            $_SESSION['error_msg'] = $error;
        }

        // DNS
        unset($output);
        exec (VESTA_CMD."v_list_dns_domain ".$v_username." ".$v_domain." json", $output, $return_var);
        if ((empty($_SESSION['error_msg'])) && ($return_var == 0 )) {
            exec (VESTA_CMD."v_delete_dns_domain ".$v_username." ".$v_domain, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = 'Error: vesta did not return any output.';
                $_SESSION['error_msg'] = $error;
            }
        }

        // Mail
        unset($output);
        exec (VESTA_CMD."v_list_mail_domain ".$v_username." ".$v_domain." json", $output, $return_var);
        if ((empty($_SESSION['error_msg'])) && ($return_var == 0 )) {
            exec (VESTA_CMD."v_delete_mail_domain ".$v_username." ".$v_domain, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = 'Error: vesta did not return any output.';
                $_SESSION['error_msg'] = $error;
            }
        }


        if (empty($_SESSION['error_msg'])) {
            $_SESSION['ok_msg'] = "OK: domain <b>".$_GET['domain']."</b> has been deleted.";
            unset($v_lname);
        }



    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_delete_web.html');
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/delete_web.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);

    } else {
        header("Location: /list/web/");
    }

}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
