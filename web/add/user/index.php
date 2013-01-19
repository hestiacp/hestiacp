<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'USER';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if (empty($_SESSION['user'])) {
    header("Location: /login/");
}

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
if ($_SESSION['user'] == 'admin') {
    if (!empty($_POST['ok'])) {
        // Check input
        if (empty($_POST['v_username'])) $errors[] = _('user');
        if (empty($_POST['v_password'])) $errors[] = _('password');
        if (empty($_POST['v_package'])) $errrors[] = _('package');
        if (empty($_POST['v_email'])) $errors[] = _('email');
        if (empty($_POST['v_fname'])) $errors[] = _('first name');
        if (empty($_POST['v_lname'])) $errors[] = _('last name');

        // Protect input
        $v_username = escapeshellarg($_POST['v_username']);
        $v_password = escapeshellarg($_POST['v_password']);
        $v_package = escapeshellarg($_POST['v_package']);
        $v_email = escapeshellarg($_POST['v_email']);
        $v_fname = escapeshellarg($_POST['v_fname']);
        $v_lname = escapeshellarg($_POST['v_lname']);
        if (empty($_POST['v_notify'])) $v_notify = 'off';

        // Validate email
        if (!filter_var($_POST['v_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_msg'] = _('Please enter valid email address.');
        }

        // Check for errors
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = _('Error: field "%s" can not be blank.',$error_msg);
        }

        if (empty($_SESSION['error_msg'])) {
            exec (VESTA_CMD."v-add-user ".$v_username." ".$v_password." ".$v_email." ".$v_package." ".$v_fname." ".$v_lname, $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = _('Error: vesta did not return any output.');
                $_SESSION['error_msg'] = $error;
            } else {
                if (empty($v_notify)) {
                    $to = $_POST['v_email'];
                    $subject = _("Welcome to Vesta Control Panel");
                    $hostname = exec('hostname');
                    $from = _('MAIL_FROM',$hostname);
                    if (!empty($_POST['v_fname'])) {
                        $mailtext = _('GREETINGS_GORDON_FREEMAN',$_POST['v_fname'],$_POST['v_lname']);
                    } else {
                        $mailtext = _('GREETINGS');
                    }
                    $mailtext .= _('ACCOUNT_READY',$_SERVER['HTTP_HOST'],$_POST['v_username'],$_POST['v_password']);
                    send_email($to, $subject, $mailtext, $from);
                }

                $_SESSION['ok_msg'] = _('ACCOUNT_CREATED_OK',$_POST[v_username],$_POST[v_username]);
                unset($v_username);
                unset($v_password);
                unset($v_email);
                unset($v_fname);
                unset($v_lname);
            }
            unset($output);
        }
    }

    exec (VESTA_CMD."v-list-user-packages json", $output, $return_var);
    check_error($return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_user.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
