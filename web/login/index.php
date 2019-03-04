<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <link rel="icon" href="/images/favicon.ico" type="image/x-icon">
  <title>Hestia - <?=__($TAB)?></title>
  <link rel="stylesheet" href="/css/styles.min.css?1446554103">
  <link type="text/css" href="/css/jquery-custom-dialogs.css?1446554103" rel="stylesheet" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script>
    //
    //  GLOBAL SETTINGS
    //
    var GLOBAL = {};
    GLOBAL.FTP_USER_PREFIX  = 'admin_';
    GLOBAL.DB_USER_PREFIX   = 'admin_';
    GLOBAL.DB_DBNAME_PREFIX = 'admin_';
    GLOBAL.AJAX_URL = '';
  </script>
</head>
<body class="body-<?=strtolower($TAB)?> lang-<?=$_SESSION['language']?>">
root@web02:/usr/local/hestia/web# nano mail/index.php ^C
root@web02:/usr/local/hestia/web# cat login/index.php
<?php

define('NO_AUTH_REQUIRED',true);


// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


$TAB = 'LOGIN';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
}

// Login as someone else
if (isset($_SESSION['user'])) {
    if ($_SESSION['user'] == 'admin' && !empty($_GET['loginas'])) {
        exec (HESTIA_CMD . "v-list-user ".escapeshellarg($_GET['loginas'])." json", $output, $return_var);
        if ( $return_var == 0 ) {
            $data = json_decode(implode('', $output), true);
            reset($data);
            $_SESSION['look'] = key($data);
            $_SESSION['look_alert'] = 'yes';
        }
    }
    header("Location: /list/user/");
    exit;
}

// Basic auth
if (isset($_POST['user']) && isset($_POST['password'])) {
    if(isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
        $v_user = escapeshellarg($_POST['user']);
        $v_ip = escapeshellarg($_SERVER['REMOTE_ADDR']);
        if (isset($_POST['twofa'])) {
            $v_twofa = escapeshellarg($_POST['twofa']);
        }

        // Get user's salt
        $output = '';
        exec (HESTIA_CMD."v-get-user-salt ".$v_user." ".$v_ip." json" , $output, $return_var);
        $pam = json_decode(implode('', $output), true);
        if ( $return_var > 0 ) {
            $ERROR = "<a class=\"error\">".__('Invalid username or password')."</a>";
        } else {
            $user = $_POST['user'];
            $password = $_POST['password'];
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
                $ERROR = "<a class=\"error\">".__('Invalid username or password')."</a>";
            } else {

                // Make root admin user
                if ($_POST['user'] == 'root') $v_user = 'admin';

                // Get user speciefic parameters
                exec (HESTIA_CMD . "v-list-user ".$v_user." json", $output, $return_var);
                $data = json_decode(implode('', $output), true);

                // Check if 2FA is active
                if ($data[$_POST['user']]['TWOFA'] != '') {
                    if (isset($v_twofa)){
                        exec(HESTIA_CMD ."v-check-user-2fa ".$v_user." ".$v_twofa, $output, $return_var);
                        unset($output);
                        if ( $return_var > 0 ) {
                            $ERROR = "<a class=\"error\">".__('Invalid or missing 2FA token')."</a>";
                        }
                    } else {
                        $ERROR = "<a class=\"error\">".__('Invalid or missing 2FA token')."</a>";
                    }
                }

                // Check if 2FA was successfully
                if ( ! isset($v_twofa) || $ERROR == '' ) {
                    // Define session user
                    $_SESSION['user'] = key($data);
                    $v_user = $_SESSION['user'];

                    // Get user favorites
                    get_favourites();

                    // Define language
                    $output = '';
                    exec (HESTIA_CMD."v-list-sys-languages json", $output, $return_var);
                    $languages = json_decode(implode('', $output), true);
                    if (in_array($data[$v_user]['LANGUAGE'], $languages)){
                        $_SESSION['language'] = $data[$v_user]['LANGUAGE'];
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
                        header("Location: /list/user/");
                        exit;
                    }
                }
            }
        }
    } else {
        $ERROR = "<a class=\"error\">".__('Invalid or missing token')."</a>";
    }
}

// Check system configuration
exec (HESTIA_CMD . "v-list-sys-config json", $output, $return_var);
$data = json_decode(implode('', $output), true);
$sys_arr = $data['config'];
foreach ($sys_arr as $key => $value) {
    $_SESSION[$key] = $value;
}

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

require_once($_SERVER['DOCUMENT_ROOT'].'/inc/i18n/'.$_SESSION['language'].'.php');
require_once('../templates/header.html');
require_once('../templates/login.html');