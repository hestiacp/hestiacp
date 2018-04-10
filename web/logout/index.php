<?php

session_start();

if (!empty($_SESSION['look'])) {
    unset($_SESSION['look']);
} else {
    session_destroy();
}

header("Location: /login/");
exit;
?>
