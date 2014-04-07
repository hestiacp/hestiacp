<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

$TAB = 'BACKUP EXCLUSIONS';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

exec (VESTA_CMD."v-list-user-backup-exclusions ".$user." 'json'", $output, $return_var);
check_return_code($return_var,$output);
if (empty($_SESSION['error_msg'])) {
    $data = json_decode(implode('', $output), true);
    unset($output);
    $v_username = $user;

    foreach ($data['WEB'] as $key => $value) {
        if (!empty($value)){
            $v_web .= $key . ":" . $value. "\n";
        } else {
            $v_web .= $key . "\n";
        }
    }

    foreach ($data['DNS'] as $key => $value) {
        if (!empty($value)){
            $v_dns .= $key . ":" . $value. "\n";
        } else {
            $v_dns .= $key . "\n";
        }
    }

    foreach ($data['MAIL'] as $key => $value) {
        if (!empty($value)){
            $v_mail .= $key . ":" . $value. "\n";
        } else {
            $v_mail .= $key . "\n";
        }
    }

    foreach ($data['DB'] as $key => $value) {
        if (!empty($value)){
            $v_db .= $key . ":" . $value. "\n";
        } else {
            $v_db .= $key . "\n";
        }
    }

    foreach ($data['USER'] as $key => $value) {
        if (!empty($value)){
            $v_userdir .= $key . ":" . $value. "\n";
        } else {
            $v_userdir .= $key . "\n";
        }
    }


    // Action
    if (!empty($_POST['save'])) {

        $v_web = $_POST['v_web'];
        $v_web_tmp = str_replace("\r\n", ",", $_POST['v_web']);
        $v_web_tmp = rtrim($v_web_tmp, ",");
        $v_web_tmp = "WEB=" . escapeshellarg($v_web_tmp);

        $v_dns = $_POST['v_dns'];
        $v_dns_tmp = str_replace("\r\n", ",", $_POST['v_dns']);
        $v_dns_tmp = rtrim($v_dns_tmp, ",");
        $v_dns_tmp = "DNS=" . escapeshellarg($v_dns_tmp);

        $v_mail = $_POST['v_mail'];
        $v_mail_tmp = str_replace("\r\n", ",", $_POST['v_mail']);
        $v_mail_tmp = rtrim($v_mail_tmp, ",");
        $v_mail_tmp = "MAIL=" . escapeshellarg($v_mail_tmp);

        $v_db = $_POST['v_db'];
        $v_db_tmp = str_replace("\r\n", ",", $_POST['v_db']);
        $v_db_tmp = rtrim($v_db_tmp, ",");
        $v_db_tmp = "DB=" . escapeshellarg($v_db_tmp);

        $v_cron = $_POST['v_cron'];
        $v_cron_tmp = str_replace("\r\n", ",", $_POST['v_cron']);
        $v_cron_tmp = rtrim($v_cron_tmp, ",");
        $v_cron_tmp = "CRON=" . escapeshellarg($v_cron_tmp);

        $v_userdir = $_POST['v_userdir'];
        $v_userdir_tmp = str_replace("\r\n", ",", $_POST['v_userdir']);
        $v_userdir_tmp = rtrim($v_userdir_tmp, ",");
        $v_userdir_tmp = "USER=" . escapeshellarg($v_userdir_tmp);

        exec ('mktemp', $mktemp_output, $return_var);
        $tmp = $mktemp_output[0];
        $fp = fopen($tmp, 'w');
        fwrite($fp, $v_web_tmp . "\n" . $v_dns_tmp . "\n" . $v_mail_tmp . "\n" .  $v_db_tmp . "\n" . $v_userdir_tmp . "\n");
        fclose($fp);
        exec (VESTA_CMD."v-update-user-backup-exclusions ".$user." ".$tmp, $output, $return_var);
        check_return_code($return_var,$output);
        if (empty($_SESSION['error_msg'])) {
            $_SESSION['ok_msg'] = __("Changes has been saved.");
        }
    }
}

include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_backup_exclusions.html');
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
