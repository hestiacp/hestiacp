<?php
error_reporting(NULL);
ob_start();
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check POST request
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Check empty fields
    if (empty($_POST['v_username'])) $errors[] = _('user');
    if (empty($_POST['v_password'])) $errors[] = _('password');
    if (empty($_POST['v_package'])) $errrors[] = _('package');
    if (empty($_POST['v_email'])) $errors[] = _('email');
    if (empty($_POST['v_name'])) $errors[] = _('name');
    if (!empty($errors)) {
        foreach ($errors as $i => $error) {
            if ( $i == 0 ) {
                $error_msg = $error;
            } else {
                $error_msg = $error_msg.", ".$error;
            }
        }
        $_SESSION['error_msg'] = sprintf(_('Field "%s" can not be blank.'),$error_msg);
    }

    // Validate email
    if ((empty($_SESSION['error_msg'])) && (!filter_var($_POST['v_email'], FILTER_VALIDATE_EMAIL))) {
        $_SESSION['error_msg'] = _('Please enter valid email address.');
    }

    // Check password length
    if (empty($_SESSION['error_msg'])) {
        if (!validate_password($_POST['v_password'])) { $_SESSION['error_msg'] = _('Password does not match the minimum requirements'); }
    }

    // Protect input
    $v_username = escapeshellarg($_POST['v_username']);
    $v_email = escapeshellarg($_POST['v_email']);
    $v_package = escapeshellarg($_POST['v_package']);
    $v_language = escapeshellarg($_POST['v_language']);
    $v_name = escapeshellarg($_POST['v_name']);
    $v_notify = $_POST['v_notify'];


    // Add user
    if (empty($_SESSION['error_msg'])) {
        $v_password = tempnam("/tmp","vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['v_password']."\n");
        fclose($fp);
        exec (HESTIA_CMD."v-add-user ".$v_username." ".$v_password." ".$v_email." ".$v_package." ".$v_name, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($v_password);
        $v_password = escapeshellarg($_POST['v_password']);
    }

    // Set language
    if (empty($_SESSION['error_msg'])) {
        exec (HESTIA_CMD."v-change-user-language ".$v_username." ".$v_language, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Set Role
    if (empty($_SESSION['error_msg'])) {
        $v_role = escapeshellarg($_POST['v_role']);
        exec (HESTIA_CMD."v-change-user-role ".$v_username." ".$v_role, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Send email to the new user
    if ((empty($_SESSION['error_msg'])) && (!empty($v_notify))) {
        $to = $_POST['v_notify'];
        $subject = _("Welcome to Hestia Control Panel"); //currently not supported to use the account language
        $hostname = exec('hostname');
        unset($output);
        $from = sprintf(_('MAIL_FROM'),$hostname); //currently not supported to use the account language

        if (!empty($_POST['v_name'])) {
            $mailtext = sprintf(_('GREETINGS_GORDON'),$_POST['v_name'])."\r\n";
        } else {
            $mailtext = _('GREETINGS')."\r\n";
        }
        $mailtext .= sprintf(_('ACCOUNT_READY'),$_SERVER['HTTP_HOST'],$_POST['v_username'],$_POST['v_password']);
        send_email($to, $subject, $mailtext, $from);
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = sprintf(_('USER_CREATED_OK'),htmlentities($_POST['v_username']),htmlentities($_POST['v_username']));
        $_SESSION['ok_msg'] .= " / <a href=/login/?loginas=".htmlentities($_POST['v_username'])."&token=".htmlentities($_SESSION['token']).">" . _('login as') ." ".htmlentities($_POST['v_username']). "</a>";
        unset($v_username);
        unset($v_password);
        unset($v_email);
        unset($v_name);
        unset($v_notify);
    }
}


// List hosting packages
exec (HESTIA_CMD."v-list-user-packages json", $output, $return_var);
check_error($return_var);
$data = json_decode(implode('', $output), true);
unset($output);

// List languages
exec (HESTIA_CMD."v-list-sys-languages json", $output, $return_var);
$language = json_decode(implode('', $output), true);
foreach($language as $lang){
    $languages[$lang] = translate_json($lang);
}
asort($languages);

// Render page
render_page($user, $TAB, 'add_user');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
