<?php
session_start();
define('NO_AUTH_REQUIRED',true);
$TAB = 'RESET PASSWORD';

if (isset($_SESSION['user'])) {
    header("Location: /list/user");
}

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

if ((!empty($_POST['user'])) && (empty($_POST['code']))) {
    $v_user = escapeshellarg($_POST['user']);
    $user = $_POST['user'];
    $cmd="/usr/bin/sudo /usr/local/vesta/bin/v-list-user";
    exec ($cmd." ".$v_user." json", $output, $return_var);
    if ( $return_var == 0 ) {
        $data = json_decode(implode('', $output), true);
        $rkey = $data[$user]['RKEY'];
        $fname = $data[$user]['FNAME'];
        $lname = $data[$user]['LNAME'];
        $contact = $data[$user]['CONTACT'];
        $to = $data[$user]['CONTACT'];
        $subject = __('MAIL_RESET_SUBJECT',date("Y-m-d H:i:s"));
        $hostname = exec('hostname');
        $from = __('MAIL_FROM',$hostname);
        if (!empty($fname)) {
            $mailtext = __('GREETINGS_GORDON_FREEMAN',$fname,$lname);
        } else {
            $mailtext = __('GREETINGS');
        }
        $mailtext .= __('PASSWORD_RESET_REQUEST',$_SERVER['HTTP_HOST'],$user,$rkey,$_SERVER['HTTP_HOST'],$user,$rkey);
        if (!empty($rkey)) send_email($to, $subject, $mailtext, $from);
        unset($output);
    }

    header("Location: /reset/?action=code&user=".$_POST['user']);
    exit;
}

if ((!empty($_POST['user'])) && (!empty($_POST['code'])) && (!empty($_POST['password'])) ) {
    if ( $_POST['password'] == $_POST['password_confirm'] ) {
        $v_user = escapeshellarg($_POST['user']);
        $user = $_POST['user'];
        $cmd="/usr/bin/sudo /usr/local/vesta/bin/v-list-user";
        exec ($cmd." ".$v_user." json", $output, $return_var);
        if ( $return_var == 0 ) {
            $data = json_decode(implode('', $output), true);
            $rkey = $data[$user]['RKEY'];
            if (hash_equals($rkey, $POST[‘code’])) {
                $v_password = tempnam("/tmp","vst");
                $fp = fopen($v_password, "w");
                fwrite($fp, $_POST['password']."\n");
                fclose($fp);
                $cmd="/usr/bin/sudo /usr/local/vesta/bin/v-change-user-password";
                exec ($cmd." ".$v_user." ".$v_password, $output, $return_var);
                unlink($v_password);
                if ( $return_var > 0 ) {
                    $ERROR = "<a class=\"error\">".__('An internal error occurred')."</a>";
                } else {
                    $_SESSION['user'] = $_POST['user'];
                    header("Location: /");
                    exit;
                }
            } else {
                $ERROR = "<a class=\"error\">".__('Invalid username or code')."</a>";
            }
        } else {
            $ERROR = "<a class=\"error\">".__('Invalid username or code')."</a>";
        }
    } else {
        $ERROR = "<a class=\"error\">".__('Passwords not match')."</a>";
    }
}

// Detect language
if (empty($_SESSION['language'])) $_SESSION['language'] = detect_user_language();

if (empty($_GET['action'])) {
    require_once '../templates/header.html';
    require_once '../templates/reset_1.html';
} else {
    require_once '../templates/header.html';
    if ($_GET['action'] == 'code' ) {
        require_once '../templates/reset_2.html';
    }
    if (($_GET['action'] == 'confirm' ) && (!empty($_GET['code']))) {
        require_once '../templates/reset_3.html';
    }
}

?>
