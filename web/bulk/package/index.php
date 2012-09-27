<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$package = $_POST['package'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v_delete_user_package';
            break;
        default: header("Location: /list/package/"); exit;
    }
} else {
    header("Location: /list/package/");
    exit;
}

foreach ($package as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$value, $output, $return_var);
    $restart = 'yes';
}


header("Location: /list/package/");
