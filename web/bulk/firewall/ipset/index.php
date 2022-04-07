<?php

ob_start();

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_POST);

// Check user
if ($_SESSION['userContext'] != 'admin') {
    header("Location: /list/user");
    exit;
}

$setname = $_POST['setname'];
$action = $_POST['action'];
switch ($action) {
    case 'delete': $cmd='v-delete-firewall-ipset';
        break;
    default: header("Location: /list/firewall/ipset/"); exit;
}


foreach ($setname as $value) {
    $v_name = escapeshellarg($value);
    exec(HESTIA_CMD.$cmd." ".$v_name, $output, $return_var);
}

header("Location: /list/firewall/ipset/");
