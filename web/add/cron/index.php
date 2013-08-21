<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'CRON';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

if (!empty($_POST['ok'])) {
    // Check input
    if ((!isset($_POST['v_min'])) && ($_POST['v_min'] != '')) $errors[] = __('minute');
    if ((!isset($_POST['v_hour'])) && ($_POST['v_hour'] != '')) $errors[] = __('hour');
    if ((!isset($_POST['v_day'])) && ($_POST['v_day'] != '')) $errors[] = __('day');
    if ((!isset($_POST['v_month'])) && ($_POST['v_month'] != '')) $errors[] = __('month');
    if ((!isset($_POST['v_wday'])) && ($_POST['v_wday'] != '')) $errors[] = __('day of week');
    if ((!isset($_POST['v_cmd'])) && ($_POST['v_cmd'] != '')) $errors[] = __('cmd');

    // Protect input
    $v_min = escapeshellarg($_POST['v_min']);
    $v_hour = escapeshellarg($_POST['v_hour']);
    $v_day = escapeshellarg($_POST['v_day']);
    $v_month = escapeshellarg($_POST['v_month']);
    $v_wday = escapeshellarg($_POST['v_wday']);
    $v_cmd = escapeshellarg($_POST['v_cmd']);

    // Check for errors
    if (!empty($errors[0])) {
        foreach ($errors as $i => $error) {
            if ( $i == 0 ) {
                $error_msg = $error;
            } else {
                $error_msg = $error_msg.", ".$error;
            }
        }
        $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
    } else {
        // Add Cron Job
        exec (VESTA_CMD."v-add-cron-job ".$user." ".$v_min." ".$v_hour." ".$v_day." ".$v_month." ".$v_wday." ".$v_cmd, $output, $return_var);
        $v_type = $_POST['v_type'];
        $v_charset = $_POST['v_charset'];
        if ($return_var != 0) {
            $error = implode('<br>', $output);
            if (empty($error)) $error = __('Error code:',$return_var);
            $_SESSION['error_msg'] = $error;
            unset($v_password);
            unset($output);
        } else {
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
}

exec (VESTA_CMD."v-list-database-types 'json'", $output, $return_var);
$db_types = json_decode(implode('', $output), true);
unset($output);

include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_cron.html');
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
