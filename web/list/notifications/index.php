<?php
error_reporting(NULL);

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


if ($_REQUEST['ajax'] == 1) {
    // Data
    v_exec('v-list-user-notifications', [$user, 'json'], false, $output);
    $data = json_decode($output, true);
    $data = array_reverse($data, true);
    foreach ($data as $key => $note) {
        $note['ID'] = $key;
        $data[$key] = $note;
    }
    echo json_encode($data);
    exit;
}



$TAB = 'NOTIFICATIONS';
// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Data
v_exec('v-list-user-notifications', [$user, 'json'], false, $output);
$data = json_decode($output, true);
$data = array_reverse($data, true);
if ($_SESSION['user'] == 'admin') {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/list_notifications.html');
} else {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/list_notifications.html');
}

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
