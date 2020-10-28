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
    $email = $_POST['email'];
    $cmd="/usr/bin/sudo /usr/local/hestia/bin/v-list-user";
    exec ($cmd." ".$v_user." json", $output, $return_var);
    if ( $return_var == 0 ) {
        $data = json_decode(implode('', $output), true);
        if($email == $data[$user]['CONTACT']){
            //genrate new rkey
            $rkey = substr( password_hash( rand(0,10), PASSWORD_DEFAULT ), 5, 12 );
            $hash = password_hash($rkey, PASSWORD_DEFAULT);
            $v_rkey = tempnam("/tmp","vst");
            $fp = fopen($v_rkey, "w");
            fwrite($fp, $hash."\n");
            fclose($fp);
            exec ("/usr/bin/sudo /usr/local/hestia/bin/v-change-user-rkey ".$v_user." ".$v_rkey."", $output, $return_var);
            unset($output);
            unlink($v_rkey);
            $name = $data[$user]['NAME'];
            $contact = $data[$user]['CONTACT'];
            $to = $data[$user]['CONTACT'];
            $subject = sprintf(_('MAIL_RESET_SUBJECT'),date("Y-m-d H:i:s"));
            $hostname = exec('hostname');
            $from = sprintf(_('MAIL_FROM'),$hostname);
            if (!empty($name)) {
                $mailtext = sprintf(_('GREETINGS_GORDON'),$name);
            } else {
                $mailtext = _('GREETINGS');
            }
            if (in_array(str_replace(':'.$_SERVER['SERVER_PORT'],'.conf',$_SERVER['HTTP_HOST']), array_merge(scandir('/etc/nginx/conf.d'),scandir('/etc/nginx/conf.d/domains'),scandir('/etc/apache2/conf.d/domains'),scandir('/etc/apache2/conf.d')))){
                $mailtext .= sprintf(_('PASSWORD_RESET_REQUEST'),$_SERVER['HTTP_HOST'],$user,$rkey,$_SERVER['HTTP_HOST'],$user,$rkey);
                if (!empty($rkey)) send_email($to, $subject, $mailtext, $from);
                header("Location: /reset/?action=code&user=".$_POST['user']);
                exit;
            } else {
                $ERROR = "<a class=\"error\">"._('Invalid host domain')."</a>";
            }
        }     
    }
    unset($output);
}

if ((!empty($_POST['user'])) && (!empty($_POST['code'])) && (!empty($_POST['password'])) ) {
    if ( $_POST['password'] == $_POST['password_confirm'] ) {
        $v_user = escapeshellarg($_POST['user']);
        $user = $_POST['user'];
        $cmd="/usr/bin/sudo /usr/local/hestia/bin/v-list-user";
        exec ($cmd." ".$v_user." json", $output, $return_var);
        if ( $return_var == 0 ) {
            $data = json_decode(implode('', $output), true);
            $rkey = $data[$user]['RKEY'];
            if (password_verify($_POST['code'], $rkey)) {
                unset($output);
                exec("/usr/bin/sudo /usr/local/hestia/bin/v-get-user-value ".$v_user." RKEYEXP", $output,$return_var);
                if($output[0] > time() - 900){
                    $v_password = tempnam("/tmp","vst");
                    $fp = fopen($v_password, "w");
                    fwrite($fp, $_POST['password']."\n");
                    fclose($fp);
                    $cmd="/usr/bin/sudo /usr/local/hestia/bin/v-change-user-password";
                    exec ($cmd." ".$v_user." ".$v_password, $output, $return_var);
                    unlink($v_password);
                    if ( $return_var > 0 ) {
                        sleep(5);
                        $ERROR = "<a class=\"error\">"._('An internal error occurred')."</a>";
                    } else {
                        $_SESSION['user'] = $_POST['user'];
                        header("Location: /");
                        exit;
                    }
                }else{
                    sleep(5);
                    $ERROR = "<a class=\"error\">"._('Code has been expired')."</a>";
                }
            } else {
                sleep(5);
                $ERROR = "<a class=\"error\">"._('Invalid username or code')."</a>";
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
