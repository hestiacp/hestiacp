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

$ip = $_POST['ip'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'reread IP': exec(VESTA_CMD."v-update-sys-ip", $output, $return_var);
                header("Location: /list/ip/");
                exit;
            break;
        case 'delete': $cmd='v-delete-sys-ip';
            break;
        default: header("Location: /list/ip/"); exit;
    }
} else {
    header("Location: /list/ip/");
    exit;
}

foreach ($ip as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$value, $output, $return_var);
}

header("Location: /list/ip/");
