<?php

// Init
error_reporting(null);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_POST);

$database = $_POST['database'];
$action = $_POST['action'];

if ($_SESSION['userContext'] === 'admin') {
    switch ($action) {
        case 'rebuild': $cmd='v-rebuild-database';
            break;
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
    exec(HESTIA_CMD.$cmd." ".$user." ".$value, $output, $return_var);
}

header("Location: /list/db/");
