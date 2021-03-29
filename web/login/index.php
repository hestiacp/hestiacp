<?php

define('NO_AUTH_REQUIRED',true);
// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

$TAB = 'login';

/*
// Logout
if (isset($_GET['logout'])) {
    setcookie('limit2fa','',time() - 3600,"/");
    session_destroy();
}
*/

/* ACTIONS FOR CURRENT USER SESSION */
if (isset($_SESSION['user'])) {

    // User impersonation
    // Allow administrators to view and manipulate contents of other user accounts
    if (($_SESSION['userContext'] === 'admin') && (!empty($_GET['loginas']))) {
        // Ensure token is passed and matches before granting user impersonation access
        if ((!$_GET['token']) || ($_SESSION['token'] != $_GET['token'])) {
            header('location: /list/user/');
            exit();
        } else {
            $v_user = escapeshellarg($_GET['loginas']);
            exec (HESTIA_CMD . "v-list-user ".$v_user." json", $output, $return_var);
            if ( $return_var == 0 ) {
                $data = json_decode(implode('', $output), true);
                reset($data);
                $_SESSION['look'] = key($data);
                // Reset account details for File Manager to impersonated user
                //unset($_SESSION['userTheme']);
                unset($_SESSION['_sf2_attributes']);
                unset($_SESSION['_sf2_meta']);
                header("Location: /login/");
            }
        }
        exit;
    }

    // Set view based on account properties
    if (empty($_GET['loginas'])) {
        // Default view to Users list for administrator accounts
        if (($_SESSION['userContext'] === 'admin') && (!isset($_SESSION['look']))) {
            header("Location: /list/user/");
            exit;
        }
        
        // Obtain account properties
        if (($_SESSION['userContext'] === 'admin') && (isset($_SESSION['look']))) {
            $v_user = escapeshellarg($_SESSION['look']);
        } else {
            $v_user = escapeshellarg($_SESSION['user']);
        }

        exec (HESTIA_CMD . "v-list-user ".$v_user." json", $output, $return_var);
        $data = json_decode(implode('', $output), true);
        unset($output); 
        
        // Determine package features and land user at the first available page
        if ($data[$user]['WEB_DOMAINS'] !== "0") {
            header("Location: /list/web/");
        } else if ($data[$user]['DNS_DOMAINS'] !== "0") {
            header("Location: /list/dns/");
        } else if ($data[$user]['MAIL_DOMAINS'] !== "0") {
            header("Location: /list/mail/");
        } else if ($data[$user]['DATABASES'] !== "0") {
            header("Location: /list/db/");
        } else if ($data[$user]['CRON_JOBS'] !== "0") {
            header("Location: /list/cron/");
        } else if ($data[$user]['BACKUPS'] !== "0") {
            header("Location: /list/backup/");
        } else {
            header("Location: /error/");
        }
        exit;
    }

    // Do not allow non-administrators to access account impersonation
    if (($_SESSION['userContext'] !== 'admin') && (!empty($_GET['loginas']))) {
        header("Location: /login/");
        exit;
    }

    exit;
}

