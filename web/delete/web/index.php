<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['domain'])) {
        $v_username = escapeshellarg($user);
        $v_domain = escapeshellarg($_GET['domain']);
        exec (VESTA_CMD."v_delete_web_domain ".$v_username." ".$v_domain, $output, $return_var);
        unset($output);

        // DNS
        if ($return_var == 0) {
            exec (VESTA_CMD."v_list_dns_domain ".$v_username." ".$v_domain." json", $output, $lreturn_var);
            if ($lreturn_var == 0 ) {
                exec (VESTA_CMD."v_delete_dns_domain ".$v_username." ".$v_domain, $output, $return_var);
                unset($output);
            }
        }

        // Mail
        if ($return_var == 0) {
            exec (VESTA_CMD."v_list_mail_domain ".$v_username." ".$v_domain." json", $output, $lreturn_var);
            if ($lreturn_var == 0 ) {
                exec (VESTA_CMD."v_delete_mail_domain ".$v_username." ".$v_domain, $output, $return_var);
            }
        }
    }
}

header("Location: /list/web/");
