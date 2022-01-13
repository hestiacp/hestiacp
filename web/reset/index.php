<?php

session_start();
define('NO_AUTH_REQUIRED', true);
$TAB = 'RESET PASSWORD';

if (isset($_SESSION['user'])) {
    header("Location: /list/user");
}

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ($_SESSION['POLICY_SYSTEM_PASSWORD_RESET'] == 'no') {
    header('Location: /login/');
    exit();
}

if ((!empty($_POST['user'])) && (empty($_POST['code']))) {
    // Check token
    verify_csrf($_POST);
    $v_user = escapeshellarg($_POST['user']);
    $user = $_POST['user'];
    $email = $_POST['email'];
    $cmd="/usr/bin/sudo /usr/local/hestia/bin/v-list-user";
    exec($cmd." ".$v_user." json", $output, $return_var);
    if ($return_var == 0) {
        $data = json_decode(implode('', $output), true);
        unset($output);
        exec(HESTIA_CMD . "v-get-user-value ".$v_user." RKEYEXP", $output, $return_var);
        $rkeyexp = json_decode(implode('', $output), true);
        if ($rkeyexp === null || $rkeyexp < time() - 900) {
            if ($email == $data[$user]['CONTACT']) {
                $rkey = substr(password_hash("", PASSWORD_DEFAULT), 8, 12);
                $hash = password_hash($rkey, PASSWORD_DEFAULT);
                $v_rkey = tempnam("/tmp", "vst");
                $fp = fopen($v_rkey, "w");
                fwrite($fp, $hash."\n");
                fclose($fp);
                exec(HESTIA_CMD . "v-change-user-rkey ".$v_user." ".$v_rkey."", $output, $return_var);
                unset($output);
                unlink($v_rkey);
                $name = $data[$user]['NAME'];
                $contact = $data[$user]['CONTACT'];
                $to = $data[$user]['CONTACT'];
                $subject = sprintf(_('MAIL_RESET_SUBJECT'), date("Y-m-d H:i:s"));
                $hostname = exec('hostname');
                $from = "noreply@".$hostname;
                $from_name = _('Hestia Control Panel');
                if (!empty($name)) {
                    $mailtext = sprintf(_('GREETINGS_GORDON'), $name);
                } else {
                    $mailtext = _('GREETINGS');
                }
                if ($hostname.":".$_SERVER['SERVER_PORT'] == $_SERVER['HTTP_HOST']) {
                    $mailtext .= sprintf(_('PASSWORD_RESET_REQUEST'), $_SERVER['HTTP_HOST'], $user, $rkey, $_SERVER['HTTP_HOST'], $user, $rkey);
                    if (!empty($rkey)) {
                        send_email($to, $subject, $mailtext, $from, $from_name, $data[$user]['NAME']);
                    }
                    header("Location: /reset/?action=code&user=".$_POST['user']);
                    exit;
                } else {
                    $ERROR = "<a class=\"error\">"._('Invalid host domain')."</a>";
                }
            }
        } else {
            $ERROR = "<a class=\"error\">"._('Please wait 15 minutes before sending a new request')."</a>";
        }
    }
    unset($output);
}

if ((!empty($_POST['user'])) && (!empty($_POST['code'])) && (!empty($_POST['password']))) {
    // Check token
    verify_csrf($_POST);
    if ($_POST['password'] == $_POST['password_confirm']) {
        $v_user = escapeshellarg($_POST['user']);
        $user = $_POST['user'];
        exec(HESTIA_CMD . "v-list-user ".$v_user." json", $output, $return_var);
        if ($return_var == 0) {
            $data = json_decode(implode('', $output), true);
            $rkey = $data[$user]['RKEY'];
            if (password_verify($_POST['code'], $rkey)) {
                unset($output);
                exec(HESTIA_CMD . "v-get-user-value ".$v_user." RKEYEXP", $output, $return_var);
                if ($output[0] > time() - 900) {
                    $v_password = tempnam("/tmp", "vst");
                    $fp = fopen($v_password, "w");
                    fwrite($fp, $_POST['password']."\n");
                    fclose($fp);
                    exec(HESTIA_CMD . "v-change-user-password ".$v_user." ".$v_password, $output, $return_var);
                    unlink($v_password);
                    if ($return_var > 0) {
                        sleep(5);
                        $ERROR = "<a class=\"error\">"._('An internal error occurred')."</a>";
                    } else {
                        $_SESSION['user'] = $_POST['user'];
                        header("Location: /");
                        exit;
                    }
                } else {
                    sleep(5);
                    $ERROR = "<a class=\"error\">"._('Code has been expired')."</a>";
                    exec(HESTIA_CMD . 'v-log-user-login ' . $v_user . ' ' . $v_ip . ' failed ' . $v_session_id . ' ' . $v_user_agent .' yes "Reset code has been expired"', $output, $return_var);
                }
            } else {
                sleep(5);
                $ERROR = "<a class=\"error\">"._('Invalid username or code')."</a>";
                exec(HESTIA_CMD . 'v-log-user-login ' . $v_user . ' ' . $v_ip . ' failed ' . $v_session_id . ' ' . $v_user_agent .' yes "Invalid Username or Code"', $output, $return_var);
            }
        } else {
            sleep(5);
            $ERROR = "<a class=\"error\">"._('Invalid username or code')."</a>";
        }
    } else {
        $ERROR = "<a class=\"error\">"._('Passwords not match')."</a>";
    }
}

if (empty($_GET['action'])) {
    require_once '../templates/header.html';
    require_once '../templates/pages/login/reset_1.html';
} else {
    require_once '../templates/header.html';
    if ($_GET['action'] == 'code') {
        require_once '../templates/pages/login/reset_2.html';
    }
    if (($_GET['action'] == 'confirm') && (!empty($_GET['code']))) {
        require_once '../templates/pages/login/reset_3.html';
    }
}
