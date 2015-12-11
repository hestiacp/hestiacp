<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$backup = $_POST['backup'];
$action = $_POST['action'];

// Check token
if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
    header('location: /login/');
    exit();
}

switch ($action) {
    case 'delete': $cmd='v-delete-user-backup';
        break;
    default: header("Location: /list/backup/"); exit;
}

foreach ($backup as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$user." ".$value, $output, $return_var);
}

header("Location: /list/backup/");
