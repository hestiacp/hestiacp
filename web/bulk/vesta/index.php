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


$pkg = $_POST['pkg'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'update': $cmd='v-update-sys-vesta';
            break;
        default: header("Location: /list/updates/"); exit;
    }
    foreach ($pkg as $value) {
        $value = escapeshellarg($value);
        exec (VESTA_CMD.$cmd." ".$value, $output, $return_var);
    }
}

header("Location: /list/updates/");
