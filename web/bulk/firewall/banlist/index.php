<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
    header('location: /login/');
    exit();
}

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

$ipchain = $_POST['ipchain'];
$action = $_POST['action'];

switch ($action) {
    case 'delete': $cmd='v-delete-firewall-ban';
        break;
    default: header("Location: /list/firewall/banlist/"); exit;
}

foreach ($ipchain as $value) {
    list($ip,$chain) = explode(":",$value);
    $v_ip    = escapeshellarg($ip);
    $v_chain = escapeshellarg($chain);
    exec (VESTA_CMD.$cmd." ".$v_ip." ".$v_chain, $output, $return_var);
}

header("Location: /list/firewall/banlist");
