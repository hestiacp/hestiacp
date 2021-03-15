<?php
error_reporting(NULL);
ob_start();
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


// Check user argument
if (empty($_GET['user'])) {
    header("Location: /list/user/");
    exit;
}

// Edit as someone else?
if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['user']))) {
    $user=$_GET['user'];
    $v_username=$_GET['user'];
} else {
    $user=$_SESSION['user'];
    $v_username=$_SESSION['user'];
}
exec(HESTIA_CMD."v-list-user-auth-log ".escapeshellarg($v_username)." json", $output, $return_var);
check_return_code($return_var,$output);
$data = json_decode(implode('', $output), true);
$data = array_reverse($data);
unset($output);

// Render page
render_page($user, $TAB, 'list_auth');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);