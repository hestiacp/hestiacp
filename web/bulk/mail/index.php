<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$domain = $_POST['domain'];
$account = $_POST['account'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    if (empty($account)) {
        switch ($action) {
            case 'delete': $cmd='v_delete_mail_domain';
                break;
            case 'suspend': $cmd='v_suspend_mail_domain';
                break;
            case 'unsuspend': $cmd='v_unsuspend_mail_domain';
                break;
            default: header("Location: /list/mail/"); exit;
        }
    } else {
        switch ($action) {
            case 'delete': $cmd='v_delete_mail_account';
                break;
            case 'suspend': $cmd='v_suspend_mail_account';
                break;
            case 'unsuspend': $cmd='v_unsuspend_mail_account';
                break;
            default: header("Location: /list/mail/?domain=".$domain); exit;
        }
    }
} else {
    if (empty($account)) {
        switch ($action) {
            case 'delete': $cmd='v_delete_mail_domain';
                break;
            default: header("Location: /list/mail/"); exit;
        }
    } else {
        switch ($action) {
            case 'delete': $cmd='v_delete_mail_account';
                break;
            default: header("Location: /list/mail/?domain=".$domain); exit;
        }
    }
}


if (empty($account)) {
    foreach ($domain as $value) {
        // Mail
        $value = escapeshellarg($value);
        exec (VESTA_CMD.$cmd." ".$user." ".$value." no", $output, $return_var);
        $restart = 'yes';
    }
} else {
    foreach ($account as $value) {
        // Mail Account
        $value = escapeshellarg($value);
        $dom = escapeshellarg($domain);
        exec (VESTA_CMD.$cmd." ".$user." ".$dom." ".$value." no", $output, $return_var);
        $restart = 'yes';
    }
}

if (empty($account)) { 
    header("Location: /list/mail/");
    exit;
} else {
    header("Location: /list/mail/?domain=".$domain);
    exit;
}
