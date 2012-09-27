<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$database = $_POST['database'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v_delete_database';
            break;
        case 'suspend': $cmd='v_suspend_database';
            break;
        case 'unsuspend': $cmd='v_unsuspend_database';
            break;
        default: header("Location: /list/db/"); exit;
    }
} else {
    switch ($action) {
        case 'delete': $cmd='v_delete_database';
            break;
        default: header("Location: /list/db/"); exit;
    }
}

foreach ($database as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$user." ".$value, $output, $return_var);
}

header("Location: /list/db/");
