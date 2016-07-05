<?php
error_reporting(NULL);

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


if($_REQUEST['ajax'] == 1){
    // Data
    exec (VESTA_CMD."v-list-user-notifications $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data,true);
    foreach($data as $key => $note){
        $note['ID'] = $key;
        $data[$key] = $note;
    }
    echo json_encode($data);
    exit;
}



$TAB = 'NOTIFICATIONS';

// Data
exec (VESTA_CMD."v-list-user-notifications $user json", $output, $return_var);
$data = json_decode(implode('', $output), true);
$data = array_reverse($data,true);

// Render page
render_page($user, $TAB, 'list_notifications');

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];
