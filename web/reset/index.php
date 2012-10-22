<?php
session_start();
$TAB = 'RESET PASSWORD';
// 
function send_email($to,$subject,$mailtext,$from) {
    $charset = "utf-8";
    $to = '<'.$to.'>';
    $boundary='--' . md5( uniqid("myboundary") );
    $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
    $priority = $priorities[2];
    $ctencoding = "8bit";
    $sep = chr(13) . chr(10);
    $disposition = "inline";
    $subject = "=?$charset?B?".base64_encode($subject)."?=";
    $header.="From: $from \nX-Priority: $priority\nCC: $cc\n";
    $header.="Mime-Version: 1.0\nContent-Type: text/plain; charset=$charset \n";
    $header.="Content-Transfer-Encoding: $ctencoding\nX-Mailer: Php/libMailv1.3\n";
    $message .= $mailtext;
    mail($to, $subject, $message, $header);
}

if ((!empty($_POST['user'])) && (empty($_POST['code']))) {
    $v_user = escapeshellarg($_POST['user']);
    $user = $_POST['user'];
    $cmd="/usr/bin/sudo /usr/local/vesta/bin/v_list_user";
    exec ($cmd." ".$v_user." json", $output, $return_var);
    if ( $return_var == 0 ) {
        $data = json_decode(implode('', $output), true);
        $rkey = $data[$user]['RKEY'];
        $fname = $data[$user]['FNAME'];
        $lname = $data[$user]['LNAME'];
        $contact = $data[$user]['CONTACT'];
        $to = $data[$user]['CONTACT'];
        $subject = 'Password Reset '.date("Y-m-d H:i:s");
        $hostname = exec('hostname');
        $from = "Vesta Control Panel <noreply@".$hostname.">";
        if (!empty($fname)) {
            $mailtext = "Hello ".$fname." ".$lname.",\n";
        } else {
            $mailtext = "Hello,\n";
        }
        $mailtext .= "You recently asked to reset your control panel password. ";
        $mailtext .= "To complete your request, please follow this link:\n";
        $mailtext .= "https://".$_SERVER['HTTP_HOST']."/reset/?action=confirm&user=".$user."&code=".$rkey."\n\n";
        $mailtext .= "Alternately, you may go to https://".$_SERVER['HTTP_HOST']."/reset/?action=code&user=".$user." and enter the following password reset code:\n";
        $mailtext .= $rkey."\n\n";
        $mailtext .= "If you did not request a new password please ignore this letter and accept our apologies — we didn't intend to disturb you.\n";
        $mailtext .= "Thanks,\nThe VestaCP Team\n";
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
        $v_password = escapeshellarg($_POST['password']);
        $cmd="/usr/bin/sudo /usr/local/vesta/bin/v_list_user";
        exec ($cmd." ".$v_user." json", $output, $return_var);
        if ( $return_var == 0 ) {
            $data = json_decode(implode('', $output), true);
            $rkey = $data[$user]['RKEY'];
            if ($rkey == $_POST['code']) {
                $cmd="/usr/bin/sudo /usr/local/vesta/bin/v_change_user_password";
                exec ($cmd." ".$v_user." ".$v_password, $output, $return_var);
                if ( $return_var > 0 ) {
                    $ERROR = "<a class=\"error\">ERROR: Internal error</a>";
                } else {
                    $_SESSION['user'] = $_POST['user'];
                    header("Location: /");
                    exit;
                }
            } else {
                $ERROR = "<a class=\"error\">ERROR: Invalid username or code</a>";
            }
        } else {
            $ERROR = "<a class=\"error\">ERROR: Invalid username or code</a>";
        }
    } else {
        $ERROR = "<a class=\"error\">ERROR: Passwords not match</a>";
    }
}

require_once '../templates/header.html';
if (empty($_GET['action'])) {
    require_once '../templates/reset_1.html';
} else {
    if ($_GET['action'] == 'code' ) {
        require_once '../templates/reset_2.html';
    }
    if (($_GET['action'] == 'confirm' ) && (!empty($_GET['code']))) {
        require_once '../templates/reset_3.html';
    }
}

?>
