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

$database = $_POST['database'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v-delete-database';
            break;
        case 'suspend': $cmd='v-suspend-database';
            break;
        case 'unsuspend': $cmd='v-unsuspend-database';
            break;
        default: header("Location: /list/db/"); exit;
    }
} else {
    switch ($action) {
        case 'delete': $cmd='v-delete-database';
            break;
        default: header("Location: /list/db/"); exit;
    }
}

foreach ($database as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$user." ".$value, $output, $return_var);
}

header("Location: /list/db/");
