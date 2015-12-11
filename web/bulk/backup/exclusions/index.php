<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$backup = $_POST['system'];
$action = $_POST['action'];

switch ($action) {
    case 'delete': $cmd='v-delete-user-backup-exclusions';
        break;
    default: header("Location: /list/backup/exclusions"); exit;
}

foreach ($backup as $value) {
    $value = escapeshellarg($value);
    exec (VESTA_CMD.$cmd." ".$user." ".$value, $output, $return_var);
}

header("Location: /list/backup/exclusions");
