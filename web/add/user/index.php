<?php

ob_start();
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['userContext'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check POST request
if (!empty($_POST['ok'])) {

    // Check token
    verify_csrf($_POST);

    // Check empty fields
    if (empty($_POST['v_username'])) {
        $errors[] = _('user');
    }
    if (empty($_POST['v_password'])) {
        $errors[] = _('password');
    }
    if (empty($_POST['v_package'])) {
        $errrors[] = _('package');
    }
    if (empty($_POST['v_email'])) {
        $errors[] = _('email');
    }
    if (empty($_POST['v_name'])) {
        $errors[] = _('name');
    }
    if (!empty($errors)) {
        foreach ($errors as $i => $error) {
            if ($i == 0) {
                $error_msg = $error;
            } else {
                $error_msg = $error_msg.", ".$error;
            }
        }
        $_SESSION['error_msg'] = sprintf(_('Field "%s" can not be blank.'), $error_msg);
    }

    // Validate email
    if ((empty($_SESSION['error_msg'])) && (!filter_var($_POST['v_email'], FILTER_VALIDATE_EMAIL))) {
        $_SESSION['error_msg'] = _('Please enter valid email address.');
    }

    // Check password length
    if (empty($_SESSION['error_msg'])) {
        if (!validate_password($_POST['v_password'])) {
            $_SESSION['error_msg'] = _('Password does not match the minimum requirements');
        }
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
        $v_password = tempnam("/tmp", "vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['v_password']."\n");
        fclose($fp);
        exec(HESTIA_CMD."v-add-user ".$v_username." ".$v_password." ".$v_email." ".$v_package." ".$v_name, $output, $return_var);
        check_return_code($return_var, $output);
        unset($output);
        unlink($v_password);
        $v_password = escapeshellarg($_POST['v_password']);
    }

    // Set language
    if (empty($_SESSION['error_msg'])) {
        exec(HESTIA_CMD."v-change-user-language ".$v_username." ".$v_language, $output, $return_var);
        check_return_code($return_var, $output);
        unset($output);
    }

    // Set Role
    if (empty($_SESSION['error_msg'])) {
        $v_role = escapeshellarg($_POST['v_role']);
        exec(HESTIA_CMD."v-change-user-role ".$v_username." ".$v_role, $output, $return_var);
        check_return_code($return_var, $output);
        unset($output);
    }

    // Set login restriction
    if (empty($_SESSION['error_msg'])) {
        if (!empty($_POST['v_login_disabled')]) {
            $_POST['v_login_disabled'] = 'yes';
            exec(HESTIA_CMD."v-change-user-config-value ".$v_username." LOGIN_DISABLED ".escapeshellarg($_POST['v_login_disabled']), $output, $return_var);
            check_return_code($return_var, $output);
            unset($output);
        }
    }

    // Send email to the new user
    if ((empty($_SESSION['error_msg'])) && (!empty($v_notify))) {
        $to = $_POST['v_notify'];
        // send email in "users" language
        putenv("LANGUAGE=".$_POST['v_language']);

        $subject = _("Welcome to Hestia Control Panel");
        $hostname = exec('hostname');
        unset($output);
        $from = "noreply@".$hostname;
        $from_name = _('Hestia Control Panel');

        if (!empty($_POST['v_name'])) {
            $mailtext = sprintf(_('GREETINGS_GORDON'), $_POST['v_name'])."\r\n";
        } else {
            $mailtext = _('GREETINGS')."\r\n";
        }

        $mailtext .= sprintf(_('ACCOUNT_READY'), $_SERVER['HTTP_HOST'], $_POST['v_username'], $_POST['v_password']);
        send_email($to, $subject, $mailtext, $from, $from_name, $_POST['name']);
        putenv("LANGUAGE=".detect_user_language());
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = sprintf(_('USER_CREATED_OK'), htmlentities($_POST['v_username']), htmlentities($_POST['v_username']));
        $_SESSION['ok_msg'] .= " / <a href=/login/?loginas=".htmlentities($_POST['v_username'])."&token=".htmlentities($_SESSION['token']).">" . _('login as') ." ".htmlentities($_POST['v_username']). "</a>";
        unset($v_username);
        unset($v_password);
        unset($v_email);
        unset($v_name);
        unset($v_notify);
    }
}


// List hosting packages
exec(HESTIA_CMD."v-list-user-packages json", $output, $return_var);
check_error($return_var);
$data = json_decode(implode('', $output), true);
unset($output);

// List languages
exec(HESTIA_CMD."v-list-sys-languages json", $output, $return_var);
$language = json_decode(implode('', $output), true);
foreach ($language as $lang) {
    $languages[$lang] = translate_json($lang);
}
asort($languages);

if (empty($v_username)) {
    $v_username = '';
}
if (empty($v_name)) {
    $v_name = '';
}
if (empty($v_email)) {
    $v_email = '';
}
if (empty($v_password)) {
    $v_password = '';
}
if (empty($v_login_disabled)) {
    $v_login_disabled = '';
}
if (empty($v_role)) {
    $v_role = '';
}
if (empty($v_notify)) {
    $v_notify = '';
}
// Render page
render_page($user, $TAB, 'add_user');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
