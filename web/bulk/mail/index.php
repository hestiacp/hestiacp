<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_POST);

$domain = $_POST['domain'];
$account = $_POST['account'];
$action = $_POST['action'];

if ($_SESSION['userContext'] === 'admin') {
    if (empty($account)) {
        switch ($action) {
            case 'rebuild': $cmd='v-rebuild-mail-domain';
                break;
            case 'delete': $cmd='v-delete-mail-domain';
                break;
            case 'suspend': $cmd='v-suspend-mail-domain';
                break;
            case 'unsuspend': $cmd='v-unsuspend-mail-domain';
                break;
            default: header("Location: /list/mail/"); exit;
        }
    } else {
        switch ($action) {
            case 'delete': $cmd='v-delete-mail-account';
                break;
            case 'suspend': $cmd='v-suspend-mail-account';
                break;
            case 'unsuspend': $cmd='v-unsuspend-mail-account';
                break;
            default: header("Location: /list/mail/?domain=".$domain); exit;
        }
    }
} else {
    if (empty($account)) {
        switch ($action) {
            case 'delete': $cmd='v-delete-mail-domain';
                break;
            case 'suspend': $cmd='v-suspend-mail-domain';
                break;
            case 'unsuspend': $cmd='v-unsuspend-mail-domain';
                break;
            default: header("Location: /list/mail/"); exit;
        }
    } else {
        switch ($action) {
            case 'delete': $cmd='v-delete-mail-account';
                break;
            case 'suspend': $cmd='v-suspend-mail-account';
                break;
            case 'unsuspend': $cmd='v-unsuspend-mail-account';
                break;
            default: header("Location: /list/mail/?domain=".$domain); exit;
        }
    }
}


if (empty($account)) {
    foreach ($domain as $value) {
        // Mail
        $value = quoteshellarg($value);
        exec(HESTIA_CMD.$cmd." ".$user." ".$value, $output, $return_var);
        $restart = 'yes';
    }
} else {
    foreach ($account as $value) {
        // Mail Account
        $value = quoteshellarg($value);
        $dom = quoteshellarg($domain);
        exec(HESTIA_CMD.$cmd." ".$user." ".$dom." ".$value, $output, $return_var);
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
