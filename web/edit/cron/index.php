<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'CRON';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

// Check job id
if (empty($_GET['job'])) {
    header("Location: /list/cron/");
    exit;
}

// List cron job
$v_job = escapeshellarg($_GET['job']);
exec (VESTA_CMD."v-list-cron-job ".$user." ".$v_job." 'json'", $output, $return_var);
check_return_code($return_var,$output);

$data = json_decode(implode('', $output), true);
unset($output);

// Parse cron job
$v_username = $user;
$v_job = $_GET['job'];
$v_min = $data[$v_job]['MIN'];
$v_hour = $data[$v_job]['HOUR'];
$v_day = $data[$v_job]['DAY'];
$v_month = $data[$v_job]['MONTH'];
$v_wday = $data[$v_job]['WDAY'];
$v_cmd = $data[$v_job]['CMD'];
$v_date = $data[$v_job]['DATE'];
$v_time = $data[$v_job]['TIME'];
$v_suspended = $data[$v_job]['SUSPENDED'];
if ( $v_suspended == 'yes' ) {
    $v_status =  'suspended';
} else {
    $v_status =  'active';
}

// Check POST request
if (!empty($_POST['save'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    $v_username = $user;
    $v_job = escapeshellarg($_GET['job']);
    $v_min = escapeshellarg($_POST['v_min']);
    $v_hour = escapeshellarg($_POST['v_hour']);
    $v_day = escapeshellarg($_POST['v_day']);
    $v_month = escapeshellarg($_POST['v_month']);
    $v_wday = escapeshellarg($_POST['v_wday']);
    $v_cmd = escapeshellarg($_POST['v_cmd']);

    // Save changes
    exec (VESTA_CMD."v-change-cron-job ".$v_username." ".$v_job." ".$v_min." ".$v_hour." ".$v_day." ".$v_month." ".$v_wday." ".$v_cmd, $output, $return_var);
    check_return_code($return_var,$output);
    unset($output);

    $v_cmd = $_POST['v_cmd'];

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __("Changes has been saved.");
    }
}

// Render page
render_page($user, $TAB, 'edit_cron');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
