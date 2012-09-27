<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$domain = $_POST['domain'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v_delete_web_domain';
                        $cmd_dns='v_delete_dns_domain';
                        $cmd_mail='v_delete_mail_domain';
            break;
        case 'suspend': $cmd='v_suspend_web_domain';
                        $cmd_dns='v_suspend_dns_domain';
                        $cmd_mail='v_suspend_mail_domain';
            break;
        case 'unsuspend': $cmd='v_unsuspend_web_domain';
                        $cmd_dns='v_unsuspend_dns_domain';
                        $cmd_mail='v_unsuspend_mail_domain';
            break;
        default: header("Location: /list/web/"); exit;
    }
} else {
    switch ($action) {
        case 'delete': $cmd='v_delete_web_domain';
                        $cmd_dns='v_delete_dns_domain';
                        $cmd_mail='v_delete_mail_domain';
            break;
        default: header("Location: /list/web/"); exit;
    }
}

foreach ($domain as $value) {
    // WEB
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$user." ".$value." no", $output, $return_var);
    $restart_web = 'yes';

    // DNS
    if ($return_var == 0) {
        exec (VESTA_CMD."v_list_dns_domain ".$user." ".$value." json", $output, $lreturn_var);
        if ($lreturn_var == 0 ) {
            exec (VESTA_CMD.$cmd_dns." ".$user." ".$value." no", $output, $return_var);
            $restart_dns = 'yes';
        }
    }

    // Mail
    if ($return_var == 0) {
        exec (VESTA_CMD."v_list_mail_domain ".$user." ".$value." json", $output, $lreturn_var);
        if ($lreturn_var == 0 ) {
            exec (VESTA_CMD.$cmd_mail." ".$user." ".$value." no", $output, $return_var);
        }
    }
}

if (!empty($restart_web)) {
    exec (VESTA_CMD."v_restart_web", $output, $return_var);
}

if (!empty($restart_dns)) {
    exec (VESTA_CMD."v_restart_dns", $output, $return_var);
}

header("Location: /list/web/");
