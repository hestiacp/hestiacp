<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'CRON';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user = $_GET['user'];
}

// Check job id
if (empty($_GET['job'])) {
    header("Location: /list/cron/");
    exit;
}

$v_username = $user;
$v_job = $_GET['job'];

// List cron job
v_exec('v-list-cron-job', [$user, $v_job, 'json'], true, $output);
$data = json_decode($output, true);

// Parse cron job
$v_min = $data[$v_job]['MIN'];
$v_hour = $data[$v_job]['HOUR'];
$v_day = $data[$v_job]['DAY'];
$v_month = $data[$v_job]['MONTH'];
$v_wday = $data[$v_job]['WDAY'];
$v_cmd = $data[$v_job]['CMD'];
$v_date = $data[$v_job]['DATE'];
$v_time = $data[$v_job]['TIME'];
$v_suspended = $data[$v_job]['SUSPENDED'];
$v_status = $v_suspended == 'yes' ? 'suspended' : 'active';

// Check POST request
if (!empty($_POST['save'])) {
    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit;
    }

    $v_min = $_POST['v_min'];
    $v_hour = $_POST['v_hour'];
    $v_day = $_POST['v_day'];
    $v_month = $_POST['v_month'];
    $v_wday = $_POST['v_wday'];
    $v_cmd = $_POST['v_cmd'];

    // Save changes
    v_exec('v-change-cron-job', [$v_username, $v_job, $v_min, $v_hour, $v_day, $v_month, $v_wday, $v_cmd]);

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __("Changes has been saved.");
    }
}

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Display body
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_cron.html');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
