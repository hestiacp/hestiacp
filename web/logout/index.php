<?php
session_start();

define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');

if (!empty($_SESSION['look'])) {

    unset($_SESSION['look']);
    header("Location: /");
} else {
    if($_SESSION['MURMUR'] && $_SESSION['user']){
        $v_user = escapeshellarg($_SESSION['user']);
        $v_murmur = escapeshellarg($_SESSION['MURMUR']);
        exec(HESTIA_CMD."v-log-user-logout ".$v_user." ".$v_murmur, $output, $return_var);
    }
    
    session_destroy();
    header("Location: /login/");
}
exit;
?>