function authenticate_user($user, $password, $twofa = ''){
    unset($_SESSION['login']);
    if(isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
    $v_user = escapeshellarg($user);
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
        if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
    }
    $v_ip = escapeshellarg($ip);
    $v_user_agent = escapeshellarg($user_agent);

     // Get user's salt
    $output = '';
    exec (HESTIA_CMD."v-get-user-salt ".$v_user." ".$v_ip." json" , $output, $return_var);
    $pam = json_decode(implode('', $output), true);
    if ( $return_var > 0 ) {
        sleep(2);
        $error = "<a class=\"error\">"._('Invalid username or password')."</a>";
        return $error;
        } else {
            $salt = $pam[$user]['SALT'];
            $method = $pam[$user]['METHOD'];

            if ($method == 'md5' ) {
                $hash = crypt($password, '$1$'.$salt.'$');
            }
            if ($method == 'sha-512' ) {
                $hash = crypt($password, '$6$rounds=5000$'.$salt.'$');
                $hash = str_replace('$rounds=5000','',$hash);
            }
            if ($method == 'des' ) {
                $hash = crypt($password, $salt);
            }

            // Send hash via tmp file
            $v_hash = exec('mktemp -p /tmp');
            $fp = fopen($v_hash, "w");
            fwrite($fp, $hash."\n");
            fclose($fp);

            // Check user hash
            exec(HESTIA_CMD ."v-check-user-hash ".$v_user." ".$v_hash." ".$v_ip,  $output, $return_var);
            unset($output);

            // Remove tmp file
            unlink($v_hash);
            // Check API answer
            if ( $return_var > 0 ) {
                sleep(2);
                $error = "<a class=\"error\">"._('Invalid username or password')."</a>";
                $v_session_id = escapeshellarg($_POST['token']);
                exec(HESTIA_CMD."v-log-user-login ".$v_user." ".$v_ip." failed ".$v_session_id." ".$v_user_agent, $output, $return_var);

                return $error;
            } else {

                // Get user specific parameters
                exec (HESTIA_CMD . "v-list-user ".$v_user." json", $output, $return_var);
                $data = json_decode(implode('', $output), true);
                unset($output); 
                if ($data[$user]['TWOFA'] != '') {
                        if(empty($twofa)){
                            $_SESSION['login']['username'] = $user;
                            $_SESSION['login']['password'] = $password;
                            return false;
                        }else{
                            $v_twofa = escapeshellarg($twofa);
                            exec(HESTIA_CMD ."v-check-user-2fa ".$v_user." ".$v_twofa, $output, $return_var);
                            unset($output);
                            if ( $return_var > 0 ) {
                                sleep(2);
                                $error = "<a class=\"error\">"._('Invalid or missing 2FA token')."</a>";
                                $_SESSION['login']['username'] = $user;
                                $_SESSION['login']['password'] = $password;
                                $v_session_id = escapeshellarg($_POST['token']);
                                exec(HESTIA_CMD."v-log-user-login ".$v_user." ".$v_ip." failed ".$v_session_id." ".$v_user_agent, $output, $return_var);
                                return $error;
                                unset($_POST['twofa']);
                            }
                        }
                }
                

                
                // Define session user
                $_SESSION['user'] = key($data);
                $v_user = $_SESSION['user'];
                //log successfull login attempt
                $v_session_id = escapeshellarg($_POST['token']);
                exec(HESTIA_CMD."v-log-user-login ".$v_user." ".$v_ip." success ".$v_session_id." ".$v_user_agent, $output, $return_var);

                $_SESSION['LAST_ACTIVITY'] = time();
                $_SESSION['MURMUR'] = $_POST['murmur'];

                $_SESSION['userContext'] = $data[$user]['ROLE'];
                $_SESSION['userTheme'] = $data[$user]['THEME'];
                if ($_SESSION['POLICY_USER_CHANGE_THEME'] === 'no') {
                    unset($_SESSION['userTheme']);
                }

                // Define language
                $output = '';
                exec (HESTIA_CMD."v-list-sys-languages json", $output, $return_var);
                $languages = json_decode(implode('', $output), true);
                if (in_array($data[$v_user]['LANGUAGE'], $languages)){
                    $_SESSION['language'] = $data[$user]['LANGUAGE'];
                } else {
                    $_SESSION['language'] = 'en';
                }
                // Regenerate session id to prevent session fixation
                session_regenerate_id();

                // Redirect request to control panel interface
                if (!empty($_SESSION['request_uri'])) {
                    header("Location: ".$_SESSION['request_uri']);
                    unset($_SESSION['request_uri']);
                    exit;
                } else {
                    if ($_SESSION['userContext'] === 'admin') {
                        header("Location: /list/user/");
                    } else {
                        if($data[$user]['WEB_DOMAINS'] != "0") {
                            header("Location: /list/web/");
                        } else if ($data[$user]['DNS_DOMAINS'] != "0") {
                            header("Location: /list/dns/");
                        } else if ($data[$user]['MAIL_DOMAINS'] != "0") {
                            header("Location: /list/mail/");
                        } else if ($data[$user]['DATABASES'] != "0") {
                            header("Location: /list/db/");
                        } else if ($data[$user]['CRON_JOBS'] != "0") {
                            header("Location: /list/cron/");
                        } else if ($data[$user]['BACKUPS'] != "0") {
                            header("Location: /list/backup/");
                        } else {
                            header("Location: /error/");
                        }
                    }
                    exit;
                }
            }
        }
    } else {
        unset($_POST);
        unset($_GET);
        unset($_SESSION);
        session_destroy();
        session_start();
        return false;
    }
}
if (!empty($_SESSION['login']['username']) && !empty($_SESSION['login']['password']) && !empty($_POST['twofa'])){
    $error = authenticate_user($_SESSION['login']['username'], $_SESSION['login']['password'], $_POST['twofa']);
    unset($_POST);
} else if (!empty($_POST['user']) && !empty($_POST['password'])) {
    $error = authenticate_user($_POST['user'], $_POST['password']);
    unset($_POST);
}else{
    unset($_SESSION['login']);
}
// Check system configuration
load_hestia_config();

// Detect language
if (empty($_SESSION['language'])) {
    $output = '';
    exec (HESTIA_CMD."v-list-sys-config json", $output, $return_var);
    $config = json_decode(implode('', $output), true);
    $lang = $config['config']['LANGUAGE'];

    $output = '';
    exec (HESTIA_CMD."v-list-sys-languages json", $output, $return_var);
    $languages = json_decode(implode('', $output), true);
    if(in_array($lang, $languages)){
        $_SESSION['language'] = $lang;
    }
    else {
        $_SESSION['language'] = 'en';
    }
}

// Generate CSRF token
$_SESSION['token'] = md5(uniqid(mt_rand(), true));

require_once('../templates/header.html');
if(!empty($_SESSION['login'])){
    require_once('../templates/login_2.html');
}else if (empty($_POST['user'])) {
    if($_SESSION['LOGIN_STYLE'] == 'old'){
        require_once('../templates/login_a.html');
    }else{
        require_once('../templates/login.html');
    }
}else if (empty($_POST['password'])) {
    require_once('../templates/login_1.html');
}else{
    if($_SESSION['LOGIN_STYLE'] == 'old'){
        require_once('../templates/login_a.html');
    }else{
        require_once('../templates/login.html');
    }
}
?>
