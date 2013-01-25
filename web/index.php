<?php
session_start();

define('NO_AUTH_REQUIRED',true);

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


if (isset($_SESSION['user'])) {
    header("Location: /list/user");
} else {
    header("Location: /login/");
}
?>
