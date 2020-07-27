<?php
session_start();

define('HESTIA_CMD', '/usr/bin/sudo /usr/local/hestia/bin/');

if (!empty($_SESSION['look'])) {
    unset($_SESSION['look']);
} else {
    if($_SESSION['MURMUR'] && $_SESSION['user']){
        $v_user = escapeshellarg($_SESSION['user']);
        $v_murmur = escapeshellarg($_SESSION['MURMUR']);
        exec(HESTIA_CMD."v-log-user-logout ".$v_user." ".$v_murmur, $output, $return_var);
    }
    
    session_destroy();
    setcookie('limit2fa','',time() - 3600,"/");
}

header("Location: /login/");
exit;
?>
