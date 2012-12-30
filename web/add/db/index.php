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

// Are you admin?
//if ($_SESSION['user'] == 'admin') {
    if (!empty($_POST['ok'])) {
        // Check input
        if (empty($_POST['v_database'])) $errors[] = 'database';
        if (empty($_POST['v_dbuser'])) $errors[] = 'username';
        if (empty($_POST['v_password'])) $errors[] = 'password';
        if (empty($_POST['v_type'])) $errors[] = 'type';
        if (empty($_POST['v_charset'])) $errors[] = 'charset';

        // Protect input
        $v_database = escapeshellarg($_POST['v_database']);
        $v_dbuser = escapeshellarg($_POST['v_dbuser']);
        $v_password = escapeshellarg($_POST['v_password']);
        $v_type = $_POST['v_type'];
        $v_charset = $_POST['v_charset'];
        if (empty($_POST['v_notify'])) $v_notify = 'off';

        // Check for errors
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = "Error: field ".$error_msg." can not be blank.";
        } else {
            // Add Database
            $v_type = escapeshellarg($_POST['v_type']);
            $v_charset = escapeshellarg($_POST['v_charset']);
            exec (VESTA_CMD."v-add-database ".$user." ".$v_database." ".$v_dbuser." ".$v_password." ".$v_type." 'default' ".$v_charset, $output, $return_var);
            $v_type = $_POST['v_type'];
            $v_charset = $_POST['v_charset'];
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = 'Error: vesta did not return any output.';
                $_SESSION['error_msg'] = $error;
                unset($v_password);
                unset($output);
            } else {
                if (empty($v_notify)) {
                    list($http_host, $port) = explode(':', $_SERVER["HTTP_HOST"]);
                    if ($_POST['v_type'] == 'mysql') $db_admin_link = "http://".$http_host."/phpMyAdmin/";
                    if ($_POST['v_type'] == 'pgsql') $db_admin_link = "http://".$http_host."/phpPgAdmin/";

                    $to = $panel[$user]['CONTACT'];
                    $subject = "Database Credentials";
                    $hostname = exec('hostname');
                    $from = "Vesta Control Panel <noreply@".$hostname.">";
                    $mailtext = "Hello ".$panel[$user]['FNAME']." ".$panel[$user]['LNAME'].",\n";
                    $mailtext .= "your ".$_POST['v_type']." database has been created successfully.\n\n";
                    $mailtext .= "database: ".$user."_".$_POST['v_database']."\n";
                    $mailtext .= "username: ".$user."_".$_POST['v_dbuser']."\n";
                    $mailtext .= "password: ".$_POST['v_password']."\n\n";
                    $mailtext .= $db_admin_link."\n\n";

                    $mailtext .= "--\nVesta Control Panel\n";
                    send_email($to, $subject, $mailtext, $from);
                }
                $_SESSION['ok_msg'] = "OK: database <a href='/edit/db/?database=".$user."_".$_POST['v_database']."'><b>".$user."_".$_POST['v_database']."</b></a> has been created successfully.";
                unset($v_database);
                unset($v_dbuser);
                unset($v_password);
                unset($v_type);
                unset($v_charset);
                unset($output);
            }
        }
    }
    exec (VESTA_CMD."v-list-database-types 'json'", $output, $return_var);
    $db_types = json_decode(implode('', $output), true);
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_db.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
//}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
