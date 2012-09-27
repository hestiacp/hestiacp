<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$ip = $_POST['ip'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'delete': $cmd='v_delete_sys_ip';
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
