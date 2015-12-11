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

$ip = $_POST['ip'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'reread IP': $cmd = 'v-update-sys-ip';
            v_exec($cmd, [], false);
            header('Location: /list/ip/');
            exit;
        case 'delete': $cmd = 'v-delete-sys-ip';
            break;
        default: header("Location: /list/ip/"); exit;
    }
} else {
    header("Location: /list/ip/");
    exit;
}

foreach ($ip as $value) {
    v_exec($cmd, [$value], false);
}

header("Location: /list/ip/");
