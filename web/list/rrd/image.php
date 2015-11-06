<?php
error_reporting(NULL);
session_start();
if ($_SESSION['user'] != 'admin') exit;
$real_path = realpath($_SERVER["DOCUMENT_ROOT"].$_SERVER['QUERY_STRING']);
if (empty($real_path)) exit;
$dir_name = dirname($real_path);
$dir_name = dirname($dir_name);
if ($dir_name != $_SERVER["DOCUMENT_ROOT"].'/rrd') exit;
header("X-Accel-Redirect: ".$_SERVER['QUERY_STRING']);
header("Content-Type: image/png");

?>
