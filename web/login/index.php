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
        exec (VESTA_CMD . "v-list-user ".escapeshellarg($_GET['loginas'])." json", $output, $return_var);
        if ( $return_var == 0 ) {
            $data = json_decode(implode('', $output), true);
            reset($data);
            $_SESSION['look'] = key($data);
            $_SESSION['look_alert'] = 'yes';
        }
    }
    header("Location: /");
    exit;
}

// Basic auth
if (isset($_POST['user']) && isset($_POST['password'])) {
    if(isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token']) {
        $v_user = escapeshellarg($_POST['user']);

        // Send password via tmp file
        $v_password = exec('mktemp -p /tmp');
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['password']."\n");
        fclose($fp);

        // Check user & password
        exec(VESTA_CMD ."v-check-user-password ".$v_user." ".escapeshellarg($v_password)." ".escapeshellarg($_SERVER['REMOTE_ADDR']),  $output, $return_var);
        unset($output);

        // Remove tmp file
        unlink($v_password);

        // Check API answer
        if ( $return_var > 0 ) {
            $ERROR = "<a class=\"error\">".__('Invalid username or password')."</a>";

        } else {

            // Make root admin user
            if ($_POST['user'] == 'root') $v_user = 'admin';

            // Get user speciefic parameters
            exec (VESTA_CMD . "v-list-user ".$v_user." json", $output, $return_var);
            $data = json_decode(implode('', $output), true);

            // Define session user
            $_SESSION['user'] = key($data);
            $v_user = $_SESSION['user'];

            // Get user favorites
            get_favourites();

            // Define language
            $output = '';
            exec (VESTA_CMD."v-list-sys-languages json", $output, $return_var);
            $languages = json_decode(implode('', $output), true);
            if(in_array($data[$v_user]['LANGUAGE'], $languages)){
                $_SESSION['language'] = $data[$v_user]['LANGUAGE'];
            }
            else {
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
                header("Location: /");
                exit;
            }
        }
    } else {
        $ERROR = "<a class=\"error\">".__('Invalid or missing token')."</a>";
    }
}

// Check system configuration
exec (VESTA_CMD . "v-list-sys-config json", $output, $return_var);
$data = json_decode(implode('', $output), true);
$sys_arr = $data['config'];
foreach ($sys_arr as $key => $value) {
    $_SESSION[$key] = $value;
}

// Detect language
if (empty($_SESSION['language'])) {
    $output = '';
    exec (VESTA_CMD."v-list-sys-config json", $output, $return_var);
    $config = json_decode(implode('', $output), true);
    $lang = $config['config']['LANGUAGE'];

    $output = '';
    exec (VESTA_CMD."v-list-sys-languages json", $output, $return_var);
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
