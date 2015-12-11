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
$record = $_POST['record'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    if (empty($record)) {
        switch ($action) {
            case 'delete': $cmd='v-delete-dns-domain';
                break;
            case 'suspend': $cmd='v-suspend-dns-domain';
                break;
            case 'unsuspend': $cmd='v-unsuspend-dns-domain';
                break;
            default: header("Location: /list/dns/"); exit;
        }
    } else {
        switch ($action) {
            case 'delete': $cmd='v-delete-dns-record';
                break;
            case 'suspend': $cmd='v-suspend-dns-record';
                break;
            case 'unsuspend': $cmd='v-unsuspend-dns-record';
                break;
            default: header("Location: /list/dns/?domain=".$domain); exit;
        }
    }
} else {
    if (empty($record)) {
        switch ($action) {
            case 'delete': $cmd='v-delete-dns-domain';
                break;
            default: header("Location: /list/dns/"); exit;
        }
    } else {
        switch ($action) {
            case 'delete': $cmd='v-delete-dns-record';
                break;
            default: header("Location: /list/dns/?domain=".$domain); exit;
        }
    }
}


if (empty($record)) {
    foreach ($domain as $value) {
        // DNS
        v_exec($cmd, [$user, $value, 'no'], false);
        $restart = 'yes';
    }
} else {
    foreach ($record as $value) {
        // DNS Record
        v_exec($cmd, [$user, $domain, $value, 'no'], false);
        $restart = 'yes';
    }
}

if (!empty($restart)) {
    v_exec('v-restart-dns', [], false);
}

if (empty($record)) {
    header("Location: /list/dns/");
    exit;
} else {
    header("Location: /list/dns/?domain=".$domain);
    exit;
}
