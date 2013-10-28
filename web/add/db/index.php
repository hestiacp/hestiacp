<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'DB';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

$v_db_email = $panel[$user]['CONTACT'];

if (!empty($_POST['ok'])) {
    // Check input
    if (empty($_POST['v_database'])) $errors[] = __('database');
    if (empty($_POST['v_dbuser'])) $errors[] = __('username');
    if (empty($_POST['v_password'])) $errors[] = __('password');
    if (empty($_POST['v_type'])) $errors[] = __('type');
    if (empty($_POST['v_charset'])) $errors[] = __('charset');

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
    }

    // Validate email
    if (!empty($_POST['v_db_email'])) {
        if (!filter_var($_POST['v_db_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_msg'] = __('Please enter valid email address.');
        }
    }

    // Protect input
    $v_database = escapeshellarg($_POST['v_database']);
    $v_dbuser = escapeshellarg($_POST['v_dbuser']);
    $v_password = escapeshellarg($_POST['v_password']);
    $v_type = $_POST['v_type'];
    $v_charset = $_POST['v_charset'];
    $v_db_email = $_POST['v_db_email'];

    // Check password length
    if (empty($_SESSION['error_msg'])) {
        $pw_len = strlen($_POST['v_password']);
        if ($pw_len < 6 ) $_SESSION['error_msg'] = __('Password is too short.',$error_msg);
    }

    if (empty($_SESSION['error_msg'])) {
        // Add Database
        $v_type = escapeshellarg($_POST['v_type']);
        $v_charset = escapeshellarg($_POST['v_charset']);
        exec (VESTA_CMD."v-add-database ".$user." ".$v_database." ".$v_dbuser." ".$v_password." ".$v_type." 'default' ".$v_charset, $output, $return_var);
        $v_type = $_POST['v_type'];
        $v_charset = $_POST['v_charset'];
        check_return_code($return_var,$output);
        unset($output);
        if ((!empty($v_db_email)) && (empty($_SESSION['error_msg']))) {
            list($http_host, $port) = explode(':', $_SERVER["HTTP_HOST"]);
            if ($_POST['v_type'] == 'mysql') $db_admin_link = "http://".$http_host."/phpmyadmin/";
            if ($_POST['v_type'] == 'pgsql') $db_admin_link = "http://".$http_host."/phppgadmin/";
            $to = $v_db_email;
            $subject = __("Database Credentials");
            $hostname = exec('hostname');
            $from = __('MAIL_FROM',$hostname);
            $mailtext = __('DATABASE_READY',$user."_".$_POST['v_database'],$user."_".$_POST['v_dbuser'],$_POST['v_password'],$db_admin_link);
            send_email($to, $subject, $mailtext, $from);
        }
        if (empty($_SESSION['error_msg'])) {
            $_SESSION['ok_msg'] = __('DATABASE_CREATED_OK',$user."_".$_POST['v_database'],$user."_".$_POST['v_database']);
            unset($v_database);
            unset($v_dbuser);
            unset($v_password);
            unset($v_type);
            unset($v_charset);
        }
    }
}

exec (VESTA_CMD."v-list-database-types 'json'", $output, $return_var);
$db_types = json_decode(implode('', $output), true);
unset($output);

include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_db.html');
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
?>
