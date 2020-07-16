<?php
error_reporting(NULL);
$TAB = 'SERVER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check POST request
if (!empty($_POST['save'])) {

    // Set success message
    $_SESSION['ok_msg'] = _('Info (Read-only mode): Crontab can be edited only trough ssh');

}

$v_config_path = '/etc/crontab';
$v_service_name = strtoupper('cron');

// Read config
$v_config = shell_exec(HESTIA_CMD."v-open-fs-config ".$v_config_path);

// Render page
render_page($user, $TAB, 'edit_server_service');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
