<?php

session_start();

if (!empty($_SESSION['look'])) {
    unset($_SESSION['look']);
} else {
    session_destroy();
}
setcookie('limit2fa','',time() - 3600,"/");
header("Location: /login/");
exit;
?>
