<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
    header('location: /login/');
    exit;
}

$service = $_POST['service'];
$action = $_POST['action'];

if ($_SESSION['user'] == 'admin') {
    switch ($action) {
        case 'stop': $cmd='v-stop-service';
            break;
        case 'start': $cmd='v-start-service';
            break;
        case 'restart': $cmd='v-restart-service';
            break;
        default: header("Location: /list/server/"); exit;
    }

    if ((!empty($_POST['system'])) && ($action == 'restart')) {
        v_exec('v-restart-system', ['yes'], false);
        $_SESSION['error_srv'] = 'The system is going down for reboot NOW!';
        header("Location: /list/server/");
        exit;
    }

    foreach ($service as $value) {
        v_exec($cmd, [$value], false);
    }
}

header("Location: /list/server/");
