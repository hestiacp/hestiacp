<?php
// Init
error_reporting(NULL);
session_start();
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
if ((!isset($_GET['token'])) || ($_SESSION['token'] != $_GET['token'])) {
    header('location: /login/');
    exit();
}

// Clear log
$v_username = escapeshellarg($user);
exec (HESTIA_CMD."v-delete-user-auth-log ".$v_username, $output, $return_var);
//check_return_code($return_var,$output);
//unset($output);


$ip = $_SERVER['REMOTE_ADDR'];
if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
    if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
} 
$v_ip = escapeshellarg($ip);
    
$v_murmur = escapeshellarg($_SESSION['MURMUR']);
exec(HESTIA_CMD."v-log-user-login ".$v_username." ".$v_ip." success ".$v_murmur, $output, $return_var);

// Render page
//render_page($user, $TAB, 'list_auth');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

header("Location: /edit/user/log/?user=".$_SESSION['user']);

exit;
