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
check_return_code($return_var,$output);
unset($output);


$ip = $_SERVER['REMOTE_ADDR'];
if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
    if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
} 
$v_ip = escapeshellarg($ip);
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$v_user_agent = escapeshellarg($user_agent);
    
$v_session_id = escapeshellarg($_SESSION['token']);

// Add current user session back to log unless impersonating another user
if (!isset($_SESSION['look'])) {
    exec(HESTIA_CMD."v-log-user-login ".$v_username." ".$v_ip." success ".$v_session_id." ".$v_user_agent, $output, $return_var);
}

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Return to authentication history
header("Location: /list/log/auth/");

exit;
