<?php
session_start();
define('NO_AUTH_REQUIRED',true);
$TAB = 'RESET PASSWORD';

if (isset($_SESSION['user'])) {
    header("Location: /list/user");
}

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

//Check values
if(!empty($_POST['user']) && !empty($_POST['twofa'])){
    $error = true;
    $v_user = escapeshellarg($_POST['user']);
    $user = $_POST['user'];
    $twofa = $_POST['twofa'];
    $cmd="/usr/bin/sudo /usr/local/hestia/bin/v-list-user";
    exec ($cmd." ".$v_user." json", $output, $return_var);
    if ( $return_var == 0 ) {
        $data = json_decode(implode('', $output), true);
        if($data[$user]['TWOFA'] == $twofa){
            $success = true;
            $cmd="/usr/bin/sudo /usr/local/hestia/bin/v-delete-user-2fa";
            exec ($cmd." ".$v_user." json", $output, $return_var);
        }else{
            sleep(5);   
        }
    }else{
        sleep(5);
    }
    
}

require_once '../templates/header.html';
require_once '../templates/reset2fa.html';

?>