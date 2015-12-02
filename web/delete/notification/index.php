<?php
// Init
error_reporting(NULL);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit;
}


if($_GET['delete'] == 1){
    $v_id = (string)((int)$_GET['notification_id']);
    v_exec('v-delete-user-notification', [$user, $v_id]);
} else {
    $v_id = (string)((int)$_GET['notification_id']);
    //echo VESTA_CMD."v-acknowledge-user-notification ".$v_username." ".$v_id;
    v_exec('v-acknowledge-user-notification', [$user, $v_id]);
}

exit;
