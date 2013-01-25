<?php
/*
 * Just sets language
 */
session_start();
$_SESSION['language'] = strtolower(substr((string)$_GET['l'],0,2));
header("Location: /");
?>
