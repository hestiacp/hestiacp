<?php

ob_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
verify_csrf($_POST);


$pkg = $_POST['pkg'];
$action = $_POST['action'];

if ($_SESSION['userContext'] === 'admin') {
    switch ($action) {
        case 'update': $cmd='v-update-sys-hestia';
            break;
        default: header("Location: /list/updates/"); exit;
    }
    foreach ($pkg as $value) {
        $value = quoteshellarg($value);
        exec(HESTIA_CMD.$cmd." ".$value, $output, $return_var);
    }
}

header("Location: /list/updates/");
