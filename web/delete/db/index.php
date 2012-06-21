<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['user'] == 'admin') {
    if (!empty($_GET['database'])) {
        $v_username = escapeshellarg($user);
        $v_database = escapeshellarg($_GET['database']);
        exec (VESTA_CMD."v_delete_database ".$v_username." ".$v_database, $output, $return_var);
        unset($output);
    }
}

header("Location: /list/db/");
