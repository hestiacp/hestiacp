<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
    header('location: /login/');
    exit;
}

$domain = $_POST['domain'];
$account = $_POST['account'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
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
} else {
    if (empty($account)) {
        switch ($action) {
            case 'delete': $cmd='v-delete-mail-domain';
                break;
            default: header("Location: /list/mail/"); exit;
        }
    } else {
        switch ($action) {
            case 'delete': $cmd='v-delete-mail-account';
                break;
            default: header("Location: /list/mail/?domain=".$domain); exit;
        }
    }
}


if (empty($account)) {
    foreach ($domain as $value) {
        // Mail
        v_exec($cmd, [$user, $value], false);
        $restart = 'yes';
    }
} else {
    foreach ($account as $value) {
        // Mail Account
        v_exec($cmd, [$user, $domain, $value], false);
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
