<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: /list/user/");
} else {
    header("Location: /login/");
}
?>
