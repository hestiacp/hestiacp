<?php
error_reporting(NULL);
ob_start();
$TAB = 'CRON';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check POST request
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Check empty fields
    if ((!isset($_POST['v_min'])) || ($_POST['v_min'] == '')) $errors[] = __('minute');
    if ((!isset($_POST['v_hour'])) || ($_POST['v_hour'] == '')) $errors[] = __('hour');
    if ((!isset($_POST['v_day'])) || ($_POST['v_day'] == '')) $errors[] = __('day');
    if ((!isset($_POST['v_month'])) || ($_POST['v_month'] == '')) $errors[] = __('month');
    if ((!isset($_POST['v_wday'])) || ($_POST['v_wday'] == '')) $errors[] = __('day of week');
    if ((!isset($_POST['v_cmd'])) || ($_POST['v_cmd'] == '')) $errors[] = __('cmd');
    if (!empty($errors[0])) {
        foreach ($errors as $i => $error) {
            if ( $i == 0 ) {
                $error_msg = $error;
            } else {
                $error_msg = $error_msg.", ".$error;
            }
        }
        $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
    }

    // Protect input
    $v_min = escapeshellarg($_POST['v_min']);
    $v_hour = escapeshellarg($_POST['v_hour']);
    $v_day = escapeshellarg($_POST['v_day']);
    $v_month = escapeshellarg($_POST['v_month']);
    $v_wday = escapeshellarg($_POST['v_wday']);
    $v_cmd = escapeshellarg($_POST['v_cmd']);

    // Add cron job
    if (empty($_SESSION['error_msg'])) {
        exec (VESTA_CMD."v-add-cron-job ".$user." ".$v_min." ".$v_hour." ".$v_day." ".$v_month." ".$v_wday." ".$v_cmd, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('CRON_CREATED_OK');
        unset($v_min);
        unset($v_hour);
        unset($v_day);
        unset($v_month);
        unset($v_wday);
        unset($v_cmd);
        unset($output);
    }
}

// Render
render_page($user, $TAB, 'add_cron');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
