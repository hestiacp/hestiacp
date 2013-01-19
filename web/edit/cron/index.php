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

    // Edit as someone else?
    if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
        $user=escapeshellarg($_GET['user']);
    }

    // Check user argument?
    if (empty($_GET['job'])) {
        header("Location: /list/cron/");
        exit;
    }

    $v_job = escapeshellarg($_GET['job']);
    exec (VESTA_CMD."v-list-cron-job ".$user." ".$v_job." 'json'", $output, $return_var);
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = _('Error: vesta did not return any output.');
        $_SESSION['error_msg'] = $error;
    } else {
        $data = json_decode(implode('', $output), true);
        unset($output);
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

        // Action
        if (!empty($_POST['save'])) {
            $v_username = $user;
            // Change job
            if (($v_min != $_POST['v_min']) || ($v_hour != $_POST['v_hour']) || ($v_day != $_POST['v_day']) || ($v_month != $_POST['v_month']) || ($v_wday != $_POST['v_wday']) || ($v_cmd != $_POST['v_cmd']) &&(empty($_SESSION['error_msg']))) {
                $v_min = escapeshellarg($_POST['v_min']);
                $v_hour = escapeshellarg($_POST['v_hour']);
                $v_day = escapeshellarg($_POST['v_day']);
                $v_month = escapeshellarg($_POST['v_month']);
                $v_wday = escapeshellarg($_POST['v_wday']);
                $v_cmd = escapeshellarg($_POST['v_cmd']);
                exec (VESTA_CMD."v-change-cron-job ".$v_username." ".$v_job." ".$v_min." ".$v_hour." ".$v_day." ".$v_month." ".$v_wday." ".$v_cmd, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
                $v_cmd = $_POST['v_cmd'];
            }
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = _("OK: changes has been saved.");
            }
        }
    }

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_cron.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
//}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
