<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if (empty($_POST["backup"])) {
	header("Location: /list/backup/");
	exit();
}
if (empty($_POST["action"])) {
	header("Location: /list/backup/");
	exit();
}

$backup = $_POST["backup"];
$action = $_POST["action"];

// Check token
verify_csrf($_POST);

switch ($action) {
	case "delete":
		$cmd = "v-delete-user-backup";
		break;
	default:
		header("Location: /list/backup/");
		exit();
}

foreach ($backup as $value) {
	$value = quoteshellarg($value);
	exec(HESTIA_CMD . $cmd . " " . $user . " " . $value, $output, $return_var);
}

header("Location: /list/backup/");
