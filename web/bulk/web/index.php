<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
    header('location: /login/');
    exit();
}

$domain = $_POST['domain'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v-delete-domain';
            break;
        case 'suspend': $cmd='v-suspend-domain';
            break;
        case 'unsuspend': $cmd='v-unsuspend-domain';
            break;
        default: header("Location: /list/web/"); exit;
    }
} else {
    switch ($action) {
        case 'delete': $cmd='v-delete-domain';
            break;
        default: header("Location: /list/web/"); exit;
    }
}

foreach ($domain as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$user." ".$value." no", $output, $return_var);
    $restart='yes';
}

if (isset($restart)) {
    exec (VESTA_CMD."v-restart-web", $output, $return_var);
    exec (VESTA_CMD."v-restart-proxy", $output, $return_var);
    exec (VESTA_CMD."v-restart-dns", $output, $return_var);
}

header("Location: /list/web/");
